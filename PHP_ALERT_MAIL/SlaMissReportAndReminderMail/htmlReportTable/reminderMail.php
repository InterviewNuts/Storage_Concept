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

   //GET PICTURE LIST
   function __construct( $list)
   {

      $this->slaList=$list;

      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
      {

         $fn= $this->slaList[0]->solidFireEngAuthorKey.".html";

         $this->fileName='/HTML/'.$fn;

         if( file_exists($this->fileName))
         {

            $retVal;

            $outPut;

            exec("rm *.html",$outPut,$retVal);

            if( $retVal != 0){
               echo "You need to have required permission to delete the File $this->fileName\n";
            }
         }
      }
      else
      {

         $fn= $this->slaList[0]->solidFireEngAuthorKey.".html";

         $this->fileName='./HTML/'.$fn;

         //       echo "HTML File Name = ".$this->fileName ."\n";

         if( file_exists($this->fileName))
         {

            $retVal;

            $outPut;

            exec("rm ./HTML/*.html",$outPut,$retVal);

            if( $retVal != 0){
               echo "You need to have required permission to delete the File $this->fileName \n";
            }
         }

      }

      $this->htmlOutput = '';

      $this->htmlMgrOutput = '';

      $this->colNum     = 1;

//      print "Constructing " . __CLASS__ . "\n";
   }

   function __destruct() 
   {
  //    print "Destroying " . __CLASS__ . "\n";
   }

   function createSlaMissHTMLTable()
   {
      $row=$this->slaList;

      for($i=0;$i<count($this->slaList);$i++)
      {
         $status="Unresolved";


         $this->htmlOutput .="<td align=center> {$row[$i]->jiraId}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->Status}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->EscalationPriority}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->SupportCaseNumber}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$status}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->escal_to_eng}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->ManagerKey}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->issuetype}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->days_since_last_modified}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->CustomerAccountName}</td> "."\n";

         $this->htmlOutput .="<td align=center> {$row[$i]->toString}</td> "."\n";

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


      print '<p><b> SolidFire CPE Jira Escalation Case Reminder Details:</b></p>'."\n";

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

      print '</tr>'."\n";

      print $this->htmlOutput;

      print '</table>'."\n";

      print '</body>'."\n";

      print '</html>'."\n";

      print '<br><br>#####This is an automated daily report. Please ignore if no actions are pending from your end.####<br>';

      print '<br><br>';

      print '<b>SolidFire SLA Miss Escalations script details available at </b><br>';

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
