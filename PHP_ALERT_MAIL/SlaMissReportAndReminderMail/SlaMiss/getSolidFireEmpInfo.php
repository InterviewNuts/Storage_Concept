<?php
define('SOLIDFIRE_ENG_DETAIL_PATH', __DIR__);
//include_once ($SCRIPT_PATH . '/getSolidFireEngDetail.pl');

$SCRIPT_PATH=SOLIDFIRE_ENG_DETAIL_PATH;




class getSolidFireEmpInfo
{
   function __construct( $userName)
   {
   }

   public static function isSolidFireEngineer($userName)
   {
      $SCRIPT_PATH=SOLIDFIRE_ENG_DETAIL_PATH;
      //$yes = shell_exec("perl $SCRIPT_PATH/getSolidFireEngDetail.pl $userName | grep \"SolidFire\" | head -1 |awk '{first = $1; $1 = \"\"; print $6 }' ");
      $yes = shell_exec("perl $SCRIPT_PATH/getSolidFireEngDetail.pl $userName | grep \"CSE SolidFire CPE\" ");
      if( $yes )
         return true;

      return false;
   }

   public static function getSolidFireEngineerInfo($userName)
   {
      $SCRIPT_PATH=SOLIDFIRE_ENG_DETAIL_PATH;
      //! mjoey adavies Joey.Mikes@netapp.com CSE CPE Joey Mikes
      //!
      $mgrInfo=new stdClass();
      $info= shell_exec("perl $SCRIPT_PATH/getSolidFireEngDetail.pl $userName");


      if(preg_match("/\b$userName\b/i", $info))
         $mgrInfo->solidFireEngAuthorKey=$mgrInfo->solidFireEngAuthorName=$userName;

      if( self::isSolidFireEngineer($userName))
         $DisplayName = shell_exec("echo \"$info\" | awk '{ print $7 \" \" $8 }' ");
      else
         $DisplayName = shell_exec("echo \"$info\" | awk '{ print $6 \" \" $7 }' ");
      $mgrInfo->solidFireEngAuthorDisplayName=$DisplayName;
      $mgrKey=shell_exec("echo \"$info\" | awk '{ print $2}' ");

      $mgrInfo->ManagerKey=$mgrKey; 
      $mgrInfo->solidFireEngEmail=shell_exec("echo \"$info\" | awk '{ print $3}' ");

      //     print_r($mgrInfo);
      return $mgrInfo;
   }



   public static function getMgrKey($userName)
   {
      $SCRIPT_PATH=SOLIDFIRE_ENG_DETAIL_PATH;
      $info= shell_exec("perl $SCRIPT_PATH/getSolidFireEngDetail.pl $userName");
      $mgrKey=shell_exec("echo \"$info\" | awk '{ print $2}' ");
      return $mgrKey;
   }

}// end of class

//$obj= new getSolidFireEmpInfo("dipalib");
//if(getSolidFireEmpInfo::isSolidFireEngineer("mjoey"))
//if($obj->isSolidFireEngineer("dipalib"))
//print_r(getSolidFireEmpInfo::getSolidFireManagerName("dipalib"));
//echo getSolidFireEmpInfo::getMgrKey("mjoey")."\n";
//echo getSolidFireEmpInfo::getMgrKey("mjoey")."\n";
//dipalib

//if(getSolidFireEmpInfo::isSolidFireEngineer("dipalib"))
//echo getSolidFireEmpInfo::getMgrKey("dipalib")."\n";
//if(getSolidFireEmpInfo::isSolidFireEngineer("abdullah"))
//echo getSolidFireEmpInfo::getMgrKey("abdullah")."\n";
//echo getSolidFireEmpInfo::getMgrKey("sburke")."\n";



/*
if(  getSolidFireEmpInfo::isSolidFireEngineer("abinesh"))
echo " abinesh is solid fire eng\n";
if(  getSolidFireEmpInfo::isSolidFireEngineer("pnambees"))
echo " pnambees is not solid fire eng\n";
if(  getSolidFireEmpInfo::isSolidFireEngineer("sburke"))
echo " sburke is solid fire eng\n";
if(  getSolidFireEmpInfo::isSolidFireEngineer("dipalib"))
echo " Depalib is solid fire eng\n";

$t=getSolidFireEmpInfo::getSolidFireEngineerInfo("abinesh");
print_r($t);
$t=getSolidFireEmpInfo::getSolidFireEngineerInfo("mjoey");
print_r($t);
   echo getSolidFireEmpInfo::getMgrKey("abinesh");
   echo getSolidFireEmpInfo::getMgrKey("sburke");
   echo getSolidFireEmpInfo::getMgrKey("dipalib");
   echo getSolidFireEmpInfo::getMgrKey("abdullah");
   echo getSolidFireEmpInfo::getMgrKey("mjoey");
*/
?>

