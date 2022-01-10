<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */

define('SLA_PATH', __DIR__);

if( !empty($argv[1]))
{
    $param = $argv[1];
}
else
{
    $param="";
}

if($param == "PARSER" )
{
    include_once (SLA_PATH . '/getSolidFireEmpParserInfo.php');
    echo " parser  use https://onestop.netapp.com/ to know about SolidFire Engineer\n";
}
else
{
    echo " Oracle DB  id used to know about SolidFire Engineer\n";
    include_once (SLA_PATH . '/getSolidFireEmpInfo.php');
}
date_default_timezone_set("America/Chicago");
//date_default_timezone_set("Asia/karachi");
///date_default_timezone_set("Asia/Kolkata");
class SlaMiss {

    private static $count=0;

    private static $countENS=0;

    //! This function will return only week days & exclude current days ( Todays date).
    //! Just pass the start date &  current date, It will calculate the number of days
    //! between these two days and exclude the week ends. The day you run this script, it will also exclude today from the calculation
    private static function countOnlyWeekDays($startDt, $endDt)
    {
        $begin= date_create($startDt);

        $start = strtotime($begin->format('Y-m-d'));

        $end = strtotime($endDt->format('Y-m-d'));

        while(date('Y-m-d', $start) < date('Y-m-d', $end))
        {
            self::$count += date('N', $start) < 6 ? 1 : 0;
            $start = strtotime("+1 day", $start);
        }//! End of  while

        return self::$count;

    }//! end of 

    //! This is legacy code copied from old burt tool alert script  /u/rsaklani/Scripts location
    //!  This legacy code only calculate the week ends. If you pass the two dates like start date & end date
    //! It calculate  the number of Satur Day & Sundays are there in between two days.
    //! Currently this script is not in use as, Script use countOnlyWeekDays() function.
    public static function countOnlyWeekEnds($startDt,$endDt)
    {
        $output = shell_exec("./count_weekends.sh $startDt $endDt");

        return $output;
    }

    //! This function add new feild to the new Json Object, these information is required for mail & other calculation purposes.
    private static function addFieldToJsonObj(&$post_data, $str,$toFrom)
    {

        //!  string spilt
        $myArray = array();

        $i=0;

        $frmString=$str;

        $token = strtok($frmString, "\r\n");

        while ($token !== false)
        {
            $myArray[$i]=$token;
            $token = strtok("\r\n");
            $i++;
        }
        //! just take the contents there in the fromString
        if($toFrom == "FROM")
        {
            $post_data->CustomerWho=substr($myArray[0],strpos($myArray[0],":")+1);

            $post_data->CustomerWhen=substr($myArray[2],strpos($myArray[1],":")+1);

            $post_data->CustomerWhat=substr($myArray[1],strpos($myArray[2],":")+1);

        }
        //! just take the contents there in the  toString
        else
        {
            $post_data->AssigneeWho=substr($myArray[0],strpos($myArray[0],":")+1);

            $post_data->AssigneeWhen=substr($myArray[1],strpos($myArray[1],":")+1);

            $post_data->AssigneeWhat=substr($myArray[2],strpos($myArray[2],":")+1);
        }

    }


    //! This function will calculate these number of days not modified by the CES SolidFire Engineer
    //! the particulat Jira Case.
    private static function getNumberOfDaysNotModified($jsonObj)
    {
        $x = date("Y-m-d");

        $today=date_create($x);

        self::$count=0;

        return SlaMiss::countOnlyWeekDays($jsonObj, $today);
    }


    //!  pass through the changelog json Root obj
    //! It recive the changelog rest api for the particular Burt#
    //! It also calculate the number of Escalation Next Steps available for the particular
    //! Jira Case. This Escalation Next Steps will used for further processing.
    //! as this will be used for SLA Logic violation.
    //! The Sla Logic is based on this. If the Escalation Next Step does not have a update By SolidFire Engineer,
    //! then this will be a case for Sla Logic Violation.

