<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 *  All rights reserved.
 */

date_default_timezone_set("Asia/Kolkata");

class checkBurtClose 
{

    private static $count=0;


    private static function isBurtIdClosed($burtid)
    {
        return BurtInfo::isBurtClosed($burtid);
    }


    public static function getBurtClosedDetails(&$jsonBurt)
    {
        self::$count++;
        $yes=false;
        if(!empty($jsonBurt))
        {
            //check is it is closed or not. We need to call oracle database to know the status of the Burt.
            //! just get the list of Jira Burt which are in Closed state.
            //! Since It is closed  then just  take the Burt id to chck if it is closed in the Burt Tool ?
            //! EPS Burt Id: (https://burtview.netapp.com/burts/1386666#)
            if(!empty($jsonBurt->fields->customfield_19230))
                $jsonBurt->CPEBurtId=$jsonBurt->fields->customfield_19230;
            else
                $jsonBurt->CPEBurtId="NULL";

            //! Call to oracle db if Burt Id is closed or not.
            if( $jsonBurt->CPEBurtId != "NULL")
                $yes = checkBurtClose::isBurtIdClosed($jsonBurt->CPEBurtId);

            if($yes)
                return "YES";
            else
                return "NO";
        }

    }

    public static function getJsonApiOfEachBurt($json)
    {
        return $json->self."?expand=changelog";
    }

    public static function getJiraCaseInfo($jsonObj)
    {

        echo "Jira Id           = ".$jsonObj->key."\n";
        echo "Jira Burt Id      = ".$jsonObj->fields->customfield_19230."\n";
        echo "Jira issuetype    = ".$jsonObj->fields->issuetype->name."\n";
        echo "Jira Resolutio    = ".$jsonObj->fields->JiraResolution->name."\n";
        echo "Jira Status       = ".$jsonObj->fields->status->name."\n";
    }



    public static function getBurtIdsOpenAndJiraClosed($jsonObj)
    {
        //if ( empty ($jsonObj->fields->customfield_19230))
        // echo "Jira Id whose Burt Id is empty = ".$jsonObj->key."\n";
        $post_data = new stdClass();
        if(!empty($jsonObj))
        { 
            //! Now we got to know that Burt is closed.
            if( self::getBurtClosedDetails($jsonObj) =="YES")
            {
                $post_data->jiraId=$jsonObj->key;

                //echo "Jira Id DONE = " . $jsonObj->key. " " . "Burt Id New/Open= " . $jsonObj->fields->customfield_19230."\n";
                //! Since NULL is checke earlier, Not required here.
                $post_data->CPEBurtId=$jsonObj->CPEBurtId;

                //! all are unresolved, hence Hardcoded.
                $post_data->JiraResolution="Unresolved";
                /*
                   if(!empty($jsonObj->fields->resolution))
                   $post_data->resolution= $jsonObj->fields->resolution->name;
                   else
                   $post_data->resolution="NULL";
                   if(!empty($jsonObj->fields->resolution))
                   $post_data->resolutionSummery= $jsonObj->fields->resolution->description;
                   else
                   $post_data->resolutionSummery="NULL";
                 */

                if(!empty($jsonObj->fields->assignee->key))
                    $post_data->assigneeKey=$jsonObj->fields->assignee->key;
                else
                    $post_data->assigneeKey="NULL";

                if(!empty($jsonObj->fields->assignee->emailAddress))
                    $post_data->assigneeEmail=$jsonObj->fields->assignee->emailAddress;
                else
                    $post_data->assigneeEmail="NULL";

                if(!empty($jsonObj->fields->customfield_15249))
                    $post_data->SupportCaseNumber=$jsonObj->fields->customfield_15249;
                else
                    $post_data->SupportCaseNumber="NULL";

                if(!empty($jsonObj->fields->status->name))
                    $post_data->JiraStatus=$jsonObj->fields->status->name;
                else
                    $post_data->JiraStatus="NULL";

                /*if(!empty($jsonObj->fields->reporter->key))
                  $post_data->ManagerKey=$jsonObj->fields->reporter->key;
                  else
                  $post_data->ManagerKey="NULL";
                  if(!empty($jsonObj->fields->reporter->emailAddress))
                  $post_data->ManagerEmail=$jsonObj->fields->reporter->emailAddress;
                  else
                  $post_data->ManagerEmail="NULL";
                 */
                //! ESCAL_TYPE
                if( !empty($jsonObj->fields->issuetype->name))
                    $post_data->issuetype=$jsonObj->fields->issuetype->name;
                else
                    $post_data->issuetype="NULL";
//                print_r($post_data);

                return $post_data;      
            }

            return null;
        }
        return null;
    }
}

?>
