<?php



class slaMail
{

   public $toMailId,$fromMailId,$ccMailId,$subject,$content,$slaMissDays;

};



class sendMail
{


   private $toMailId;

   private $CCMailId;

   private $subject="Burt Tool Daily Alert Report where escal_status directly RESOLVED without RCA_DONE state - As on ";

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
	  echo " Haramohan has sent mail\n";



   }

}// end of sendmail class


class Util 
{
    public static function getArraOfBurtsBelongsToSFEngineer($burtDLIST,$userName)
    {
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


        //! In ths inner lopp, iT tries to collect all the object belongs to one user,
        //! as loop pass through, the last element is left out, which invalidate the pointer,
        //! hence condition written to collect Object before it gets invalidated.
        $mgrBurtCount=array();
        $burtDLIST->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);
        for ($burtDLIST->rewind(); $burtDLIST->valid(); )
        {
            if($burtDLIST->current()->burtOwner == $userName)
            {
                $mgrBurtCount[]=$burtDLIST->current();
                $burtDLIST->offsetUnset($burtDLIST->key());
                if($burtDLIST->count()>1)
                    $burtDLIST->rewind();
            }
            else
            {
                $burtDLIST->next();
            }
        }
        $burtDLIST->rewind();
        return $mgrBurtCount;
    }
}


//shell_exec("./executeBurtCmd.sh");



//$htmlOutput = '<tr>'."\n";
$htmlOutput =  '';
$engineerBurtArray[]="";

unset($engineerBurtArray);

//$content = file_get_contents("report_all");
$content = file_get_contents("report_final");
//$s = preg_split("/\d+\)|\n/",$content);
$s = preg_split("/\n/",$content);
$sub=array_filter($s);

$burtDLIST = new SplDoublyLinkedList();

for ($cnt=0; $cnt< count( $sub);$cnt++)
{
	$burt_data_for_user = new stdClass();
	
	$line=$sub[$cnt];
	$parts = explode('|', $line);

	
	$arrToken = explode(' ', $parts[0]);
	
	for ($arrTokenIndex=0; $arrTokenIndex< count( $arrToken);$arrTokenIndex++)
	{
			if ( $arrTokenIndex >0 and $arrTokenIndex < 7 )
			{
				
				$w=trim($arrToken[$arrTokenIndex]);
				if ( $arrTokenIndex == 1 )
					$burt_data_for_user->burt_id=$w;
				else if ( $arrTokenIndex == 2 )
					$burt_data_for_user->burt_state=$w;
				else if ( $arrTokenIndex == 3 )
					$burt_data_for_user->burt_escal_status=$w;
				else if ( $arrTokenIndex == 4 )
					$burt_data_for_user->burtOwner=$w;
				else if ( $arrTokenIndex == 5 )
					$burt_data_for_user->burtManager=$w;
				else if ( $arrTokenIndex == 6 )
					$burt_data_for_user->burt_escal_id=$w;

			}
	}
	$w=trim($arrToken[0]);
	$burt_data_for_user->burt_escal_resol_dt=$w;
	
	$word=trim($parts[1]);
	$burt_data_for_user->burt_title=$word;
	  
	//print "Burt owner = ". $burtOwner.'@'."netapp.com";
	//print  "Burt Manager = ". $burtOwner.'@'."netapp.com";
	$burtDLIST->push($burt_data_for_user);
}

