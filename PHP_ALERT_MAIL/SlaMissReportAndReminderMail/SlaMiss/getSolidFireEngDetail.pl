
   $ENV{ORACLE_HOME} = '/usr/software/test/oracle';
   $ENV{NLS_LANG} = 'AMERICAN_AMERICA.AL32UTF8';

   use DBI;

   my $dbh = DBI->connect('dbi:Oracle:host=burtdw-open.rtp.openeng.netapp.com;port=1526;sid=burtopen', GtnB_18Hr51, netapp1 );
   my $id = @ARGV[0];
    #my $sth='SELECT burt_id, state, owner,  JIRA_ID,burtdb.manager_of(owner) AS "mgr_owner"  FROM burtdb.burt_main where owner=? and state IN (\'NEW\',\'OPEN\')';
    #my $sth='SELECT  e.name AS "employee",  e.login AS "username",  m.name AS "manager" ,  e.email FROM burtdb.employee e JOIN burtdb.employee m  ON e.managerid = m.employeeid WHERE m.login = ? ORDER BY e.name';
    #my $sth='SELECT   b.burt_id,  b.owner,  b.state,b.JIRA_ID,  b.title FROM burtdb.burt_main b JOIN burtdb.employee e   ON b.owner = e.login  JOIN burtdb.employee m   ON e.managerid = m.employeeid  WHERE   b.state IN (\'NEW\',\'OPEN\') AND   m.login = ? ORDER BY b.owner';
    
   my $sth='  SELECT login, burtdb.manager_of(login) AS "manager" ,email,costcentername,name FROM burtdb.employee WHERE login = ?';

   my $sth = $dbh->prepare($sth);
   $sth->execute($id);

   while (my @row = $sth->fetchrow_array)
   {
      print "@row\n";
   }

$dbh->disconnect;
