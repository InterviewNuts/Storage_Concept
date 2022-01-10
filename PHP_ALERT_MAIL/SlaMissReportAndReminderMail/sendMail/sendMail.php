<?php 

/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */

class slaMail
{

   public $toMailId,$fromMailId,$ccMailId,$subject,$content,$slaMissDays;

};



class sendMail
{


   private $toMailId;

   private $CCMailId;

   private $subject="CPE ESCALATION_STATUS SLA Target Missed Report - As on ";//24 Mar 2021 05:02:55  

   private $reminderSubject="CPE ESCALATION_STATUS SLA Target Missed Reminder Mail - As on ";//24 Mar 2021 05:02:55  

   private $txt="";

   private $fromMailId;

   private $currDateTime;

   private $days_since_last_modified;



   public function __construct(slaMail $slaObj, $sub="report")
   {

      $this->currDateTime=date("d-m-y H:i:s");

      $this->toMailId=$slaObj->toMailId;

      $this->fromMailId=$slaObj->fromMailId;

      if( $sub == "report")
      {
         $this->subject.=$this->currDateTime;
      }
      else
      {
         $this->subject=$this->reminderSubject;

         $this->subject.=$this->currDateTime;
      }

      $this->txt.= "<br>";

      $this->txt.= "<br>";

      $this->txt.=$slaObj->content;

      $this->CCMailId=$slaObj->ccMailId;

      $this->days_since_last_modified=$slaObj->slaMissDays;


   }



   public function sendMail()
   {


      $this->txt.="Date : ";

      $this->txt.= $this->currDateTime;

      // Always set content-type when sending HTML email

      $headers = "MIME-Version: 1.0" . "\r\n";

      $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";



      $headers .= "From: ".$this->fromMailId . "\r\n" ;

      $headers.= "CC: ". $this->CCMailId;

      mail( $this->toMailId,$this->subject,$this->txt,$headers);




   }

}// end of sendmail class



?>