    public static function getLastUpdateOnEscalationNextSteps($jsonObj)
    {

        $post_data = new stdClass();

        $count=0;

        $ensJson;

        $keyCSD;

        self::$countENS=0;

        if(!empty($jsonObj))
        {
            //! We dont care if the Escalatio Priority is not set or P4-Low ( This customfield_15192 is Escalation Priority)
            if(  empty($jsonObj->fields->customfield_15192->value) or  $jsonObj->fields->customfield_15192->value=="P4-Low")
                return null;

            //! just count the Escalation Next Steps, if it is not there , then it means , 
            //! customer has not updated Or it is not yest viaolated
            $ensDList=self::getCountOfENS($jsonObj);

            $ensCount=$ensDList->count();

            //    if( $ensCount == 0 and empty($jsonObj->solidFireEngLastUpdateTime))
            if( $ensCount == 0 and  SlaMiss::getNumberOfDaysNotModified($jsonObj->fields->created) < 1)
                $newJiraId=true;
            else
                $newJiraId=false;
            if( !$newJiraId)
            {
                $post_data->jiraId=$jsonObj->key;

                if( !empty($jsonObj->fields->issuetype->name))
                    $post_data->issuetype=$jsonObj->fields->issuetype->name;
                else
                    $post_data->issuetype="NULL";

                $post_data->SupportCaseNumber=(!empty($jsonObj->fields->customfield_15249))?
                    ($jsonObj->fields->customfield_15249):("NULL");

                if(!empty($jsonObj->fields->customfield_15148))
                    $post_data->CustomerAccountName=$jsonObj->fields->customfield_15148;
                else
                    $post_data->CustomerAccountName="NULL";

                if(!empty($jsonObj->fields->customfield_15192->value))
                    $post_data->EscalationPriority=$jsonObj->fields->customfield_15192->value;
                else
                    $post_data->EscalationPriority="NULL";

                if(!empty($jsonObj->fields->created))
                    $post_data->IssueCreationDate=$jsonObj->fields->created;
                else
                    $post_data->IssueCreationDate="NULL";

                if(!empty($jsonObj->fields->status->name))
                    $post_data->Status=$jsonObj->fields->status->name;
                else
                    $post_data->Status="NULL";

                /*        if(!empty($jsonObj->fields->reporter->displayName))
                          $post_data->Manager=$jsonObj->fields->reporter->displayName;
                          else
                          $post_data->Manager="NULL";

                          if(!empty($jsonObj->fields->reporter->key))
                          $post_data->ManagerKey=$jsonObj->fields->reporter->key;
                          else
                          $post_data->ManagerKey="NULL";

                          if(!empty($jsonObj->fields->reporter->emailAddress))
                          $post_data->ManagerEmail=$jsonObj->fields->reporter->emailAddress;
                          else
                          $post_data->ManagerEmail="NULL";

                 */
                if(!empty($jsonObj->fields->customfield_19230))
                    $post_data->EPSBURTid=$jsonObj->fields->customfield_19230;
                else
                    $post_data->EPSBURTid="NULL";

                if(!empty($jsonObj->fields->assignee->key))
                {

                    if( stristr($jsonObj->fields->assignee->key,"JIRAUSER"))
                        $userName=$jsonObj->fields->assignee->name;
                    else
                        $userName=$jsonObj->fields->assignee->key;

                    $post_data->assigneeKey=$userName;

                }
                else
                    $post_data->assigneeKey="NUL";

                if(!empty($jsonObj->fields->assignee->name))
                    $post_data->escal_to_eng=$jsonObj->fields->assignee->name;
                else
                    $post_data->escal_to_eng="NUL";


                $post_data->RCA_Delivered="no";

                $post_data->ManagerKey=getSolidFireEmpInfo::getMgrKey($post_data->assigneeKey);

                //! Take the Object where update t done by SolidFire Engineer.
                //! The logic is simple, we have taken all the Escalation Next Steps & kept it in a double link list
                //! set it like Last LIFO, and check  the update done by Solid Fire Engineer, By taking the Update done whome( Author)
                //! and the same author gets checked by quering the Oracle DB emplyee DB Table. This SQL Query will tell me if autho is SolidFire Engineer or not.
                //! if the author is not a Solid Fire Engineer, the go back to Double Link List, do the same check. 
                //! Wehere ever you find the updation Done by Solid Fire Engineer, Take that particular Node.
                //! If don't find any update done by solifFire Engineer, then  go to else part.
                $ensLastUpadateList=SlaMiss::getLastUpdateInfoDoneBySolidFireEng($ensDList,$jsonObj);

                //! just count the Escalation Next Steps, if it is not there, Then based on the Priority,
                //! days_since_last_modified will be taking the value from the Jira Bug priority.
                //! & owner name will be the engineer name., Look at the else part for this logic.
                //! we now get the last update done the SolidFire Engineer

                //! There will be 3 cases.
                //! Case 1: There is a Escalation Next Syep section there in Jira Case &Update Done By SolidFire Engineer
                //! Case 2: There is a Escalation Next Syep section there in Jira Case & None of the update are done by SolidFire engineer
                //! Case 3: There is NO Escalation Next Syep section there in Jira Case Itself.

                //! Case 1 : "Escalation Next Steps: Found & Update done by SolidFire Enggineer
                if (!empty($ensLastUpadateList))
                {
                    //! Just populate the information from the Last update done by the SolidFire Engineer
                    $post_data->field=$ensLastUpadateList->SFfield;

                    $post_data->fieldtype=$ensLastUpadateList->SFfieldtype;

                    $post_data->from=$ensLastUpadateList->SFfrom;

                    $post_data->fromString=$ensLastUpadateList->SFfromString;

                    $post_data->toString=$ensLastUpadateList->SFToString;

                    $post_data->solidFireEngLastUpdateTime=$ensLastUpadateList->solidFireEngLastUpdateTime; 

                    $post_data->days_since_last_modified=SlaMiss::getNumberOfDaysNotModified($ensLastUpadateList->solidFireEngLastUpdateTime);

                    $post_data->solidFireEngAuthorDisplayName=$ensLastUpadateList->solidFireEngAuthorDisplayName;

                    $post_data->solidFireEngAuthorName=$ensLastUpadateList->solidFireEngAuthorName;

                    $post_data->solidFireEngAuthorKey=$ensLastUpadateList->solidFireEngAuthorKey;

                    $post_data->solidFireEngEmail=$ensLastUpadateList->solidFireEngEmail;

                    //              $post_data->ManagerKey= $ensLastUpadateList->ManagerKey;

                    //   $post_data->ManagerKey=getSolidFireEmpInfo::getMgrKey($post_data->solidFireEngAuthorKey);

                    $post_data->EscalationFound="YES";

                }

                //! The ELSE Part fals into Case 2 & Case 3.
                else
                {
                    //! Since  there is NO Escalation Next Steps ,Then based on the Priority,
                    //! days_since_last_modified will be taking the value from the Jira Bug priority.
                    //! & owner name will be the engineer name., Look at the else part for this logic.

                    //! Case 2 : "Escalation Next Steps: Not Found OR
                    //! Case 3 :  Update Not done by SolidFire Enggineer
                    $days=0;
                    if( $post_data->EscalationPriority != "NULL")
                    {
                        if($post_data->EscalationPriority == "P1-Critical")
                        {
                            $days=1; 
                        }
                        else  if($post_data->EscalationPriority == "P2-High")
                        {
                            $days=3;
                        }
                        else if($post_data->EscalationPriority == "P3-Medium")
                        {
                            $days=5;
                        }
                    }
                    else
                    {
                        //! Since  the Priority is not set , I assumed it as a Midium one. Hence set to deafult is 5; as P3-Medium ==  5 days
                        //  $days=5;
                        //Since EscalationPriority is not set , Just ignore the case as of now , But I will discuss with Anji.
                        return null;
                    }

                    $post_data->days_since_last_modified=$days;

                    $post_data->EscalationFound="NO";

                    $post_data->solidFireEngLastUpdateTime=$jsonObj->solidFireEngLastUpdateTime; 

                    $post_data->lastUpdateField=$jsonObj->lastUpdateField;

                    //$post_data->toString=$jsonObj->lastUpdateToString;

                    //$post_data->toString=$jsonObj->lastUpdateToString;

                    //! Anji suggested that ,Since the update not done by SolidFire Engineer , hen put comment like below.
                    $post_data->toString="No Update available in Escalation Next Steps by CSE SolidFire CPE Engineer";

                    /*          $post_data->solidFireEngAuthorDisplayName=$jsonObj->solidFireEngAuthorDisplayName;

                                $post_data->solidFireEngAuthorName=$jsonObj->solidFireEngAuthorName;

                                $post_data->solidFireEngAuthorKey=$jsonObj->solidFireEngAuthorKey;

                                $post_data->solidFireEngEmail=$jsonObj->solidFireEngEmail;

                     */

                    $post_data->solidFireEngAuthorDisplayName=$jsonObj->fields->assignee->displayName;

                    $post_data->solidFireEngAuthorName=$jsonObj->fields->assignee->name;

                    $post_data->solidFireEngAuthorKey=$jsonObj->fields->assignee->key;

                    $post_data->solidFireEngEmail=$jsonObj->fields->assignee->emailAddress;

                    //   $post_data->ManagerKey=getSolidFireEmpInfo::getMgrKey($post_data->solidFireEngAuthorKey);

                    // $manager=getSolidFireEmpInfo::getMgrKey($post_data->solidFireEngAuthorKey);

                    // $post_data->ManagerKey=$manager;

                }// end of else part

                return $post_data;
            }
        }// end of if
    }




