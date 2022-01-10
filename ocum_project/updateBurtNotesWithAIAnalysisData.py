import json
import os
import platform
import subprocess
import sys;
import time
from pathlib import Path

if sys.version_info[0] == 2 and sys.version_info[1] == 4:
    # print (sys.version_info);
    print(platform.python_version())
    # print(platform.python_version_tuple());
    version = 24
elif sys.version_info[0] == 2 and sys.version_info[1] == 7:
    version = 27
    # print (sys.version_info);
    print(platform.python_version())
# print(platform.python_version_tuple());

# Path("/u/haramoha/ravi/updateBurtNotes/report_all").touch()
# Path("/u/haramoha/ravi/updateBurtNotes/report_new").touch()
# Path("/u/haramoha/ravi/updateBurtNotes/newBurts.txt").touch()
# fileName='/u/haramoha/ravi/updateBurtNotes/report_all'

# if os.path.exists(fileName):
# os.remove(fileName)
#	Path("/u/haramoha/ravi/updateBurtNotes/test.cron").touch()
# Path("/u/haramoha/ravi/updateBurtNotes/report_all").touch()
#	os.remove("/u/haramoha/ravi/updateBurtNotes/report_new")
#	os.remove("/u/haramoha/ravi/updateBurtNotes/newBurts.txt")


# read the content which need to be updated in the burts. The first Line must be the burt ID and rest the contents

burtHeader = "Hi, I'm troubleshooting Assistant ! \n My job is to learn from the past solved issues and suggest you the data collection points required to solve this problem statement \n and further the closest matches in terms of KBs, BURTs, DOCs and LLP actions. \n Please fill out 2 survey questions (https://www.questionpro.com/t/ASoMJZpSBo) to help me improve ! \n \n"


def updateNotesSectionOfBurt(burtid, notes):
    burt = burtid.strip()
    burtHeaderUpdate = True
    try:
        # This is the burt CLI. Run burt view <burtnum> to get the details of a given burt linked to Jira item
        burtdetail = subprocess.check_output('/usr/software/rats/bin/burt edit -xo ' + str(burt), shell=True,
                                             universal_newlines=True)
    except subprocess.CalledProcessError as grepexc:
        print(" Exceptin in updateNotesSectionOfBurt() :  could not open the burt  in edit mode Please check ", str(burt))
        print('error code : = %s  grepexc.output=%s ', grepexc.returncode, grepexc.output)
        raise
    finally:
        print(" Now , updates successfully, hene close the tempburtFile ")
        pass

    # ===== Short_Fields ====================================================
    # this will take above line & get the first line
    # first_line= (burtdetail.split("\n")[0] ,burtdetail.split("\n")[1])[len(burtdetail.split("\n")[0]) ==0];

    firstLineBlank = False
    # Any burt file , if you open it in edit mode, then after modification, if you want to submit it, then the first line
    # of the burt file must be like below.
    # ===== Short_Fields ====================================================
    # What happend actiualy , if burt edit -xo burtid will create a blank line , that is the reason, I am checking
    # wheather blank line is there or not. if it is there, I need to remove it, otherwise it will thron exception.
    # 2 point , if we dont find  the said blankk line , because of our modification, then I am just keeping it in avariable
    # for further use , But as of now in this script , I am not loosing this able line, hence not requured, but defensive cod eis writen
    # for future use.
    if len(burtdetail.split("\n")[0].strip()) == 0:
        firstLineBlank = True
        # since first line is blank, i am taking the first line content from second line.
        first_line = burtdetail.split("\n")[1]
    else:
        first_line = burtdetail.split("\n")[0]
        firstLineBlank = False

    # text=first_line + "\r\n" + burtdetail;
    # burtdetail=text;

    # Use this temporary file to store all burt fields as it is needed for burt notes section updated from the CLI
    f = open("/u/haramoha/ravi/updateBurtNotes/tempburtFile.txt", "w")

    for line in burtdetail.split("\n"):
        # here I am just cheking for the Rel_Notes, whereever I found, just add my content before that.
        # Noetes section is available , just befor this string. :)
        if (line.strip().find("===== Rel_Notes ") != -1):
            # print(line.strip())
            # if (line.find("===== Notes =================== [APPEND-ONLY] =========================") != -1):
            f.write("\n")
            if burtHeaderUpdate:
                f.write(burtHeader)
                burtHeaderUpdate = False

            f.write(notes)
            f.write("\n")
            f.write("\n")

        # Here if I found my burt file first line is blank, then dont take that blank line to file, just ignore it
        # as this will trigger exception, Burt will not be updated.
        if firstLineBlank:
            firstLineBlank = False
        else:
            # So here , first line is ignored, & I am just taking al these thing as it is to a temporary file
            # which then be fed  as input to burt command below.
            f.write(line)
            f.write("\n")

    # /u/haramoha/ravi/updateBurtNotes/tempburtFile.txt
    f.close()

    time.sleep(2)
    # Now After ther pause of 2 miliseconds, open it for reading & then send this one as input file to the stdin,
    # so that contents gets updated in the Notes section of the Burt.
    f = open("/u/haramoha/ravi/updateBurtNotes/tempburtFile.txt", "r")
    try:
        # print("State update is needed. Attempting to move to CLOSED for burt:  " + str(burt) +". \n");
        response = subprocess.check_output('/usr/software/rats/bin/burt edit -xi ' + str(burt) + " ", stdin=f,shell=True, universal_newlines=True)
        print(" burt has beedn updated ", str(burt))
        print(" Burt update response  ", str(response))
    except subprocess.CalledProcessError as grepexc:
        print("error code ", grepexc.returncode, grepexc.output);
        print(grepexc)
        # I am thinking to send an email to manager that The said burt could not be updated.
        # I will write another module to to send mail.
        f.close()
        raise
    # sys.exit(1);
    finally:
        # Now , updates successfuke, henclose the tempburtFile
        f.close()


