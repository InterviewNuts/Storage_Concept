<?php

/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */


class createHTMLSlaMissReport
{

   private $fileName;

   //INITIALIZE VARIABLES
   private   $htmlOutput;

   private   $htmlMgrOutput;

   private   $colNum;

   private   $slaList;
   private   $log_file;

   //GET PICTURE LIST
   function __construct( $list)
   {
      
   $this->log_file = "./solidfire_closeburt.log";
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') 
         {
         $this->fileName="slaReportFromList.html";

         if( file_exists($this->fileName))
         {

            if (!unlink($this->fileName)) 
              { 
       //        echo "You need to have required permission to delete the File $this->fileName";
                error_log("You need to have required permission to delete the File $this->fileName\n", 3, $this->log_file);
            }			
         }
      }
      else
       {
         $this->fileName="./slaReportFromList.html";
         if( file_exists($this->fileName))
         {
            $retVal;
            $outPut;
            exec('rm ./slaReportFromList.html',$outPut,$retVal);
            if( $retVal != 0)
            {
            //   echo "You need to have required permission to delete the File $this->fileName";
               error_log("You need to have required permission to delete the File $this->fileName\n",3, $this->log_file);
            }
         }

      }

      $this->htmlOutput = '';

      $this->htmlMgrOutput = '';

      $this->colNum     = 1;

      $this->slaList=$list;

   //   print "Constructing " . __CLASS__ . "\n";
    //  error_log("Constructing " . __CLASS__, 3, $this->log_file);
   }

   function __destruct() 
   {
      //print "Destroying " . __CLASS__ . "\n";
    //  error_log("Destroying  " . __CLASS__, 3, $this->log_file);
   }

   function createSlaMissHTMLTable()
   {
      $mgrBurtCount[]="";

      $status="Unresolved";

      $this->slaList->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

      for ($this->slaList->rewind(); $this->slaList->valid(); $this->slaList->next())
      {
         $row=$this->slaList->current();

         //$this->htmlOutput .= '<tr>'."\n";

         $this->htmlOutput .="<td align=center> {$row->jiraId}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->Status}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->EscalationPriority}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->SupportCaseNumber}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$status}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->escal_to_eng}</td> "."\n";


         $this->htmlOutput .="<td align=center> {$row->ManagerKey}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->issuetype}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->days_since_last_modified}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->CustomerAccountName}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->toString}</td> "."\n";

         //			$this->htmlOutput .="<td align=center> {$this->yes} </td>"."\n";

         $this->htmlOutput .= '</tr>'."\n";

         $mgrBurtCount[]=$row->ManagerKey;
      }
     //asort($mgrBurtCount);

        
      $mgrAssoArray=array_count_values($mgrBurtCount);
    asort($mgrAssoArray);
      foreach($mgrAssoArray as $key => $value)
      {

         if(!empty($key)){
            // $this->htmlMgrOutput .= '<tr>'."\n";

            $this->htmlMgrOutput .="<td align=center> {$key}</td> "."\n";

            $this->htmlMgrOutput .="<td align=center> {$value}</td> "."\n";

            $this->htmlMgrOutput .= '</tr>'."\n";

         }
      }
      //	print_r(array_count_values($mgrBurtCount));

      ob_start();

      print '<p>P1-Critical : 1 day</p>'."\n";


      print '<p>P2-High     : 3 days</p>'."\n";

      print '<p>P3-Medium   : 5 days</p>'."\n";

      print '<p><b> Manager wise classification: </b></p>'."\n";

      print '<!DOCTYPE html>'."\n";

      print '<html>'."\n";

      print '<head>'."\n";

      print '<style>'."\n";

      print 'table,th,td'."\n";

      print '{'."\n";

      print 'border:1px solid black ;'."\n";

      print 'border-collapse:collapse;'."\n";

      print '}'."\n";

      print '</style>'."\n";

      print '</head>'."\n";

      print '<table>'."\n";

      print '<tr bgcolor="#99C4E7">'."\n";

      print '<td align=center>mgr_owner</td>'."\n";

      print '<td align=center>count</td>'."\n";

      print '</tr>'."\n";

      print $this->htmlMgrOutput;

      print '</table>'."\n";


      print '<p><b> SolidFire CPE Jira Escalation Case Details:</b></p>'."\n";

      print '<body>'."\n";
      //DISPLAY TABLE

      print '<table>'."\n";

      print '<tr bgcolor="#99C4E7">'."\n";

      print '<td align=center>&nbsp;&nbsp;JIRA_ID&nbsp;&nbsp;</td>' ."\n";

      print '<td align=center>&nbsp;JIRA_STATE&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;PRIORITY&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;CASE_NUMBER&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;ESCAL_STATUS&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;OWNER_OF_JIRA_ID&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;MGR_OWNER&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;ESCAL_TYPE&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;DAYS_SINCE_LAST_MODIFIED&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;CUSTOMER_NAME&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;LAST_UPDATED_COMMENT&nbsp;</td>'."\n";

      //		print '<td align=center>&nbsp;ESCAL_TO_ENG&nbsp;</td>'."\n";

      print '</tr>'."\n";

      print $this->htmlOutput;

      print '</table>'."\n";

      print '</body>'."\n";

      print '</html>'."\n";

      print '<br><br>#####This is an automated daily report. Please ignore if no actions are pending from your end.####<br>';

      print '<br><br>';

      //print '<b><u>Click on below Confluence Link to know details of SolidFire SLA Miss Escalations Logic </u></b><br>';

      print '<b>SolidFire SLA Miss Escalations script details available at </b><br>';

      print '<a href="https://confluence.ngage.netapp.com/display/CPE/Backend+Scripts+-+SolidFire+Escalations+JIRA">"https://confluence.ngage.netapp.com/display/CPE/Backend+Scripts+-+SolidFire+Escalations+JIRA</a>';

      print '<br>';

      //		print '<br>#####For SLA Logic, please refere the Page:https://confluence.ngage.netapp.com/display/CPE/Backend+Scripts+-+SolidFire+Escalations+JIRA.####<br>';

      //  Return the contents of the output buffer

      $htmlStr = ob_get_contents();

      // Clean (erase) the output buffer and turn off output buffering

      ob_end_clean();

      // Write final string to file

      file_put_contents($this->fileName, $htmlStr);
   }

}


//$htmlReportObj= new createHTMLSlaMissReport();
//$htmlReportObj->createSlaMissHTMLTable();
?>