    private static function iSSolidFireEng($ensDList,$jsonObj)
    {
        //! This code is temporary, I need to call a Oracle db check for the authentication of the Solid File engineer and its manager
        if( $ensDList->solidFireEngAuthorKey == $jsonObj->fields->assignee->key)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    private static function getLastUpdateInfoDoneBySolidFireEng($ensDList, $jsonObj)
    {
        //! Now loop through the detail of changes in Escalation Next Steps
        //! Here intention is to know the changes done by SolidFire Enginneer or Not.
        //! If changes done by by SolidFire Enginneer, then only take the updated time & related info.
        if(!$ensDList->isEmpty() )
        {

            //!LIFO (Last In First Out) , (Intention is from last Update done by solidfirE ENG) just check the changes done by author,
            //! is actually, a solid Fire Enginneer or not, if not , the go back and the check for the same.
            $ensDList->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);

            $lastNode =$ensDList->top();

            for ($ensDList->rewind(); $ensDList->valid(); $ensDList->next()) 
            {

                $userName=$ensDList->current()->solidFireEngAuthorKey;

                if(getSolidFireEmpInfo::isSolidFireEngineer($userName))
                {
                    $engInfo=getSolidFireEmpInfo::getSolidFireEngineerInfo($userName);

                    $ensDList->current()->ManagerKey=$engInfo->ManagerKey;

                    //     $ensDList->current()->ManagerKey=getSolidFireEmpInfo::getSolidFireEngineerInfo($userName);

                    $ensDList->current()->solidFireEngAuthorDisplayName=$engInfo->solidFireEngAuthorDisplayName;

                    $ensDList->current()->solidFireEngAuthorName=$engInfo->solidFireEngAuthorName;

                    $ensDList->current()->solidFireEngAuthorKey=$engInfo->solidFireEngAuthorKey;

                    $ensDList->current()->solidFireEngEmail=$engInfo->solidFireEngEmail;

                    //  $ensDList->current()->ManagerKey=getSolidFireEmpInfo::getMgrKey($userName);
                    $ensDList->current()->notSolidFireEngg=false;

                    return $ensDList->current();
                }
            }
            //! Now we have Escalation Next Steps But non of the Escalation Next Steps are updated by SolidFire Eng.

            $userName=$lastNode->solidFireEngAuthorKey;

            //  $engInfo=getSolidFireEmpInfo::getSolidFireEngineerInfo($userName);

            // $lastNode->Manager=$engInfo->Manager;

            $lastNode->solidFireEngAuthorDisplayName=$jsonObj->fields->assignee->name;

            $lastNode->solidFireEngAuthorName=$jsonObj->fields->assignee->key;

            $lastNode->solidFireEngAuthorKey=$jsonObj->fields->assignee->key;

            $lastNode->toString=$jsonObj->lastUpdateToString;

            $lastNode->notSolidFireEngg=true;

            // $lastNode->ManagerKey=getSolidFireEmpInfo::getMgrKey($userName);
            return null;

        }
        //! if owner & escalation next step does not match, then just return last update details.

        //! Now "Escalation Next Steps" found But the Updates not done  by the SolidFire eng.
        //! In this case take the last update done by the assignee & considered it as "Escalation Next Steps" Not found case.
        //! here populate the information like manager Name . 

        /*   $userName=$lastNode->solidFireEngAuthorKey;

             $engInfo=getSolidFireEmpInfo::getSolidFireEngineerInfo($userName);

             $lastNode->Manager=$engInfo->Manager;

             $lastNode->ManagerKey=getSolidFireEmpInfo::getSolidFireEngineerInfo($userName);

             $lastNode->solidFireEngAuthorDisplayName=$engInfo->solidFireEngAuthorDisplayName;

             $lastNode->solidFireEngAuthorName=$engInfo->solidFireEngAuthorName;

             $lastNode->solidFireEngAuthorKey=$engInfo->solidFireEngAuthorKey;

             $lastNode->solidFireEngEmail=$engInfo->solidFireEngEmail;

             $lastNode->ManagerKey=getSolidFireEmpInfo::getMgrKey($userName);

             $lastNode->notSolidFireEngg=true;

        //      print_r($lastNode);
         */
        return null;
        //    return $lastNode;
    }






