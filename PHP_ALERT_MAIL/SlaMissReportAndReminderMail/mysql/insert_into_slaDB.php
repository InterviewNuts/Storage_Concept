<?php

/*
 *  Copyright (c) 2021 Network Appliance, Inc.
 * All rights reserved.
 */
//!  This class is used to hold data for metric table
class MetricScheema
{

   public $Date_of_report,$manager,$No_of_misses_mgr,$No_of_active_escalations_mgr,$total_active_escalations,$percent_compliance;

}


class BurtScheema
{

   public $BURT_ID, $BURT_STATE,$JIRA_ID,$JIRA_STATE,$JIRA_OWNER,$support_case_number;

}

//!  This class is used to hold data for report table
class dbScheema
{

   public $j_id, $j_state,$j_reso,$j_case_num,$j_prio,$j_owner,$j_mgr_owner,$j_type,$j_days_last_modi,$j_c_name,$j_DOR;

}



class MySqlDbOp 
{


   private static $servername = "localhost";

   private static $username   = "root";

   private static $password   = "netapp";

   private static $dbname     = "sf_sla_testDB";

   private static $tblName    = "sf_sla_test_table";

   private  $conn;





   //! If user wants to use diferent table, then use this constructor.
   function __construct($tableName="sf_sla_test_table")
   {
      self::$tblName=$tableName;

      $this->conn = new mysqli(self::$servername, self::$username, self::$password, self::$dbname);

      // Check connection

      if ($this->conn->connect_error) 
      {
         die("Connection failed: " . $this->conn->connect_error);
      }

      //    print "Constructing " . __CLASS__ . "\n";

   }

   function __destruct() 
   {

      //      print "Destroying " . __CLASS__ . "\n";

      $this->conn->close();

   }

   public  function dbBurtNotClosedDataInsert( $dbObj)
   {
      /*
         mysql> desc sf_sla_burt_tobeclosed_table;
         +---------------------+-------------+------+-----+---------+-------+
         | Field               | Type        | Null | Key | Default | Extra |
         +---------------------+-------------+------+-----+---------+-------+
         | BURT_ID             | int(20)     | NO   |     | NULL    |       |
         | BURT_STATE          | varchar(20) | NO   |     | NULL    |       |
         | JIRA_ID             | varchar(20) | NO   |     | NULL    |       |
         | JIRA_STATUS         | varchar(20) | NO   |     | NULL    |       |
         | JIRA_RESOLUTION     | varchar(30) | NO   |     | NULL    |       |
         | JIRA_OWNER          | varchar(50) | NO   |     | NULL    |       |
         | support_case_number | int(20)     | NO   |     | NULL    |       |
         | Date_of_report      | date        | NO   |     | NULL    |       |
         +---------------------+-------------+------+-----+---------+-------+

       */
      $table=self::$tblName;
      //print_r($dbObj);
      $dbObj->BURT_ID = $dbObj->BURT_ID;
      $dbObj->BURT_STATE = trim($dbObj->BURT_STATE," ");
      $dbObj->JIRA_ID = trim($dbObj->JIRA_ID," ");
      $dbObj->JIRA_STATUS = trim($dbObj->JIRA_STATUS," ");
      $dbObj->JIRA_RESOLUTION = trim($dbObj->JIRA_RESOLUTION," ");
      $dbObj->JIRA_OWNER = trim($dbObj->JIRA_OWNER," ");
      $dbObj->support_case_number = $dbObj->support_case_number;


      $sql="INSERT INTO $table (BURT_ID,BURT_STATE,JIRA_ID,JIRA_STATUS,JIRA_RESOLUTION,JIRA_OWNER,support_case_number,Date_of_report) 
         VALUES ($dbObj->BURT_ID,'$dbObj->BURT_STATE','$dbObj->JIRA_ID','$dbObj->JIRA_STATUS','$dbObj->JIRA_RESOLUTION','$dbObj->JIRA_OWNER',$dbObj->support_case_number,CURDATE())";

      if ($this->conn->query($sql) === TRUE) 
      {
         //         echo "New record Inserted into $table successfully\n";
      } else 
      {
         echo "MySql Insert Record Error: " . $sql . $this->conn->error . "\n";
      }

   }

   public  function dbMetricsDataInsert(MetricScheema $dbObj)
   {
      $table=self::$tblName;
      //print_r($dbObj);


      $sql="INSERT INTO $table (Date_of_report,manager,No_of_misses_mgr,No_of_active_escalations_mgr,total_active_escalations,percent_compliance) VALUES (CURDATE(),'$dbObj->manager',$dbObj->No_of_misses_mgr,$dbObj->No_of_active_escalations_mgr,$dbObj->total_active_escalations,$dbObj->percent_compliance)";


      if ($this->conn->query($sql) === TRUE) {

         //         echo "New record Inserted into $table successfully\n";

      } else {

         echo "MySql Insert Record Error: " . $sql . $this->conn->error . "\n";

      }

   }

