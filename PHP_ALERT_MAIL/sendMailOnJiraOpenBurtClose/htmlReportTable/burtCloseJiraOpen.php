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
         $this->fileName="burtCloseJiraOpen.html";

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
         $this->fileName="./burtCloseJiraOpen.html";
         if( file_exists($this->fileName))
         {
            $retVal;
            $outPut;
            exec('rm ./burtCloseJiraOpen.html',$outPut,$retVal);
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

      $status="CLOSED";

      //$this->slaList->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO);

     // for ($this->slaList->rewind(); $this->slaList->valid(); $this->slaList->next())
      foreach ($this->slaList as $row) 
      {
      //   $row=$this->slaList->current();

         //$this->htmlOutput .= '<tr>'."\n";

         $this->htmlOutput .="<td align=center> {$row->jiraId}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->JiraStatus}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->JiraResolution}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row->CPEBurtId}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$status}</td> "."\n";


         $this->htmlOutput .= '</tr>'."\n";

      }


      ob_start();


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

      print '<p><b> SolidFire CPE BURT Details (Burt Closed, Jira Case is Unresolved)</b></p>'."\n";

      print '<body>'."\n";
      //DISPLAY TABLE

      print '<table>'."\n";

      print '<tr bgcolor="#99C4E7">'."\n";

      print '<td align=center>&nbsp;&nbsp;JIRA_ID&nbsp;&nbsp;</td>' ."\n";

      print '<td align=center>&nbsp;JIRA_STATUS&nbsp;</td>'."\n";

      print '<td align=center>&nbsp;JIRA_RESOLUTION&nbsp;</td>'."\n";
      
      print '<td align=center>&nbsp;BURT_ID&nbsp;</td>'."\n";
      
      print '<td align=center>&nbsp;BURT_STATUS&nbsp;</td>'."\n";

      print '</tr>'."\n";

      print $this->htmlOutput;

      print '</table>'."\n";

      print '</body>'."\n";

      print '</html>'."\n";

      print '<br><br>#####This is an automated daily report. Please ignore if no actions are pending from your end.####<br>';

      print '<br><br>';

      //print '<b><u>Click on below Confluence Link to know details of SolidFire SLA Miss Escalations Logic </u></b><br>';

      print '<b>SolidFire ALL script details available at </b><br>';

      print '<a href="https://confluence.ngage.netapp.com/display/CPE/Backend+Scripts+-+SolidFire+Escalations+JIRA">"https://confluence.ngage.netapp.com/display/CPE/Backend+Scripts+-+SolidFire+Escalations+JIRA</a>';

      print '<br>';

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


