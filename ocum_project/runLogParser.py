try:
    import os
    import threading
    from threading import *
    import time
    import asyncio
    import subprocess
    import smtplib
    import argparse
    from time import sleep, perf_counter
    from email.mime.text import MIMEText
except ImportError:

    os.system("pip install argparse")
    os.system("pip install asyncio")
    os.system("pip install subprocess")
    os.system("pip install smtplib")
    import os
    import threading
    from threading import *
    import time
    import asyncio
    import subprocess
    import smtplib
    import argparse
    from time import sleep, perf_counter
    from email.mime.text import MIMEText

filePath = "/x//eng//cs-data//CPE_TroubleShootingAsistance_Sync"
fileName = "CPE_TroubleShooting_BurtList_For_LogParsing.txt"

invokeFile = "python //root//NetAppTroubleshootingAssistant//main.py"


class sendMail:
    def __init__(self, sub, txt, to="haramoha@netapp.com", fromMail="haramoha@netapp.com",
                 cc="mkomarth@netapp.com, deepakk9@netapp.com"):
        # def __init__(self, sub, txt, to="haramoha@netapp.com", fromMail="haramoha@netapp.com", cc="cwharamohamkomarth@netapp.com, haramoha@netapp.com"):
        self.sender = fromMail
        self.receivers = to
        self.port = 25
        self.msg = MIMEText(txt)

        self.msg['Subject'] = str(sub)
        self.msg['From'] = str(fromMail)
        self.msg['To'] = str(to)
        self.msg['Cc'] = str(cc)

    def sendMail(self):
        with smtplib.SMTP('smtp.netapp.com', self.port) as server:
            # server.login('haramoha@netapp.com', 'xxxxxxxxx')
            server.sendmail(self.sender, self.receivers, self.msg.as_string())
            print("Successfully sent email")


def main_thread_reading_MFT_File():
    threads = []
    res_dct = dict()
    start_time = perf_counter()
    # read file
    with open(filePath + "//" + fileName, "r") as fn:
        line = fn.readline()
        while line:
            parseList = line.split(":")
            init = iter(parseList)
            res_dct = dict(zip(init, init))
            for key, value in res_dct.items():
                # print('%s => %s' % ( key,value))
                nextstr = " --caseId " + key.strip() + "  --dcplist list --logType error  --prodName "
                cmd = invokeFile + nextstr + value.strip() + " --extract yes "
                # print(cmd)
                try:
                    thread = Thread(target=runLogParser, args=(cmd, key.strip()))
                    thread.start()
                    threads.append(thread)
                    print("threading.activeCoun= ", threading.activeCount())
                    print("The name of the thread curr", threading.current_thread().name)
                except:
                    print(" exception occured ", key.strip())
                    pass

                print("All hread thread finished...Means all log parsed, Please check /x/eng/cs-data/case id....")

                # await runLogParser(cmd)
                print("Finished Case id = ", key.strip(), " For Product = ", value.strip())
            line = fn.readline()

    # Wait for all threads to complete
    for t in threads:
        try:
            t.join()
        except  Exception as e:
            print("Excepion occured while in the thread join ", e)
            obj = sendMail("Extraction rasie some exception \n\n ", e)
            obj.sendMail()

    end_time = perf_counter()
    obj = sendMail("All the extracted processed successfuly\n\n",
                   str(res_dct) + f'\nIt took {end_time - start_time: 0.2f} second(s) to complete.')
    obj.sendMail()
    print(f'It took {end_time - start_time: 0.2f} second(s) to complete.')
    print("Exiting Main Thread")


def main_thread_reading_CmdLine(args):
    threads = []
    res_dct = dict()
    start_time = perf_counter()

    # with open(filePath + "//" + fileName, "r") as fn:
    for i in args.caseId:
        nextstr = " --caseId " + str(args.caseId) + "  --dcplist" + str(args.dcplist) + "  --logType " \
                  + str(args.logType) + " --prodName " + str(args.prodName) + " --bundleName " + str(args.bundleName)
        cmd = invokeFile + nextstr + " --extract" + str(args.extract)
        print(cmd)
        # sys.exit()
        try:
            thread = Thread(target=runLogParser, args=(cmd, str(args.caseId)))
            # thread.start()
            threads.append(thread)
            print("threading.activeCoun= ", threading.activeCount())
            print("The name of the thread curr", threading.current_thread().name)
        except:
            print(" exception occured for case id ", str(args.caseId))
            pass

        print("All hread thread finished...Means all log parsed, Please check /x/eng/cs-data/case id....")

        # await runLogParser(cmd)
        print("Finished Case id = ", str(args.caseId), " For Product = ", str(args.prodName))

    # Wait for all threads to complete
    for t in threads:
        try:
            t.join()
        except  Exception as e:
            print("Excepion occured while in the thread join ", e)
            obj = sendMail("Extraction rasie some exception \n\n ", e)
            obj.sendMail()

    end_time = perf_counter()
    obj = sendMail("All the extracted processed successfuly\n\n",
                   args + f'\nIt took {end_time - start_time: 0.2f} second(s) to complete.')
    obj.sendMail()
    print(f'It took {end_time - start_time: 0.2f} second(s) to complete.')
    print("Exiting Main Thread")


def runLogParser(cmd, caseId):
    try:
        time.sleep(1)
        print(cmd, caseId)
        # ii = subprocess.check_output(cmd, shell=True,universal_newlines=True)
    except subprocess.CalledProcessError as grepexc:
        print(grepexc)
        print("error code : ", grepexc.returncode, grepexc.output)
        me = 'haramoha@netapp.com'
        cc = 'mkomarth@netapp.com, deepakk9@netapp.com '
        to = 'haramoha@netapp.com'
        bcc = 'mkomarth@netapp.com, deepakk9@netapp.com'
        sub = " I am am subject testing mail"
        txt = "Exception Occured while unzipping file for the case id" + caseId + "\n" + grepexc
        obj = sendMail(sub, txt, to, me, cc)
        obj.sendMail()


if __name__ == '__main__':
    parser = argparse.ArgumentParser()

    # Add an argument
    parser.add_argument('--prodName', type=str, required=False)
    parser.add_argument('--bundleName', type=str, required=False)
    parser.add_argument('--caseId', type=int, required=False)
    parser.add_argument('--dcplist', type=str, required=False)
    parser.add_argument('--logType', type=str, nargs='+', required=False)
    parser.add_argument('--logDate', type=str, required=False)
    parser.add_argument('--logTime', type=str, required=False)
    parser.add_argument('--logDuration', type=str, default=30, required=False)
    parser.add_argument('--extract', default="yes", required=False)

    args = parser.parse_args()

    start_time = perf_counter()
    if args.prodName == None and args.bundleName == None and args.caseId == None and args.logType == None:
        main_thread_reading_MFT_File()
    else:
        main_thread_reading_CmdLine(args)

    end_time = perf_counter()
    print(f'It main took {end_time - start_time: 0.2f} second(s) to complete.')
    print("thread finished...exiting")
