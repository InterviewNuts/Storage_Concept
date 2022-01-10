import subprocess
import time
import urllib3
import os
import getpass
import logging



#now we will Create and configure logger 
#logging.basicConfig(filename="burtClosing.log",format='%(asctime)s %(message)s', filemode='w');

logging.basicConfig(filename="burtClosing.log",level=logging.DEBUG,filemode='w')

#Let us Create an object 
logger=logging.getLogger() 

urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)



#burtList=[1399779];

username = getpass.getuser();
Escalation_Status_Txt="JIRA escalation is closed. Hence, moving the escalation status to closed and archiving the escalation\n";
result = subprocess.check_output("date", shell=True)
#authorTxt="-- Author: {0}  Date: {1}--------------\n".format(username,result.rstrip("\n") );
#authorTxt_1="-- SolidFire Enginner's Comment : {0}  Date: {1}--------------\n".format(username,result.rstrip("\n") );



#for burt in burtList:
f = open("burtCloseFile.txt", "r")
for x in f:
	burtList=x.split(":");
	
		
	
for burt in burtList:
	burt=burt.strip();
	if len(burt) < 6 :
		continue;
	
	logger.info('BURT To be closed %s ',burt);
	try:
		#This is the burt CLI. Run burt view <burtnum> to get the details of a given burt linked to Jira item
		burtdetail = subprocess.check_output('/usr/software/rats/bin/burt edit -xo ' + str(burt),shell=True,universal_newlines=True)
		logger.info('BURT To be closed %s ',burtdetail);
	except subprocess.CalledProcessError as grepexc:
		print("error code : ", grepexc.returncode, grepexc.output);
		logger.info('error code : = %s  grepexc.output=%s ',grepexc.returncode,grepexc);
		continue;
	#===== Short_Fields ====================================================
	# this will take above line & get the first line
	#first_line= (burtdetail.split("\n")[0] ,burtdetail.split("\n")[1])[len(burtdetail.split("\n")[0]) ==0];
	#print(first_line);
	x = lambda a: a[1] if len(a[0]) ==0 else a[0];
	first_line=x(burtdetail.split("\n"));
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
	f = open ("/u/haramoha/sahu/script/python/tempburtFile.txt","w");
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
		f = open ("/u/haramoha/sahu/script/python/tempburtFile.txt","r") 
		try:
			#print("State update is needed. Attempting to move to CLOSED for burt:  " + str(burt) +". \n");
			response = subprocess.check_output('/usr/software/rats/bin/burt edit -xi ' + str(burt) + " ",stdin=f,shell=True,universal_newlines=True)
			
		except subprocess.CalledProcessError as grepexc:                                                                                                   
			print("error code : ", grepexc.returncode, grepexc.output);
			logger.info('error code : = %s  grepexc.output=%s ',grepexc.returncode,grepexc.output);
			f.close();
			#sys.exit(1);
			
		f.close();
	else:
		print("Burt Id is not closed for burt " + str(burt) + ".\n");
	
	
	
	
	
	