//! new loop starts , which iterates over the double link list which contains the all the objects of report_fine file.

   $burtDLIST->setIteratorMode(SplDoublyLinkedList::IT_MODE_KEEP);
   for ($burtDLIST->rewind(); $burtDLIST->valid(); )
   {

      //! Since there is one node left, just take data whatver it, I dont care, It must be a unique user
      if($burtDLIST->count() == 1)
      {
         $engineerBurtArray[]=$burtDLIST->current();
         $burtDLIST->offsetUnset($burtDLIST->key());
         if($burtDLIST->count()>1)
            $burtDLIST->rewind();
      }
      else{
         $engineerBurtArray=Util::getArraOfBurtsBelongsToSFEngineer($burtDLIST,$burtDLIST->current()->burtOwner);
      }

      if( $burtDLIST->count() > 1)
         $burtDLIST->next();

      //! One iteration done, so bring the pointer to begining. We have collected data for one user by now.
      $burtDLIST->rewind();  


      //!  mail parts begins by using the engineerBurtArray[] array, 
	  //!  which contains all the BurtId belongs to one ontap Engineer

      if($burtDLIST->count() != 1 )
         $burtDLIST->next();
	 

      $row=$engineerBurtArray;
	 // print_r($row);

	$id='@'."netapp.com";
	$owner_id='';
	$mgrid='';
	
	
	 //! get the mail content ready
     for($i=0;$i<count($row);$i++)
	 {
		 $owner_id=$row[$i]->burtOwner.$id;
		 $mgrid=$row[$i]->burtManager.$id;
		 $htmlOutput.="<td align=center> {$row[$i]->burt_id}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burt_state}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burt_escal_status}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burtOwner}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burtManager}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burt_escal_id}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burt_escal_resol_dt}</td> " . "<br>";
		 $htmlOutput.="<td align=center> {$row[$i]->burt_title}</td> " . "<br>";
		 $htmlOutput .= '</tr>'."\n";
	 }
 
 	//print "Burt owner = ". $owner_id."\n";
	//print  "Burt Manager = ". $mgrid."\n";

  
  
$owner=$row[0]->burtOwner;
$today=date("d-m-y H:i:s");
$cont="Hi $owner ,<br>";

         $cont.="<br>";

         $cont.="<br>";

         $cont.="Please find the list of CPE BURTs where escal_status directly RESOLVED without passing through RCA_DONE state, as of $today and please take necessary action if required.";

         $cont.="<br>";
         $cont.="<br>";
         $cont.="<br>";


		$text= '<!DOCTYPE html>'."\n";
		$text.= '<html>'."\n";
		$text.= '<head>'."\n";
		$text.= '<style>'."\n";
		$text.= 'table,th,td'."\n";
		$text.= '{'."\n";
		$text.= 'border:1px solid black ;'."\n";
		$text.= 'border-collapse:collapse;'."\n";
		$text.= '}'."\n";
		$text.= '</style>'."\n";
		$text.= '</head>'."\n";
	
		$text.= '<p><b> Alert Mail Report where escal_status directly RESOLVED without passing through RCA_DONE state:</b></p>'."\n";
		$text.= '<body>'."\n";
		//DISPLAY TABLE
		$text.= '<table>'."\n";
// id      state      s p impact    type           subtype                owner      gen  |title
		$text.= '<tr bgcolor="#99C4E7">'."\n";
		$text.= '<td align=center>&nbsp;&nbsp;BURT_ID&nbsp;&nbsp;</td>' ."\n";
		$text.= '<td align=center>&nbsp;BURT_STATE&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;BURT_ESCAL_STATUS&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;BURT_OWNER&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;BURT_MGR_OWNER&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;BURT_CASE_NUMBER&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;ESCAL_RESOLVED_DATE&nbsp;</td>'."\n";
		$text.= '<td align=center>&nbsp;BURT_TITLE&nbsp;</td>'."\n";
		$text.= '</tr>'."\n";
		$cont.=$text;
		$cont.=$htmlOutput;
		$cont.= '</table>'."\n";
		$cont.= '</body>'."\n";
		$cont.= '</html>'."\n";
		

         //$cont.="Regards,";
         $cont.="<br>";
		 $cont.="Automation Script";
		 $cont.="<br>";
		 $cont.="<br>";



		$slaMailObj= new slaMail();

		//$slaMailObj->toMailId="kkranthi@netapp.com";
		//$slaMailObj->toMailId="haramoha@netapp.com";
		$slaMailObj->toMailId=$owner_id;
        $slaMailObj->fromMailId="Auto_EPS_Alert@Daily_Report";

         $slaMailObj->ccMailId=$mgrid;
         $slaMailObj->subject=" Reminder Mail";

         $slaMailObj->content=$cont;
		 
         $mailObj= new sendMail($slaMailObj);

         $mailObj->sendMail();
		 echo "Mail Sent\n";
		 print "Burt owner = ". $owner_id."\n";
		 print  "Burt Manager = ". $mgrid."\n";
		 //! re set the htmloutput
		 $htmlOutput =  '';
		 unset($engineerBurtArray);

		 
}


?>
