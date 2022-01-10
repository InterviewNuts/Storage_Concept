#
# Copyright (c) 2021 Network Appliance, Inc.
# All rights reserved.
# 
import time;
import subprocess;
import sys;
#if sys.version_info <(2, 7):
#if sys.version_info[0] == 2 and sys.version_info[1] == 4 :
#	import urllib2;
#elif sys.version_info[0] == 2 and sys.version_info[1] == 7 :
#	import urllib3
#import os
#import getpass
import logging;
import platform;



if sys.version_info[0] == 2 and sys.version_info[1] == 4 :
	#print (sys.version_info);
	print(platform.python_version());
	#print(platform.python_version_tuple());
	VERSION=24;
elif sys.version_info[0] == 2 and sys.version_info[1] == 7 :
	VERSION=27;
	#print (sys.version_info);
	print(platform.python_version());
	#print(platform.python_version_tuple());
	

#now we will Create and configure logger 
#logging.basicConfig(filename="burtClosing.log",format='%(asctime)s %(message)s', filemode='w');

logging.basicConfig(filename="burtClosing.log",level=logging.DEBUG,filemode='w')

#Let us Create an object 
logger=logging.getLogger() 


#burtList=[1399779];


Escalation_Status_Txt="JIRA escalation is closed. Hence, moving the escalation status to closed and archiving the escalation\n";

if VERSION == 24:
	try:
		#for burt in burtList:
		f = open("/x/eng/sustools/MFT/CPE_SolidFire_Sync/froyo_solidfire_burtlist.txt", "r");
	except (RuntimeError, TypeError, NameError), e:
		print "I/O error({0}): {1}".format(e.errno, e.strerror);
	except:
		print "Unexpected error:", sys.exc_info()[0];
		sys.exit(1);
	
if VERSION == 27:
	try:
		#for burt in burtList:
		f = open("/x/eng/sustools/MFT/CPE_SolidFire_Sync/froyo_solidfire_burtlist.txt", "r");
	except subprocess.CalledProcessError , grepexc:
		print("error code : ", grepexc.returncode, grepexc.output);
		sys.exit(1);
	
for x in f:
	burtList=x.split(":");
	
		
	
