<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */

define('BURT_DETAIL_PATH', __DIR__);
//include_once ($SCRIPT_PATH . '/getSolidFireEngDetail.pl');

$SCRIPT_PATH=BURT_DETAIL_PATH;



class BurtInfo
{
   function __construct( $burtId)
   {
      self::$burtId=$burtId;
   }

   public static function isBurtClosed($burtId)
   {
      $SCRIPT_PATH=BURT_DETAIL_PATH;
      //$yes = shell_exec("perl $SCRIPT_PATH/checkBurtClose.pl $burtId | grep \"CLOSED\" | head -1 |awk '{ print $2 }' ");
      $yes = shell_exec("perl $SCRIPT_PATH/checkBurtClose.pl $burtId | awk '{ print $2 }' ");
      $str="OPEN";
      $res=strnatcasecmp("$yes" ,"$str");
      $str="NEW";
      $res1=strnatcasecmp("$yes" ,"$str");
      //$res=strcmp("$yes" ,"$str");
      if( $res ==1 or $res1 == 1)
         return true;

      return false;
   }

       

 public static function notClosedBurtInfo($burtId)
   {

   //! 1399263 NEW CSD-4750 pkarurma Case# 2008755545 (PT Pertamina Persero Tbk): sfvasa service not running alerts on multiple storage nodes post 12.3 upgrade
       $burtInfo=new stdClass();

      $SCRIPT_PATH=BURT_DETAIL_PATH;
      $burt = shell_exec("perl $SCRIPT_PATH/checkBurtClose.pl $burtId ");

       $burtInfo->burt_id=  shell_exec("echo \"$burt\" | awk '{print $1 }' ");      
       $burtInfo->state=  shell_exec("echo \"$burt\" | awk '{print $2 }' ");      
       $burtInfo->JIRA_ID=  shell_exec("echo \"$burt\" | awk '{print $3 }' ");      
       $burtInfo->owner=  shell_exec("echo \"$burt\" | awk '{print $4 }' ");      
       $burtInfo->support_case_number=  shell_exec("echo \"$burt\" | awk '{print $6 }' ");      

      return $burtInfo;
   }


}






//echo BurtInfo::isBurtClosed(1391130)."\n";

//print_r(BurtInfo::notClosedBurtInfo(1391130))."\n";



?>
