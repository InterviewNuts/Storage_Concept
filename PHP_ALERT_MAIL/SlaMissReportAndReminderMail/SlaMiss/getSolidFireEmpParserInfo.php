<?php
class getSolidFireEmpInfo
{

   private static  $url = "http://onestop.netapp.com/dir/detail.php?username=";

   private static $html;

   private static $empArray=array();

   private static $mgrInfo;//=new stdClass();

   function __construct( $userName)
   {

      $endPoint=self::$url."$userName";

      self::$html = file_get_contents($endPoint);

      self::$mgrInfo=new stdClass();
   }
   public static function textFromHtml(TidyNode $node) {

      if ($node->isText()) 
      {

         if( $node->value != "vCard")
            return $node->value.":";
      } else if ($node->hasChildren()) {

         $childText = '';

         foreach ($node->child as $child)
         {
            $childText .= getSolidFireEmpInfo::textFromHtml($child);
         }

         return $childText;

      }

      return '';
   }

   public static function isSolidFireEngineer($userName)
   {

      $endPoint="http://onestop.netapp.com/dir/detail.php?username="."$userName";

      self::$html = file_get_contents($endPoint);

      self::$mgrInfo=new stdClass();

      $tidy = new tidy();

      $tidy->parseString(self::$html);

      $text = getSolidFireEmpInfo::textFromHtml($tidy->body());

      $token = strtok($text, ":");

      while ($token !== false)
      {

         if( strlen($token)> 2)
            self::$empArray[]=$token;

         $token = strtok(":");
      }

      $yes=false;

      if (is_array(self::$empArray))
      {
         foreach(self::$empArray as $key=>$value)
         {

            if ($value == "Cost Center")
               if( stristr(self::$empArray[$key+1],"CSE SolidFire CPE"))
               {
                  $yes=true;
               }
            //      if( $value == "Mgr Chain" and $yes){
            //       self::$mgrInfo->Manager=self::$empArray[$key+1];
            //    }
         }
      }

      self::$html=null;

      self::$mgrInfo=null;

      self::$empArray=null;

      $tidy=null;

      $token=null;

      return $yes;
   }

   public static function getSolidFireEngineerInfo($userName)
   {

      $endPoint="http://onestop.netapp.com/dir/detail.php?username="."$userName";

      self::$html = file_get_contents($endPoint);

      self::$mgrInfo=new stdClass();

      $tidy = new tidy();

      $tidy->parseString(self::$html);

      $text = getSolidFireEmpInfo::textFromHtml($tidy->body());

      $token = strtok($text, ":");

      while ($token !== false)
      {
         if( strlen($token)> 2)
            self::$empArray[]=$token;
         $token = strtok(":");
      }

      $yes=false;

      foreach(self::$empArray as $key=>$value)
      {

         if ($value == "Cost Center")
            if( stristr(self::$empArray[$key+1],"CSE SolidFire CPE"))
            {
               $yes=true;

               self::$mgrInfo->solidFireEngAuthorDisplayName=self::$empArray[1];

               self::$mgrInfo->solidFireEngAuthorName=self::$empArray[$key-2];

               self::$mgrInfo->solidFireEngAuthorKey=self::$empArray[$key-2];

               self::$mgrInfo->solidFireEngEmail=self::$empArray[$key+5];

            }

         if( $value == "Mgr Chain" and $yes){

            self::$mgrInfo->ManagerKey=self::$empArray[$key+1];
         }
      }

      $engInfo=self::$mgrInfo;

      self::$html=null;

      self::$mgrInfo=null;

      self::$empArray=null;

      $tidy=null;

      //      echo "=============parse enginfor============\n";
      //    print_r($engInfo);
      //    echo "============= end parse enginfor============\n";

      return $engInfo;
   }



   public static function getSolidFireManagerName($engName)
   {

      $engInfo=getSolidFireEmpInfo::getSolidFireEngineerInfo($engName); 

      return $engInfo;

   }

   public static function getSolidFireManagerNameOnly($userName)
   {

      $endPoint="http://onestop.netapp.com/dir/detail.php?username="."$userName";

      self::$html = file_get_contents($endPoint);

      self::$mgrInfo=new stdClass();

      $tidy = new tidy();

      $tidy->parseString(self::$html);

      $text = getSolidFireEmpInfo::textFromHtml($tidy->body());


      $token = strtok($text, ":");

      while ($token !== false)
      {

         if( strlen($token)> 2)
            self::$empArray[]=$token;

         $token = strtok(":");
      }

      foreach(self::$empArray as $key=>$value)
      {

         if( $value == "Mgr Chain")
         {
            self::$mgrInfo->Manager=self::$empArray[$key+1];
         }

      }

      if(!empty(self::$mgrInfo->Manager))
         return self::$mgrInfo->Manager;
      else
         return null;

   }
   public static function getFromHtml(TidyNode $node) 
   {

      // check if the current node is of requested type
      if($node->isHtml()) {
         return $node->value.":=:";

      }

      // check if the current node has childrens
      if($node->hasChildren()) {
         foreach($node->child as $child) {
            $childText .=getSolidFireEmpInfo::getFromHtml($child);
         }
         return $childText;
      }

   }



   public static function getMgrKey($userName)
   {

      $endPoint="http://onestop.netapp.com/dir/detail.php?username="."$userName";;

      $html = file_get_contents($endPoint);

      $tidy = new tidy();

      $tidy->parseString($html);

      $text=getSolidFireEmpInfo::getFromHtml($tidy->body());

      $token = strtok($text, ":=:");

      while ($token !== false)
      {
         //   echo "$token"."\n";
         if( strlen($token)> 2)
            self::$empArray[]=$token;
         $token = strtok(":=:");
      }

      foreach(self::$empArray as $key=>$value)
      {

         if( stristr($value,"Mgr Chain"))
         {
            //echo self::$empArray[$key+4]."\n";
            self::$mgrInfo->ManagerKey= strtok(self::$empArray[$key+4], "'");
         }
      }

      if(!empty(self::$mgrInfo->ManagerKey))
         return self::$mgrInfo->ManagerKey;
      else
         return null;
   }

}// end of class

/*
$obj= new getSolidFireEmpInfo("dipalib");
if(getSolidFireEmpInfo::isSolidFireEngineer("pnambees"))
echo " pnambees is not  solid fire engineer\n";
if($obj->isSolidFireEngineer("dipalib"))
print_r(getSolidFireEmpInfo::getSolidFireManagerName("dipalib"));
echo getSolidFireEmpInfo::getMgrKey("mjoey")."\n";
//echo getSolidFireEmpInfo::getMgrKey("mjoey")."\n";

//dipalib

if(getSolidFireEmpInfo::isSolidFireEngineer("dipalib"))
echo getSolidFireEmpInfo::getMgrKey("dipalib")."\n";
if(getSolidFireEmpInfo::isSolidFireEngineer("abdullah"))
echo getSolidFireEmpInfo::getMgrKey("abdullah")."\n";
//echo getSolidFireEmpInfo::getMgrKey("sburke")."\n";

if(  getSolidFireEmpInfo::isSolidFireEngineer("sburke"))
echo " Hara mhaon\n";

*/
?>