    /*
     * pass the changelog JSON root object. This function will pass through change log, & try to check the Escalation Next Steps
     *  in the Jira BUG Change log & if found push it to a double link list & return.
     */
    public static function getCountOfENS(&$jsonObj )
    {   
        $ensData = new stdClass();

        $found=false;

        $onceENSFlag=false;

        $ensDList = new SplDoublyLinkedList();

        self::$countENS=0;

        if(!empty($jsonObj))
        {
            //! This is temporary provision. I will get it checked with Oracle DN for authentication of SolidFire Engineer
            //$ensData->owner=$jsonObj->fields->assignee->key;

            /*  This the example how the history is looking like. 
                "histories": [
                {
                "id": "5403305",
                "author": {
                "self": "https://jira.ngage.netapp.com/rest/api/2/user?username=rossetto",
                "name": "rossetto",
                "key": "rossetto",
                "emailAddress": "Edward.Rossetto@netapp.com",
                "avatarUrls": {
                "48x48": "https://jira.ngage.netapp.com/secure/useravatar?avatarId=10122",
                "24x24": "https://jira.ngage.netapp.com/secure/useravatar?size=small&avatarId=10122",
                "16x16": "https://jira.ngage.netapp.com/secure/useravatar?size=xsmall&avatarId=10122",
                "32x32": "https://jira.ngage.netapp.com/secure/useravatar?size=medium&avatarId=10122"
                },
                "displayName": "Rossetto, Edward",
                "active": true,
                "timeZone": "America/Denver"
                },
                "created": "2020-09-24T18:45:15.540-0500",
                "items": [
                {
                "field": "assignee",
                "fieldtype": "jira",
                "from": null,
                "fromString": null,
                "to": "rossetto",
                "toString": "Edward Rossetto"
                },
                {
                "field": "project",
                "fieldtype": "jira",
                "from": "19128",
                "fromString": "NetApp HCI Escalation Service Desk",
                "to": "19108",
                "toString": "CPE Service Desk (Sustaining)"
                }
                ]
                [items] => Array
                (
                [0] => stdClass Object
                (
                [field] => RCA Delivered
                [fieldtype] => custom
                [from] => 
                [fromString] => 
                [to] => 2021-04-15T11:38:00-0500
                [toString] => 15/Apr/21 12:38 PM
                )
                }
             */
            foreach($jsonObj->changelog->histories  as $key=>$value)
            {
                foreach($value->items as $key1=>$value1)
                {
                    if( stristr($value->author->key,"JIRAUSER"))
                        $userName=$value->author->name;
                    else
                        $userName=$value->author->key;

                    if( ($value1->field == "Escalation Next Steps") and (getSolidFireEmpInfo::isSolidFireEngineer($userName)) )
                    {

                        //! "Escalation Next Steps"  Found But the update not done by the SolidFire Engg, then it is not consideded as 
                        //! "Escalation Next Steps" FOUND. It is considered as No ""Escalation Next Steps" found.
                        // $userName=$value->author->key;
                        $found=true;

                        $onceENSFlag=true;

                        if(!empty($value1->toString))
                            $ensData->SFToString=$value1->toString;
                        else
                            $ensData->SFToString=null;

                        if(!empty($value1->field))
                            $ensData->SFfield=$value1->field;
                        else 
                            $ensData->SFfield=null;

                        if(!empty($value1->fieldtype))
                            $ensData->SFfieldtype=$value1->fieldtype;
                        else
                            $ensData->SFfieldtype=null;

                        if(!empty($value1->from))
                            $ensData->SFfrom=$value1->from;
                        else
                            $ensData->SFfrom=null;

                        if(!empty($value1->fromString))
                            $ensData->SFfromString=$value1->fromString;
                        else
                            $ensData->SFfromString=null;
                    }
                    //"RCA Delivered"
                    if( $value1->field == "RCA Delivered")
                    {
                        $ensData->RCA_Delivered="yes";
                    }

                    //! Since there is no Escalation Next Steps OR( There is "Escalation Next Steps", But update done by Assign enng who is not a SolidFire Engg, 
                    //! Hence these information need to be collected for mail purposes. ( here take the last update done the assignee.
                    $jsonObj->lastUpdateToString=$value1->toString;

                    $jsonObj->lastUpdateField=$value1->field;

                    $userName=$value->author->key;

                    //           $jsonObj->ManagerKey = getSolidFireEmpInfo::getMgrKey( $userName);
                }

                //! if Jira Case has Escalation Next Steps.
                if( $found )
                {
                    if(!empty($value->created))
                        $ensData->solidFireEngLastUpdateTime=$value->created;
                    else
                        $ensData->solidFireEngLastUpdateTime=null;

                    if(!empty($value->author->displayName))
                        $ensData->solidFireEngAuthorDisplayName=$value->author->displayName;
                    else
                        $ensData->solidFireEngAuthorDisplayName=null;

                    if(!empty($value->author->name))
                        $ensData->solidFireEngAuthorName=$value->author->name;
                    else
                        $ensData->solidFireEngAuthorName=null;

                    if(!empty($value->author->key))
                        $ensData->solidFireEngAuthorKey=$value->author->key;
                    else
                        $ensData->solidFireEngAuthorKey=null;

                    if(!empty($value->author->emailAddress))
                        $ensData->solidFireEngEmail=$value->author->emailAddress;
                    else
                        $ensData->solidFireEngEmail=null;

                    $ensDList->push($ensData);
                }

                //! if there is no Escalation Next Syeps available in the Jira Case.
                //! Then take these bare minumum  data for Mail & other purposes.
                if(!$onceENSFlag)
                {

                    if(!empty($value->created))
                        $jsonObj->solidFireEngLastUpdateTime=$value->created;
                    else
                        $jsonObj->solidFireEngLastUpdateTime=null;

                    $userName=$value->author->key;
                    //  $jsonObj->ManagerKey=getSolidFireEmpInfo::getMgrKey($userName);
                    //! Now we need to take the last updated time as well.
                    if(!empty($value->created))
                        $jsonObj->solidFireEngLastUpdateTime=$value->created;
                    else
                        $jsonObj->solidFireEngLastUpdateTime=null;

                    if(!empty($value->author->displayName))
                        $jsonObj->solidFireEngAuthorDisplayName=$value->author->displayName;
                    else
                        $jsonObj->solidFireEngAuthorDisplayName=null;

                    if(!empty($value->author->name))
                        $jsonObj->solidFireEngAuthorName=$value->author->name;
                    else
                        $jsonObj->solidFireEngAuthorName=null;

                    if(!empty($value->author->key))
                        $jsonObj->solidFireEngAuthorKey=$value->author->key;
                    else
                        $jsonObj->solidFireEngAuthorKey=null;

                    if(!empty($value->author->emailAddress))
                        $jsonObj->solidFireEngEmail=$value->author->emailAddress;
                    else
                        $jsonObj->solidFireEngEmail=null;
                }
                $found=false;

                $onceENSFlag=false;

                $ensData=null;
            }


        }

        //! Now  we have  the list of Escalation Next Steps in a single JIRA Bug
        return $ensDList;
    }


