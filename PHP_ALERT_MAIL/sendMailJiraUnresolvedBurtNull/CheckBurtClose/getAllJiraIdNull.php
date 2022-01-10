<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 *  All rights reserved.
 */

date_default_timezone_set("Asia/Kolkata");

class checkBurtClose 
{


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



    public static function getBurtIdsOpenAndJiraNull($jsonObj)
    {
        $post_data = new stdClass();
        if(!empty($jsonObj))
        { 
            $post_data->jiraId=$jsonObj->key;

            //! Check burt is null or not
            if(empty($jsonBurt->fields->customfield_19230))
                $post_data->CPEBurtId="NULL";

            //! all are unresolved, hence Hardcoded.
            $post_data->JiraResolution="Unresolved";

            if(!empty($jsonObj->fields->status->name))
                $post_data->JiraStatus=$jsonObj->fields->status->name;
            else
                $post_data->JiraStatus="NULL";

            //! ESCAL_TYPE
            if( !empty($jsonObj->fields->issuetype->name))
                $post_data->issuetype=$jsonObj->fields->issuetype->name;
            else
                $post_data->issuetype="NULL";

            //print_r($post_data);

            return $post_data;      
        }

        return null;
    }
}

?>
