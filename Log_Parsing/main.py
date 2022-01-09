
import argparse
import os
import sys

from extractLogMsg import GetLogsRecursively
from restEndPoint import endpoints

current_path = os.path.abspath('.')
sys.path.append(current_path)




if __name__ == "__main__":
    import time

    parser = argparse.ArgumentParser()

    # Add an argument
    parser.add_argument('--prodName', type=str, required=True)
    parser.add_argument('--caseId', type=int, required=True)
    parser.add_argument('--bundleName', type=str, required=False)
    parser.add_argument('--dcplist', type=str, required=False)
    parser.add_argument('--logType', type=str,default="error", nargs='+', required=True)
    parser.add_argument('--logDate', type=str, required=False)
    parser.add_argument('--logTime', type=str, required=False)
    parser.add_argument('--logDuration', type=str, default=30, required=False)
    parser.add_argument('--extract', default="yes", required=False)

    args = parser.parse_args()
    
    print(args.bundleName)
    start_time = time.time()
    try:
        GetLogsRecursively.main(args)
    except Exception as e:
        print(" -------------------------------------\n")
        print(" could not parse the Logs folder : ",e)
        print(" -------------------------------------\n")
        raise e
        # send mail and clean TroubleshootingAssistant  folder
        #pass

    # call to update burt.

    

    #endpoints.callEndpoints()

    print("Total Time taken = %s " % (time.time() - start_time))