    //! this function will get  return Jira change log  URL
    //! example "https://jira.ngage.netapp.com/rest/api/2/issue/819155?expand=changelog"
    public static function getJsonApiOfEachBurt($json)
    {
        return $json->self."?expand=changelog";
    }

    private static function getBurtLastUpdateDate($json)
    {
        $dateString=$json->lastUpdateTime;

        $dateTimeObj=date_create($dateString);

        $date=date_format($dateTimeObj, 'd-m-y');

        return $date;
    }

    private static function getBurtLastUpdateHour()
    {

        $dateTimeObj = new \DateTime($json->lastUpdateTime);

        //echo date_format($date, 'Y-m-d H:i:s');

        // $dateString = $json->lastUpdateTime;

        // $dateTimeObj = date_create($dateString);

        $timeH = date_format($dateTimeObj, 'H');

        return $timeH;
        /*
           $dateString = $json->lastUpdateTime;
           $dateTimeObj = date_create($dateString);
           $timeH = date_format($dateTimeObj, 'H');
           return $timeH;
         */
    }

    private static function getBurtLastUpdateMin()
    {
        /*
           $dateString = $json->lastUpdateTime;
           $dateTimeObj = date_create($dateString);
         */

        $dateTimeObj = new \DateTime($json->lastUpdateTime);

        $timeM = date_format($dateTimeObj, 'i');

        return $timeM;

    }