for burt in burtList:
	burt=burt.strip();
	if len(burt) < 6 :
		continue;
	
	logger.info('BURT To be closed %s ',burt);
	if VERSION == 24:
		try:

			cmd="/usr/software/rats/bin/burt edit -xo " + str(burt);
			burtdetail, err = subprocess.Popen(cmd, stdout=subprocess.PIPE, shell=True,universal_newlines=True).communicate();
			#This is the burt CLI. Run burt view <burtnum> to get the details of a given burt linked to Jira item
			#burtdetail = subprocess.call('/usr/software/rats/bin/burt edit -xo ' + str(burt),shell=True,universal_newlines=True)
			logger.info('BURT To be closed %s ',burtdetail);
		except:
			print("error  could not edit the file ");
			logger.info("error  could not edit the file = %s",str(burt));
			continue;
	if VERSION == 27:	
		try:
			#This is the burt CLI. Run burt view <burtnum> to get the details of a given burt linked to Jira item
			burtdetail = subprocess.check_output('/usr/software/rats/bin/burt edit -xo ' + str(burt),shell=True,universal_newlines=True)
			logger.info('BURT To be closed %s ',burtdetail);		
		except subprocess.CalledProcessError , grepexc:
			print("error code : ", grepexc.returncode, grepexc.output);
			logger.info('error code : = %s  grepexc.output=%s ',grepexc.returncode,grepexc);
			continue;

	#===== Short_Fields ====================================================
	# this will take above line & get the first line
	#if VERSION == 24:
	first_line= (burtdetail.split("\n")[0] ,burtdetail.split("\n")[1])[len(burtdetail.split("\n")[0]) ==0];
	#print(first_line);
	#x = lambda a: a[1] if len(a[0]) ==0 else a[0];
	#first_line=x(burtdetail.split("\n"));
	#print(first_line);

	state='';
	
	#TBD --> make it empty
	cust_defect_type='';
	
	#NEW --> CLOSED
	escal_status='';
	
	#Escalation_Status
	Escalation_Status='';
	

	
	#Iterate over the burt state and break when found
	for item in burtdetail.split("\n"):
		if item.startswith('state'):
			state=item;
			break;

	
	#Iterate over the burt cust_defect_type and break when found
	#for item in burtdetail.split("\n"):
	#	if item.startswith('cust_defect_type'):
	#		cust_defect_type=item;
	#		break;

	#Iterate over the burt escal_status and break when found
	for item in burtdetail.split("\n"):
		if item.startswith('escal_status'):
			escal_status=item;
			break;
	
		
	#Escalation_Status
	#Iterate over the burt Escalation_Status and break when found
	#for item in burtdetail.split(" "):
	#	if item.startswith('Escalation_Status'):
	#		Escalation_Status=item;
	#		break;
			
	#print(state);
	#print(escal_status);
	#print(cust_defect_type);
	#print(Escalation_Status);

	#State has a bunch of whitespace. Strip it out and just get the actual state
	state = state.split(" ");
	#print(state);
	state = state[-1];
	#print(state);
	logger.info('BURT=  %s state= %s ',burt,state);
	#escal_status has a bunch of whitespace. Strip it out and just get the actual state
	escal_status = escal_status.split(" ");
	#print(escal_status);
	escal_status = escal_status[-1];
	#print(escal_status);
	logger.info('BURT= %s escal_status= %s ',burt,escal_status);
	#cust_defect_type has a bunch of whitespace. Strip it out and just get the actual state
	#cust_defect_type = cust_defect_type.split(" ");
	#print(cust_defect_type);
	#cust_defect_type = cust_defect_type[-1];
	#print(cust_defect_type);
	
	#Escalation_Status has a bunch of whitespace. Strip it out and just get the actual state
	#Escalation_Status = Escalation_Status.split(" ");
	#print(burtdetail);
	#Escalation_Status = Escalation_Status[-1];
	#print(Escalation_Status);

  
  
		
	#Update the burt state from NEW to CLOSED
	if state == "NEW" :
		burtdetail = burtdetail.replace('state                    NEW','state                    CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	elif state == "OPEN" :
		burtdetail = burtdetail.replace('state                    OPEN','state                    CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	else:
		continue;
	
	#Update the burt cust_defect_type from TBD to "" empty
	#burtdetail = burtdetail.replace('cust_defect_type                    TBD','cust_defect_type                    ')
	#burtdetail = burtdetail.split("\n",1)[1];	
	
	#Update the burt escal_status from NEW to CLOSED and dont change any space, it will not work
	if escal_status == "NEW" :
		burtdetail = burtdetail.replace('escal_status             NEW','escal_status             CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	elif escal_status == "RESOLVED" :
		burtdetail = burtdetail.replace('escal_status             RESOLVED','escal_status             CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	elif escal_status == "WAIT_EE_CUST" :
		burtdetail = burtdetail.replace('escal_status             WAIT_EE_CUST','escal_status             CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	elif escal_status == "RCA_DONE" :
		burtdetail = burtdetail.replace('escal_status             RCA_DONE','escal_status             CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	else: 
		burtdetail = burtdetail.replace('escal_status             ACTIVE','escal_status             CLOSED');
		burtdetail = burtdetail.split("\n",1)[1];
	
	
	
	
	text=first_line + "\r\n" + burtdetail;
	#print(text);
	burtdetail=text;
	
	

	
	#Use this temporary file to store all burt fields as it is needed for burt state changes from the CLI
	f = open ("/u/haramoha/burt_close/script/tempburtFile.txt","w");
	for line in burtdetail.split("\n"):
		if (line.find('RCA_Notes') != -1):
			f.write("\n");
			f.write(Escalation_Status_Txt);
			f.write("\n");
		
		f.write(line);
		f.write("\n");

	f.close();

	#cmd="/usr/software/rats/bin/burt addnotes -field Escalation_Status " + str(burt) + " \"JIRA escalation is closed moving the escalation status to closed\"";

	# Check to see if any burts are in STUDY state. If they are... update them to OPEN
	if (state == "NEW" or state == "OPEN"):
		
		#print("State update is needed. Attempting to move to CLOSED for burt:  " + str(burt) +". \n")
		time.sleep(2);
		if VERSION == 24:			
			try:
				f = open ("/u/haramoha/burt_close/script/tempburtFile.txt","r");
				#print("State update is needed. Attempting to move to CLOSED for burt:  " + str(burt) +". \n");
				cmd="/usr/software/rats/bin/burt edit -xi " + str(burt);
				response, err = subprocess.Popen(cmd, stdout=subprocess.PIPE, stdin=f,shell=True,universal_newlines=True).communicate();
			except:
				print("error  could not edit the file while closing ");
				logger.info("error  could not edit the filewhile closing = %s",str(burt));
				f.close();
		if VERSION == 27:
			try:
				f = open ("/u/haramoha/burt_close/script/tempburtFile.txt","r")
				#print("State update is needed. Attempting to move to CLOSED for burt:  " + str(burt) +". \n");
				response = subprocess.check_output('/usr/software/rats/bin/burt edit -xi ' + str(burt) + " ",stdin=f,shell=True,universal_newlines=True);
			except subprocess.CalledProcessError , grepexc:                                                                                                   
				print("error code : ", grepexc.returncode, grepexc.output);
				logger.info('error code : = %s  grepexc.output=%s ',grepexc.returncode,grepexc.output);
				f.close();
				#sys.exit(1);
			
		f.close();
	else:
		print("Burt Id is not closed for burt " + str(burt) + ".\n");
	
	
	
	
	
	