# class GetRestData:
#    def __init__(self):
#        pass

# instance = None

# def __new__(cls):
#   if cls.instance is None:
#       cls.instance = super().__new__(cls)
#   return cls.instance

#	@staticmethod
def getRestData(prod, title):
    # print(prod)
    # print(title)
    # prd = prod
    host = "\'http://172.20.194.237:5001/summary\'"
    content_type = "\'Content-Type: application/json\'"
    # prod="\"sra\""
    openqt = "\""
    closeqt = "\""
    prod = openqt + prod + closeqt
    # query = "\"SRA Test failover failing.\""
    query = openqt + title + closeqt
    # curl --location  -X POST 'http://172.20.194.237:5001/summary'  -H 'Content-Type: application/json' -d '{"product" : "ocum" , "datasource" : "all", "search_query" : "AIQUM 9.9 backups from 2 separate systems are unable to be restored into new OVAs", "similarity_score": 0.6}'

    cmd = "curl --location -X POST " + host + " -H " + content_type + " -d "
    pd = "\"product\""
    ds = "\"datasource\""
    al = "\"all\""
    sq = "\"search_query\""
    ss = "\"similarity_score\""
    sd = 0.6
    cmd1 = "\'{"
    cmd2 = "}\'"
    cmd3 = cmd1 + pd + " : " + prod + "," + ds + ":" + al + "," + sq + ":" + query + "," + ss + " : " + str(sd) + cmd2
    # print ( cmd + cmd3)
    try:
        burtdetail = subprocess.check_output(cmd + cmd3, shell=True, universal_newlines=True)
        y = json.loads(burtdetail)
        # print(y["summary"]["Commonly used Data Collection Points"])
        # print(json.dumps(y, indent=4))
        return json.dumps(y, indent=4)
    except subprocess.CalledProcessError as grepexc:
        print(" Rest Api call failled")
        print("error code ", grepexc.returncode, grepexc.output)
        print(grepexc)
        # Here also I am thing to send an email
        raise grepexc
        pass