    //! These time api is just given for futere use, But not used by script as of now.
    private static function getCurrentDateiAndTime()
    {
        return date_format(date_create('now'),"Y-m-d-H-i");
    }

    //! These time api is just given for futere use, But not used by script as of now.
    private static function getCurrentDate()
    {
        return date_create(date("Y-m-d"));
    }

    private static  function getCurrentTimeHour()
    {
        return date_format(date_create('now'),"H");
    }

    private static function getCurrentTimeMinutes()
    {
        return date_format(date_create('now'),"i");
    }

    //! it will get yus the difference between the dates. Just API is given for future use. This API is not currently used by the script
    private  static function getDateDifference($bugDate)
    {
        $x = date("Y-m-d");

        $today=date_create($x);

        $diff=date_diff($today, date_create($bugDate)  );

        return $diff->format("%a");
    }
    //! This function will check if the Jira case came just today, if todat then it has not violated SLA , as expected.
    //! so ignore these type of cases, as it is clear that , it has not violated the sLA.
    //! No need to go through the entire process to to check.
    private static function checkLessThen24Hours($dateStart)
    {
        $date2 = date_create(SlaMiss::getCurrentDateiAndTime());

        $difference = date_diff(date_create($dateStart), $date2); 

        return $difference->format('%a');
    }




