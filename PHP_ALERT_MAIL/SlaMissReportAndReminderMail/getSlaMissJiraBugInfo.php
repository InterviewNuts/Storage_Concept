<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */


include_once  './sendMail/sendMail.php';
include_once  './htmlReportTable/createHtmlTableFromJSONList.php';
include_once  './SlaMiss/SlaMiss.php';
include_once  './mysql/insert_into_slaDB.php';
include_once  './Util/Util.php';

//! password changed to complex one for security reason
$username = 'sf_api_sa';
$password = 'J6aXK4vSOOmBK0KCOhXK2tDuVze3eyBIHJ4RewaVKZkdC7Vc7c3riNdJ4wiH';

if( !empty($argv[2]))
{
    $DEBUG = $argv[2];
}
else
{
    $DEBUG="";
}



/*
 * This URL will get us the uresolved Jira Bugs. This one is filtered one.
 */

$url2 = 'https://jira.ngage.netapp.com/rest/api/2/search?jql=project=%22CSD%22%20and%20resolution%20is%20null%20and%20type=%22CPE%20Escalation%22&fields=issuetype,resolution,customfield_15192,customfield_16413,assignee,reporter&maxResults=1000';


//Initialize cURL.
$ch = curl_init();

curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

//Set the URL that you want to GET by using the CURLOPT_URL option.
curl_setopt($ch, CURLOPT_URL, $url2);

//Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

//Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

//Execute the request.
$data = curl_exec($ch);

$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

$count_total_jira_active_cases=0;
$notViolatedJiraCases=0;
$p4Cases=0;
$escalNotSet=0;
$percent_compliance_overall=100;
$counter=0;