   public  function populateDbMetricObject(MetricScheema &$dbObj ,$slaJsonData)
   {


      //! DB does not take special char, hence replaced escape char added

      //      $p=strpos($slaJsonData->manager,"'");

      //    $str=substr_replace($slaJsonData->manager, "\\", $p,0);
      // $str= trim($slaJsonData->manager,"\\");

      //$dbObj->Date_of_report='CURDATE()';
      //$dbObj->manager=$str;
      $dbObj->manager=$slaJsonData->manager;

      $dbObj->No_of_misses_mgr=$slaJsonData->No_of_misses_mgr;

      $dbObj->No_of_active_escalations_mgr=$slaJsonData->No_of_active_escalations_mgr;

      $dbObj->total_active_escalations=$slaJsonData->total_active_escalations;

      $dbObj->percent_compliance=$slaJsonData->percent_compliance;
      //         print_r($dbObj);

   }


   public function getMysqlConObj()
   {

      return $this->conn;

   }



   public function alterColumnDataType( $colunName, $toDataType, $sz)

   {


      $table=self::$tblName;



      $sql="alter table $table modify column $colunName $toDataType($sz)";

      if ($this->conn->query($sql) === TRUE) 
      {

         echo "Table $table's COLUMNNAME=$colunName Datatype changed to $toDataType($sz) altered successfully\n";

      } 
      else 
      {

         echo "Error: " . $sql . $this->conn->error . "\n";

      }

   }



   public  function populateDbObject(dbScheema &$dbObj ,$slaJsonData)
   {

      $dateString =$slaJsonData->solidFireEngLastUpdateTime;

      $dateTimeObj = date_create($dateString);

      $date = date_format($dateTimeObj, 'y-m-d');


      //! DB does not take special char, hence replaced escape char added

      $p=strpos($slaJsonData->CustomerAccountName,"'");

      $str=substr_replace($slaJsonData->CustomerAccountName, "\\", $p,0);

      $dbObj->j_id="$slaJsonData->jiraId";

      $dbObj->j_state="$slaJsonData->Status";

      $dbObj->j_reso="Unreolved";

      $dbObj->j_case_num=$slaJsonData->SupportCaseNumber;

      $dbObj->j_prio="$slaJsonData->EscalationPriority";

      $dbObj->j_owner="$slaJsonData->escal_to_eng";

      $dbObj->j_mgr_owner="$slaJsonData->ManagerKey";

      $dbObj->j_type="$slaJsonData->issuetype";

      $dbObj->j_days_last_modi=$slaJsonData->days_since_last_modified;

      $dbObj->j_c_name="$str";

      $dbObj->j_DOR="$date";
   }



   public  function dbSimpleInsert(dbScheema $dbObj)
   {

      $table=self::$tblName;

      $sql="INSERT INTO $table (sf_jira_id, sf_jira_state, sf_jira_resolution,Case_number,escal_priority,owner,mgr_owner,type,days_since_last_modified,

         customer_name,Date_of_report) VALUES ('$dbObj->j_id','$dbObj->j_state','$dbObj->j_reso','$dbObj->j_case_num','$dbObj->j_prio','$dbObj->j_owner','$dbObj->j_mgr_owner', '$dbObj->j_type',$dbObj->j_days_last_modi, '$dbObj->j_c_name',CURDATE())";



      if ($this->conn->query($sql) === TRUE) {

         //     echo "New record inserted into  $table successfully\n";

      } else {

         echo "MySql Insert Record Error: " . $sql . $this->conn->error . "\n";

      }


   }




   public  function dbInsertPreaparedStmt(dbScheema $dbObj)

   {


      $table=self::$tblName;



      // prepare and bind

      $stmt = $this->conn->prepare("INSERT INTO $table (sf_jira_id, sf_jira_state, sf_jira_resolution,Case_number,escal_priority,

         owner,mgr_owner,type,days_since_last_modified,customer_name,Date_of_report) VALUES (?,?,?,?,?,?,?,?,?,?,?)");

      if($stmt)
      {

         $stmt->bind_param("ississssiss",$dbObj->j_id,$dbObj->j_state,$dbObj->j_reso, 

               $dbObj->j_case_num,$dbObj->j_prio,$dbObj->j_owner,$dbObj->j_mgr_owner,$dbObj->j_type,$dbObj->j_days_last_modi,

               $dbObj->j_c_name,$dbObj->j_DOR);



         if($stmt->execute())
         {

            echo "No of records inserted : ".$stmt->affected_rows;

         }// end of exec sql query

         else

         {

            echo " SQL bind_param() & execute() failed ";

         }

      }// end of prepare statement

      else

      {

         echo " sql Preapare Statement is failled ";

      }



      $stmt->close();

   }



}// end of class 

//! for unit test puposes , please dont delete it, As PHPUNIT is not installed here, I have tested this way.
//MySqlDbOp::alterColumnDataType('sf_jira_id','varchar',10);

//$obj = new MySqlDbOp('sf_sla_burt_tobeclosed_table');

//$dbObj= new dbScheema();

//$obj->alterColumnDataType('Case_number','varchar',50);


//$dbObj= new BurtScheema();
// $dbObj->burt_id = 123456;

//$obj->dbBurtNotClosedDataInsert($dbObj);



?>


