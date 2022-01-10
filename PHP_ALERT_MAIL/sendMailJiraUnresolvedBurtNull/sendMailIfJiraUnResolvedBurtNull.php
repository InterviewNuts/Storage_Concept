<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */

define('PROJECT_PATH', __DIR__);
include_once  (PROJECT_PATH . '/CheckBurtClose/getAllJiraIdNull.php');
include_once  (PROJECT_PATH . '/sendMail/sendMail.php');
include_once  (PROJECT_PATH . '/htmlReportTable/burtNullJiraOpen.php');


$username = 'sf_api_sa';
$password = 'J6aXK4vSOOmBK0KCOhXK2tDuVze3eyBIHJ4RewaVKZkdC7Vc7c3riNdJ4wiH';

/*
 * This URL will get us the resolved Jira Bugs. This one is filtered one.
 */

$url1 = 'https://jira.ngage.netapp.com/rest/api/2/search?jql=project=%22CSD%22%20and%20resolution%20is%20null%20and%20type=%22CPE%20Escalation%22&fields=issuetype,resolution,customfield_15192,customfield_16413,assignee,reporter&maxResults=3000';


$url2 = 'https://jira.ngage.netapp.com/rest/api/2/search?jql=project=%22CSD%22%20and%20resolution%20is%20null%20and%20type=%22CPE%20DU/DL/DC%22&fields=issuetype,resolution,customfield_15192,customfield_16413,assignee,reporter&maxResults=3000';

$burtNotClosedArray=array();
$slaDLIST = new SplDoublyLinkedList();

for ( $i=1;$i <3 ;$i++)
{

    //Initialize cURL.
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    if( $i== 1)
    {
        //Set the URL that you want to GET by using the CURLOPT_URL option.
        curl_setopt($ch, CURLOPT_URL, $url1);
    }
    else
    {
        curl_setopt($ch, CURLOPT_URL, $url2);
    }
    //Set CURLOPT_RETURNTRANSFER so that the content is returned as a variable.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //Set CURLOPT_FOLLOWLOCATION to true to follow redirects.
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    //Execute the request.
    $data = curl_exec($ch);

    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);



    //200 = OK
    if($statusCode == '200')
    {
        $jsonObj = json_decode($data);

        //! now loop through the parent respApi to get the each burt restapi( changelog rest api will be extracted).
        foreach($jsonObj->issues as $key=>$value)
        {
            //! get the Jira change log  endpoint
            $burtUrl=CheckBurtClose::getJsonApiOfEachBurt($value);

            //Execute the request.
            //Set the URL that you want to GET by using the CURLOPT_URL option.
            curl_setopt($ch, CURLOPT_URL, $burtUrl);

            $burtData = curl_exec($ch);

            $burtJsonObj = json_decode($burtData);

            //! Get the  required details of a JIRA _ID, which is required 
            $jsonBurt=CheckBurtClose::getBurtIdsOpenAndJiraNull($burtJsonObj);
            //  CheckBurtClose::getJiraCaseInfo($burtJsonObj);
            if($jsonBurt != null)
            {
                //! Push to The List    
                //$slaDLIST->push($jsonBurt);
                //! Now we have all the Jira Ids which are closed in Jira Tool and not closed in the BURT Tool.
                array_push($burtNotClosedArray, $jsonBurt);
            }
        }
    }
}


//print_r($burtNotClosedArray);

//! host=burtdw-open.rtp.openeng.netapp.com;port=1526;sid=burtopen, table name = burt_main

//$myfile = fopen("./burtCloseFile.txt", "w") or die("Unable to open file burtCloseFile.txt!\n");

//! host=burtdw-open.rtp.openeng.netapp.com;port=1526;sid=burtopen, table name = burt_main

//! Parent loop
//! This loop will loop through the entire  double link list, while travesing , it is traversed & will keep the itm in the list itself.

//! It is temporary solution, All the ids are written into flat file
/*
   foreach ($burtNotClosedArray as $jiraCase)
   {
   $burtid=$jiraCase->EPSBurtId;
   fwrite($myfile, $burtid); 
   fwrite($myfile, ":"); 
   fflush($myfile);
   }
   fclose($myfile);
 */

$curDir=getcwd();

chdir('htmlReportTable');

$htmlReportObj= new createHTMLSlaMissReport($burtNotClosedArray);

$htmlReportObj->createSlaMissHTMLTable();

chdir($curDir);

$now = date_create('now')->format('H');


$text = file_get_contents('./htmlReportTable/burtNullJiraOpen.html');

if(!empty($text))
{

    $slaMailObj= new slaMail();

    $cont="Hi All,<br>";

    $cont.="<br>";

    $cont.="<br>";

    $cont.="Please find the list of CPE Jira Case where it's JIRA Resolution is Unresolved in JIRA Tool But associated Burt ";

    $cont.="Id is NULL.";

    $cont.="<br>";

    $cont.="<br>";

    $cont.=$text;

//    $slaMailObj->toMailId="sukanyar@netapp.com";
    //$slaMailObj->toMailId="kkranthi@netapp.com";
    $slaMailObj->toMailId="haramoha@netapp.com";
    $slaMailObj->ccMailId="haramoha@netapp.com";
    $slaMailObj->fromMailId="Auto_EPS_Alert@Daily_Report";


    $slaMailObj->subject=" Reminder Mail";

    $slaMailObj->content=$cont;


    $mailObj= new sendMail($slaMailObj,"reminder");

    $mailObj->sendMail();

    echo " Mail is  sent\n";

}// End of  If block 











//Close the cURL handle.
curl_close($ch);
?>