def getBurtDetails():
    # This is the burt CLI. Run burt view <burtnum> to get the details of a given burt
    # subprocess.check_output("burt report -q'$type =~ /^(CPE)/ && $state =~ /NEW|OPEN/ && $subtype =~ /cpe_client/ && $date_create =~ /1/' -f:'id	state   call_rec date_create  keywords:CPE_SUBTYPE   subtype  title' -s 'pri' -xdays > /u/haramoha/ravi/report_all",shell=True,universal_newlines=True)
    # burtdetail = subprocess.check_output("burt report -q'$type =~ /^(CPE)/ && $state =~ /NEW|OPEN/ && $subtype =~ /cpe_client/ && $date_create =~ /1/' -f:'id	state   call_rec date_create  keywords:CPE_SUBTYPE   subtype  title' -s 'pri' -xdays",shell=True,universal_newlines=True)
    # print(burtdetail)
    # print("This script gets the new Burt & then gets the data from rest server and finaly update the burts's notes section")
    # Path("/u/haramoha/ravi/updateBurtNotes/report_all").touch()
    import datetime

    Now = datetime.datetime.now()
    today = datetime.datetime.now().strftime("%Y%m%d")
    dateFileExt = ".date"
    todayDateFile = today + dateFileExt

    # this will clean up files on daily basis.
    if not os.path.exists(todayDateFile):
        print(todayDateFile)
        try:
            os.remove("/u/haramoha/ravi/updateBurtNotes/*.date")
        except OSError:
            print("Could not delete Date files ")
            pass
        finally:
            Path("/u/haramoha/ravi/updateBurtNotes/" + todayDateFile).touch()
            print(todayDateFile, "file created!!! ")

    try:
        #  && $date_create =~ /1/
        # gets the burt details.
        burtdetail = subprocess.check_output(
            "burt report -q'$type =~ /^(CPE)/ && $state =~ /NEW|OPEN/ && $subtype =~ /cpe_client/' -f:'id state call_rec date_create keywords:CPE_SUBTYPE subtype title' -s 'pri' -xdays",
            shell=True, universal_newlines=True)
    except:
        print(" could get get  burts details  burt report command failed")

    # print("This script gets the new Burt & then gets the data from rest server and finaly update the burts's notes section")
    firstLine = 0
    print("\n")

    # This mapping I got it from Ravi & verified by SUKANYA as well.
    prd_dict = dict()
    prd_dict["SC_Server"] = "scserver"
    prd_dict["SnapCenter_Server"] = "scserver"
    prd_dict["OCUM6x"] = "ocum"
    prd_dict["OCUM"] = "ocum"
    prd_dict["SRA"] = "sra"
    prd_dict["SCW"] = "scpwindows"
    prd_dict["SCV"] = "scpscv"
    prd_dict["SCSQL"] = "scpsql"
    prd_dict["VSC"] = "vsc"
    prd_dict["SCC"] = "scpcustom"
    prd_dict["SCE"] = "scpexchange"
    prd_dict["SCO"] = "scporacle"
    prd_dict["SCU"] = "scporacle"

    for line in burtdetail.split("\n"):
        # print(line)
        if line.startswith("id"):
            pass
        # This is intended to ignore the header of the burt report
        # ['id  state call_rec  days_create CPE_SUBTYPE  subtype ', 'title ']

        elif line.strip():
            # split the the line  between title and before, so that titleSplit[0] will contain
            # 'id      state      call_rec        days_create   CPE_SUBTYPE subtype'
            titleSplit = line.split("|")
            # print(titleSplit)
            burtTitleSplitLine = titleSplit[1].strip().split(":")


            # Now split the first part of the line
            # 1412058 OPEN  2008817482  91   WFA  cpe_client
            # print(titleSplit[0])

            firstPartOfSplit = titleSplit[0].strip().split()
            # print(firstPartOfSplit[0])
            days_create = firstPartOfSplit[3].strip()
            burtid = firstPartOfSplit[0].strip()
            product = firstPartOfSplit[4].strip()
            # temporary code -- sahu

            # if product == "OCUM6x":
            # 	burtTitleRequiredSearKeyLine=burtTitleSplitLine[1].strip()

            # print("%s %s %s %s" %(burtid,product ,days_create,burtTitleRequiredSearKeyLine))
            # Path("/u/haramoha/ravi/test.cron").touch()
            burtExt = ".burt"
            if int(days_create) < 20 and product in prd_dict:
                # Now get the only search line required for REST EndPoint
                burtTitleRequiredSearKeyLine = burtTitleSplitLine[1].strip()
                # print("%s " %(days_create))
                # print("%s " %(burtid))
                filePath = "/u/haramoha/ravi/updateBurtNotes/"
                burtFileName = filePath + str(burtid)
                if os.path.exists(burtFileName + burtExt):
                    print("Already Notes Section is updated for the burt id :", str(burtid))
                else:
                    # try to call the rest endpoints here & then format it and update the burt's notes section.
                    # print("Product to be quiried ",product)
                    #if product in prd_dict:
                    if True:
                        #restJson = getRestData(prd_dict[product], burtTitleRequiredSearKeyLine)
                        print("=============================================================")
                        #print(restJson)
                        print("Burt id = ", burtid,"product = ",product)
                        print("burtTitleRequiredSearKeyLine = ", burtTitleRequiredSearKeyLine)
                        print("=============================================================")
                        # print(burtTitleRequiredSearKeyLine);
                        # print("getting Data for the Burt id =",str(burtid));
                        # Now update the burt id's Notes Section."
                        try:
                            # burtid="1434997"
                            # burtid="1431159"
                            # print(" Hara sahu prodcut = ",product)
                            # if str(product) == "OCUM6x":
                            # 	print(" burtTitleRequiredSearKeyLine =",burtTitleRequiredSearKeyLine)
                            # 	print(" Inside burtid =",burtid)
                            # 	restJson=getRestData(prd_dict[product],burtTitleRequiredSearKeyLine)
                            # 	updateNotesSectionOfBurt( burtid,restJson)
                            # print(restJson);
                            # Now update the burt
                            #updateNotesSectionOfBurt(burtid, restJson)
                            print("updating Notes Section Of Burt Finished with SUCCESS......")
                            # Touching file is required as this will be used to avoind updating the burts time and again.
                            # As this script will be runing  every 1 hour interval, so script checks it ,
                            # as onece successfuly burts gets updated, it does  touch a file with burtid.burt(1432401.burt)
                            # In the next subsequent time, if this file presents, then burt will not be updated. This is a marker file.
                            # IMPORTANT, DONT DELETE these .burt files. As this is the market files.
                            #  HARAMOHAN SAHU : In my opinion, checking a TOOL User name is best options. Since I did not get any answer
                            # FRON NETAPP, I am using this 123456.burt files technique.
                            #
                            Path(burtFileName + burtExt).touch()
                        except:
                            print("Exception occuredBurt id could not be updated...", str(burtid))
                            if os.path.exists(burtFileName + burtExt):
                                os.remove(burtFileName + burtExt)
            else:
                pass


# print(" There is no new burt created as of now which is older then 4 days\n")


if __name__ == "__main__":
    getBurtDetails()
