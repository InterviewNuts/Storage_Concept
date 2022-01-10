#!/usr/bin/perl

#
#  Copyright (c) 2021 Network Appliance, Inc.
#  All rights reserved.



$ENV{ORACLE_HOME} = '/usr/software/test/oracle';
$ENV{NLS_LANG} = 'AMERICAN_AMERICA.AL32UTF8';

use DBI;
my $dbh = DBI->connect('dbi:Oracle:host=burtdw-open.rtp.openeng.netapp.com;port=1526;sid=burtopen', GtnB_18Hr51, netapp1 ); 
#my $dbh = DBI->connect('dbi:Oracle:host=burtdw-svl.eng.netapp.com;port=1626;sid=burtdw_p', epsmatrics, epsmatrics );
my $id = @ARGV[0];

my $sth='SELECT burt_id,state,JIRA_ID,owner,TITLE FROM burtdb.burt_main where burt_id=? AND state IN(\'OPEN\',\'NEW\')';
#my $sth='SELECT burt_id,state,JIRA_ID,owner,TITLE FROM burtdb.burt_main where burt_id=? AND state IN(\'NEW\')';

      my $sth = $dbh->prepare($sth);

      $sth->execute($id);

      while (my @row = $sth->fetchrow_array)
      {
      print "@row\n";
      }

      $dbh->disconnect;
