import os
import sys

import argparse

current_path = os.path.abspath('.')
sys.path.append(current_path)


# from extractLogMsg import GetLogsRecursively
# from flask import Flask, jsonify


# def callRest():
#     app = Flask(__name__)

#     incomes = [
#         {'description': 'salary', 'amount': 5000}
#     ]

#     @app.route('/incomes')
#     def get_incomes():
#         return jsonify(incomes)

#     app.run()


def testArgs(args):
    print(args.caseid)


if __name__ == "__main__":
    import time

    # argList=[]
    # Create the parser
    parser = argparse.ArgumentParser()

    # Add an argument
    parser.add_argument('--prodName', type=str, nargs='+', required=False)
    parser.add_argument('--caseId', type=int, nargs='+', required=True)
    parser.add_argument('--dcplist', type=str, required=False)
    parser.add_argument('--logType', type=str, nargs='+', required=False)
    parser.add_argument('--logDate', type=str, required=False)
    parser.add_argument('--logTime', type=str, required=False)
    parser.add_argument('--logDuration', type=str, default=30, required=False)
    parser.add_argument('--extract', default="yes", required=False)

    args = parser.parse_args()

    j = 0
    case_prod_dict = dict()
    for i in args.caseId:
        print(args.prodName[j])
        case_prod_dict[i] = args.prodName[j]
        j = j + 1

start_time = time.time()
# GetLogsRecursively.main(args)
# endpoints.callEndpoints()

print("Total Time taken = %s " % (time.time() - start_time))