//200 = OK
if($statusCode == '200')
{

    $jsonObj = json_decode($data);

    $slaDLIST = new SplDoublyLinkedList();

    //! now loop through the parent respApi to get the each burt restapi( changelog rest api will be extracted).
    foreach($jsonObj->issues as $key=>$value)
    {
        if( !empty($value->fields->assignee))
        {
            if( !empty($value->key))
            {
                ++$count_total_jira_active_cases;
            }


            if( stristr($value->fields->assignee->key,"JIRAUSER"))
                $jsonObj->mgrKey[]=getSolidFireEmpInfo::getMgrKey($value->fields->assignee->name);
            else
                $jsonObj->mgrKey[]=getSolidFireEmpInfo::getMgrKey($value->fields->assignee->key);

            $burtUrl=SlaMiss::getJsonApiOfEachBurt($value);

            //Execute the request.
            //Set the URL that you want to GET by using the CURLOPT_URL option.
            curl_setopt($ch, CURLOPT_URL, $burtUrl);

            $burtData = curl_exec($ch);


            $burtJsonObj = json_decode($burtData);
            if( empty($burtJsonObj->fields->customfield_15192->value))
            { 
                if ( !empty($DEBUG))
                {
                    echo "Escalation Priority Not set, Hence Ignored, JIRA ID = ".$burtJsonObj->key."\n";
                }
                $escalNotSet++;
                continue;
            }
            if( $burtJsonObj->fields->customfield_15192->value == "P4-Low" )
            { 
                if ( !empty($DEBUG))
                {
                    echo "P4-Low Case, Hence Ignored, JIRA ID = ".$burtJsonObj->key."\n";
                } 
                $p4Cases++;
                continue;
            }

            $jsonBurt=SlaMiss::getLastUpdateOnEscalationNextSteps($burtJsonObj);
            if( !empty($jsonBurt))
            {
                $violated=SlaMiss::getSLAMissForBurt($jsonBurt);
                if($violated == "YES")
                {
                    //! Push to The List    
                    $slaDLIST->push($jsonBurt);
                }
                else
                {
                    $notViolatedJiraCases++;
                    if ( !empty($DEBUG))
                    {
                        echo "Jira Id Not violated SLA           =".$jsonBurt->jiraId ."\n";
                    }
                }
            }
        }
        else
        {
            if ( !empty($DEBUG))
                echo "Jira Id where Assign is Null          =".$value->key."\n";        
        }
    }//! end of Parent loop


    //! Here I have removed the P4-Low , and the Jira Caases where Escalation Priority is not set from total jira cases.
    $count_total_jira_active_cases = $count_total_jira_active_cases - ($escalNotSet+$p4Cases);

    //! just add the total number of sla jira id into the main json object
    $jsonObj->count_total_jira_active_cases=$count_total_jira_active_cases;

    //! Add toltal number of managers into main json object(mean including sla miss & sla non miss).
    $jsonObj->count_total_jira_active_manager=count(array_keys(array_count_values($jsonObj->mgrKey)));

    //! get the tolal number of sla miss bugs
    $totalSlaBurt=$slaDLIST->count();
    if ( !empty($DEBUG))
    {
        echo "Total number of Jira cases                    =".$jsonObj->count_total_jira_active_cases."\n";
        echo "Total number of Jira Manager                  =".$jsonObj->count_total_jira_active_manager."\n";
        echo "Total Violated Jira Cases                     =" .$totalSlaBurt ."\n";
        echo "Total NON Violated Jira Cases                 =" .$notViolatedJiraCases ."\n";
        echo "Total Escalation Priority Not set Jira Cases  =" .$escalNotSet ."\n";
        echo "Total P4 Low Cases                            =" .$p4Cases ."\n";
    }
    //! Insert into sf_sla_test_table table for sla miss report data.
    if(!$slaDLIST->isEmpty() )
    {
        $dbObj= new dbScheema();

        $dbInsertObj = new MySqlDbOp();

        $slaDLIST->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
        //! Insert SLA report infor into DB
        for ($slaDLIST->rewind(); $slaDLIST->valid(); $slaDLIST->next()) 
        {
            $mgrList[]=$slaDLIST->current()->ManagerKey;

            $dbInsertObj->populateDbObject($dbObj, $slaDLIST->current());

            $dbInsertObj->dbSimpleInsert($dbObj);
        }
        $slaDLIST->countMgrSlaMissKey=$mgrList;

        $metricDbObj= new MetricScheema();

        $metricDbInsertObj = new MySqlDbOp("sf_metrics_data");

        //! Inser slamiss metric data for each manager.
        //! calculate the percent_compliance for each maneger
        $mgrAssoArray1=array_count_values($jsonObj->mgrKey);

        $mgrArray=array_count_values($slaDLIST->countMgrSlaMissKey);

        $total_miss=0;
        foreach($mgrAssoArray1 as $key => $value) 
        {

            if( array_key_exists($key, $mgrArray))
            {
                $vcount_mgr_miss=  $mgrArray[$key];
            }
            else
            {
                $vcount_mgr_miss=0;
            }

            if( $mgrAssoArray1[$key] < 1)
            {
                $pcount_total_active_mgr=0;
            }
            else
            {
                $pcount_total_active_mgr=$mgrAssoArray1[$key];
            }

            //! with abobe code,  Now we got for a particular manager  total no of jira case & count of violated Jira Cases.

            //! Now just calculate how are not missed the sla.
            $count_total_non_misses=$pcount_total_active_mgr-$vcount_mgr_miss;

            //! The If Block checks if there is any violated cases, if no, the obviously the percentage  = 100
            if ( $vcount_mgr_miss == 0 )
            {
                $percent_compliance=100;
            }
            else
            {
                //! calcaulate the percentage.
                //! Logic is : 100 * how many are not missed under one manager / how many are violated.
                //! I have taken the logic from Burt tool eps_alerts_escal_status.sh
                $percent_compliance= (100*$count_total_non_misses)/$pcount_total_active_mgr; 
            }  

            //! Now to find total no of misses , Just keep adding the missess.
            $total_miss=($total_miss + $vcount_mgr_miss);

            //! Now write into metric db table.



            $obj->manager=$key;

            $obj->No_of_misses_mgr=$vcount_mgr_miss;

            $obj->No_of_active_escalations_mgr=$pcount_total_active_mgr;

            $obj->total_active_escalations=$jsonObj->count_total_jira_active_cases;

            $obj->percent_compliance=$percent_compliance;

            $metricDbInsertObj->populateDbMetricObject($metricDbObj, $obj);

            $metricDbInsertObj->dbMetricsDataInsert($metricDbObj);

        }// end of parent loop

        $total_non_miss=($jsonObj->count_total_jira_active_cases - $total_miss);

        $percent_compliance_overall=round((100*$total_non_miss)/$jsonObj->count_total_jira_active_cases);

        $countOfMangerHavingSlaMiss= array_keys($slaDLIST->countMgrSlaMissKey);
        //! If there is no manager having SLA Miss Violation, then overal percentage =100;
        if ($countOfMangerHavingSlaMiss < 1 ) 
        {
            $percent_compliance_overall=100;
        }



    }
    echo "percent_compliance_overall = ".$percent_compliance_overall."\n";

    $curDir=getcwd();

    chdir('htmlReportTable');

    $htmlReportObj= new createHTMLSlaMissReport($slaDLIST);

    $htmlReportObj->createSlaMissHTMLTable();

    chdir($curDir);

    $now = date_create('now')->format('H');
    $now=2;
    if($now >= 1)
    {

        $text = file_get_contents('./htmlReportTable/slaReportFromList.html');

        if(!empty($text))
        {

            $slaMailObj= new slaMail();

            $cont="Hi All,<br>";

            $cont.="<br>";

            $cont.="<br>";

            $cont.="Please find the list of CPE JIRA Cases whose Resolution is Unresolved for which ESCALATION_STATUS ";

            $cont.="section has not been updated with ‘customer consumable update’ as per SLA. This is an absolute";

            $cont.=" CPE minimum expected deliverable as part of ownership and accountability.";

            $cont.="<br>";

            $cont.="<br>";

            $cont.="<br>";

            $cont.="<p><b>Total number of Active Escalations : $jsonObj->count_total_jira_active_cases</b></p>";

            $cont.="<br>";

            $cont.="<p><b>Percentage compliance for today : $percent_compliance_overall</b></p>";

            $cont.="<br>";

            $cont.=$text;

            $MailId='';
            $readStr="TO_MAIL";
            Util::readMailiId($MailId,$readStr);
            $slaMailObj->fromMailId="Auto_EPS_Alert@Daily_Report";
            $slaMailObj->toMailId=$MailId;

            $readStr="CC_MAIL";
            $MailId='';
            Util::readMailiId($MailId,$readStr);
            $slaMailObj->ccMailId=$MailId;
            $slaMailObj->subject=" Reminder Mail";

            $slaMailObj->content=$cont;

            $mailObj= new sendMail($slaMailObj);

            $mailObj->sendMail();

            echo " Mail is  sent\n";

        }
    }// end of evening or morning if

}
else
{
    echo " resource does not Exists, either Jira Link broken or Check the service accoint user name/password\n";
}


//Close the cURL handle.
curl_close($ch);
?>
