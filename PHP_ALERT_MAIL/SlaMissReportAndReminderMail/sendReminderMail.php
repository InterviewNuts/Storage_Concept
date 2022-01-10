<?php
/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */

include_once  './sendMail/sendMail.php';
include_once  './htmlReportTable/reminderMail.php';
include_once  './SlaMiss/SlaMiss.php';
include_once  './Util/Util.php';

//! password changed to complex one for security reason
$username = 'sf_api_sa';
$password = 'J6aXK4vSOOmBK0KCOhXK2tDuVze3eyBIHJ4RewaVKZkdC7Vc7c3riNdJ4wiH';

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
         $burtUrl=SlaMiss::getJsonApiOfEachBurt($value);

         //Execute the request.
         //Set the URL that you want to GET by using the CURLOPT_URL option.
         curl_setopt($ch, CURLOPT_URL, $burtUrl);

         $burtData = curl_exec($ch);

         $burtJsonObj = json_decode($burtData);

         $jsonBurt=SlaMiss::getLastUpdateOnEscalationNextSteps($burtJsonObj);

         if( !empty($jsonBurt))
         {
            $violated=SlaMiss::getSLAMissForBurt($jsonBurt);

            if($violated == "YES")
            {
               //! Push to The List    
               $slaDLIST->push($jsonBurt);
            }
         }
      }
   }

   echo " Total Violation Count = ". $slaDLIST->count()."\n";

   //! get the tolal number of sla miss bugs
   $totalSlaBurt=$slaDLIST->count();

   $engineerBurtArray[]="";

   unset($engineerBurtArray);


   //! Parent loop
   //! This loop will loop through the entire  double link list, while travesing , it is traversed & will keep the itm in the list itself.
   //! That is is the reason, I have kept as "IT_MODE_KEEP",  Node deletion will take place inside the loop itself, based on match logic.
   //! SplDoublyLinkedList::IT_MODE_LIFO (Stack style)
   //! SplDoublyLinkedList::IT_MODE_FIFO (Queue style) The behavior of the iterator (either one or the other)
   //! SplDoublyLinkedList::IT_MODE_DELETE (Elements are deleted by the iterator)
   //! SplDoublyLinkedList::IT_MODE_KEEP (Elements are traversed by the iterator)
   //! A doubly-linked list allows you to efficiently bypass and add large data sets without re-hashing.
   //! SplDoublyLinkedList  in php (1. SplStack, 2.SplQueue)
   //! http://web.archive.org/web/20130805120049/http://blueparabola.com/blog/spl-deserves-some-reiteration

   $slaDLIST->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP);
   for ($slaDLIST->rewind(); $slaDLIST->valid(); )
   {

      //! Since there is one node left, just take data whatver it, I dont care, It must be a unique user
      if($slaDLIST->count() == 1)
      {
         $engineerBurtArray[]=$slaDLIST->current();
         $slaDLIST->offsetUnset($slaDLIST->key());
         if($slaDLIST->count()>1)
            $slaDLIST->rewind();
      }
      else{
         $engineerBurtArray=Util::getArraOfBurtsBelongsToSFEngineer($slaDLIST,$slaDLIST->current()->assigneeKey);
      }

      if( $slaDLIST->count() > 1)
         $slaDLIST->next();

      //! One iteration done, so bring the pointer to begining. We have collected data for one user by now.
      $slaDLIST->rewind();  


      //!  mail parts begins by using the engineerBurtArray[] array, which contains all the Jira Id belongs to one SolidFire Engineed

      if($slaDLIST->count() != 1 )
         $slaDLIST->next();

      $curDir=getcwd();

      chdir('htmlReportTable');

      $htmlReportObj= new createHTMLSlaMissReport($engineerBurtArray);

      $htmlReportObj->createSlaMissHTMLTable();

      chdir($curDir);

      $now = date_create('now')->format('H');

      if($now >= 0 )
      {
         //! take the file name created with name of Individual user name.html
         $fn=$engineerBurtArray[0]->solidFireEngAuthorKey.".html";

         $pathFile='./htmlReportTable/HTML/'.$fn;

         $text = file_get_contents($pathFile);

         if(!empty($text))
         {
            $toName=  $engineerBurtArray[0]->solidFireEngAuthorDisplayName;

            $slaMailObj= new slaMail();

            $cont="Hi $toName,<br>";

            $cont.="<br>";

            $cont.="<br>";

            $cont.="Please find the list of CPE JIRA Cases whose Resolution is Unresolved for which ESCALATION_STATUS ";

            $cont.="section has not been updated with ‘customer consumable update’ as per SLA. This is an absolute";

            $cont.=" CPE minimum expected deliverable as part of ownership and accountability.";
            $cont.="<br>";
            $cont.="<br>";
            $cont.="<br>";
            $cont.= "<b>This is just a friendly REMINDER notifying the escalation owner to update the escalation status of this CPE JIRA Cases to ";
            $cont.="<b>avoid missing agreed SLA.";
            $cont.="<br>";
            $cont.="<br>";

            $cont.=$text;

            //$slaMailObj->toMailId="anjaiah@netapp.com";
            //   $slaMailObj->toMailId="sukanyar@netapp.com";
            //$slaMailObj->toMailId="kkranthi@netapp.com";
            $slaMailObj->toMailId="haramoha@netapp.com";

            $slaMailObj->fromMailId="Auto_EPS_Alert@Daily_Report";

            //  $slaMailObj->ccMailId.="haramoha@netapp.com";

            //$slaMailObj->ccMailId.=", kkranthi@netapp.com";
            // $slaMailObj->ccMailId.=", anjaiah@netapp.com";
            // $slaMailObj->ccMailId.=", mkomarth@netapp.com";
            //  echo " Al mail id = ".$slaMailObj->ccMailId  ."\n";
            // $slaMailObj->ccMailId="haramoha@netapp.com";


            $slaMailObj->subject=" Reminder Mail";

            $slaMailObj->content=$cont;


            $mailObj= new sendMail($slaMailObj,"reminder");

            $mailObj->sendMail();

         }
      }// end of evening or morning if

      unset($engineerBurtArray);

   }


}
else
{
   echo " resource does not Exists, either Jira Link broken or Check the service accoint user name/password\n";
}

//Close the cURL handle.
curl_close($ch);
?>