    //! This function deals with the Jira State. It read the states from the ./config/slaConfig.cfg  file.
    //! any  JIRA states , you want to exclude from the SLA Logic, then please go to the above path & add the Jira Valid states there in the .cfg file.
    //! You need to add the states in the below line. You need to add next to the string "EXCLUDED_JIRA_STATES=", As this function reads the
    //! EXCLUDED_JIRA_STATES ="CLOSED:RESOLVED:DONE". 
    //! reads the string "EXCLUDED_JIRA_STATES" ans Jira Case state is check with the states mentioned in the config file( Remember you need to add the 
    //! Jira States with this string only "EXCLUDED_JIRA_STATES"!!!!.
    //! Any line starts with '#' symbol in the cfg file will be ignore. 
    //! If you want to add some comment, then please add comments, which must be starts withg '#'.
    //! For any other states if you want to add, the add the your"SEARCH STRING="states"




    //! Example of  slaConfig.cfg
    //!---------------------------
    //! Content of  cat slaConfig.cfg
    //! #This JIRA States need to be separated by ":". If you want to add other confi strings, the do it like below example is given below
    //! #MY_CONFIG_PARAM="XXX:XXX:XXX"
    //! EXCLUDED_JIRA_STATES ="CLOSED:RESOLVED:DONE"

    private static function getJiraBugState($state,$excludeString )
    {
        $flag=false;

        $jiraState='';

        //! check for the platfor. Though this script is written only for Linux, But platform is taken care
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        {
            $confFile="config/slaConfig.cfg";

            if( file_exists($confFile))
            {
                $flag=true;
            }
        }
        else
        {
            $confFile="./config/slaConfig.cfg";

            if( file_exists($confFile))
            {
                $flag=true;
            }
        }
        //! If file exist 
        if($flag)
        {
            $fileObj = new SplFileObject($confFile);

            while (!$fileObj->eof())
            {
                $line=$fileObj->fgets();
                //! ignore the '#'
                if( substr($line,0,1) != "#")
                {
                    //! Match the string "EXCLUDED_JIRA_STATES"
                    if (preg_match("/\b$excludeString\b/i", $line))
                        //! if the string "EXCLUDED_JIRA_STATES" matched , then check for Jira states mentioned in the .cfg file.
                        if (preg_match("/\b$state\b/i", $line))
                            return true;
                        else
                            return false;
                }

            }// end of while

        }// end of if
        else
        {
            echo "./config/slaConfig.cfg is not available , please create the ./config/slaConfig.cfg File and have the Jira states in this file\n";
        }  

    }// end of function




    //! this function is not in use, This function reads the .conf file & it is in json format  
    //! So you need to add in json format. 
    //! this is tested  & working , but not in use. Just Api is given for future use.


    //! Example
    //!cat slaState.conf
    //![
    //!   {"state":"CLOSED"},
    //!  {"state":"RESOLVED"},
    //!  {"state":"DONE"}
    //!]

    private static function getJiraBugStateUsingJson($jsonOb )
    {

        $jiraState='';

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
        {
            $confFile="config/slaState.conf";
            if( file_exists($confFile))
            {
                $jiraState = file($confFile);
            }
        }
        else
        {
            $confFile="./config/slaState.conf";
            if( file_exists($confFile))
            {
                $jiraState = file($confFile);
                // print_r($jiraState);
            }
        }  

        $text='';
        foreach( $jiraState  as $line)
        {
            if( $line[0] != '#' )
            {
                $text.=$line;
            }
        }

        $jsonStateObj= json_decode($text);

        $statusFlag=false;

        foreach( $jsonStateObj as $state )
        {
            //if( $jsonOb->Status == $state->state )
            if( strcasecmp($jsonOb->Status,$state->state ) == 0)
            {
                $statusFlag=true;
                return  $statusFlag;
            } 
        }
        return $statusFlag;
    }


    //! This function is used to check for the Jira Case , if it has violated the Sla defined logic or not.
    //! this checks the priority of the Jira Cases.
    //! Before starting, it just ignore few cases like , The Jira case should not be in less then 24 hours, & the states mentioned in the .cfg file 
    //! , should not be there in Jira case. Means, The jira  Case's State must not be having the any of the states mentioned in the .cfg file. As , these states
    //! mentioned in the .cfg file are not  required for Sla logic.
    private static function checkSLAViolated($jsonObj)
    {
        // This is required as  we need to check based on the hour , whether it is less then 24 hrs, as only date will not tell the correct date.
        //if( (SlaMiss::checkLessThen24Hours($jsonObj->solidFireEngLastUpdateTime)>= 1 ) and  ( !SlaMiss::getJiraBugState($jsonObj->Status,"EXCLUDED_JIRA_STATES" )   ))
        if( !SlaMiss::getJiraBugState($jsonObj->Status,"EXCLUDED_JIRA_STATES" ) )
        {

            //! here Number of days not modified is required , to chech whethat it has crossed the time  line for the Particular Jira case.
            //! P1-Critical  ==> as per SLA , it must be updated withis 1 day.
            //$days=SlaMiss::getNumberOfDaysNotModified($jsonObj->solidFireEngLastUpdateTime);
            $days=$jsonObj->days_since_last_modified;

            //! Case 2 and Case 3 fals here, As there is no Escalation Next Sep found or Solid Fire Engineer has not updated
            //! then check for Violation and to keep the code clean, I have matained a separate If BLOCK, DID Not intentinaly clubbed with other condition.
            if( $jsonObj->EscalationFound=="NO")
            {
                $issueAge=SlaMiss::getNumberOfDaysNotModified($jsonObj->IssueCreationDate);
                if($jsonObj->EscalationPriority == "P1-Critical")
                {
                    if($issueAge > 1)
                        return "YES";
                }
                else if($jsonObj->EscalationPriority == "P2-High")
                {
                    if($issueAge > 3)
                        return "YES";
                }
                else if($jsonObj->EscalationPriority == "P3-Medium")
                {
                    if($issueAge > 5)
                        return "YES";
                }
                return "NO";


            }
            if( $jsonObj->EscalationFound=="YES")
            {
                if($jsonObj->EscalationPriority == "P1-Critical")
                {
                    // echo "P1-Critical\n";
                    if($days > 1)
                        return "YES";
                }
                //! P2-HIGH  ==> as per SLA , it must be updated withis 3 days.
                else  if($jsonObj->EscalationPriority == "P2-High")
                {
                    //  echo "P2-High\n";
                    if($days > 3)
                        return "YES";
                }
                //! P3-HIGH  ==> as per SLA , it must be updated withis 5 days.
                else if($jsonObj->EscalationPriority == "P3-Medium")
                {
                    //  echo "P3-Midium\n";
                    if($days > 5)
                        return "YES";
                }
            }
        }
        $days=0;
        return "NO";

    }


    //! this function will return YES or NO.
    //! Means , if violated YES , NO, otherwise.
    public static function getSLAMissForBurt($jsonBurtInfo)
    {
        if(!empty($jsonBurtInfo))
        {
            $yes=SlaMiss::checkSLAViolated($jsonBurtInfo);
            return $yes;
        }
        return "NO";
    }

    //! this function will  get me the change log rest api for each Burt#
    public static function getJsonApiBurt($json)
    {
        return $json->self."?expand=changelog";
    }



    //! as name suggest , will get us EscalationNextSteps
    public static function getEscalationNextSteps($jsonObj)
    {
        foreach($jsonObj->issues as $key=>$value)
        {
            $burtRestApliLink=getJsonApiBurt($value->items);

        }
    }






}//End Of SlaMiss class

//echo SlaMiss::countOnlyWeekEnds("2021-03-11", "2021-03-25");
// lastUpdateTime
?>


