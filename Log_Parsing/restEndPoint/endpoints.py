import platform
from flask import Flask, jsonify
import os


def callEndpoints():
    app = Flask(__name__)

    # api for logs path
    @app.route('/<string:productName>/<int:caseID>', methods=['GET'])
    def pathToFile(productName, caseID):
        if platform.system() == "Linux":
            filePath = (os.getcwd()+"//segregated_log//"+productName)
        else:
            filePath = (os.getcwd()+"\\segregated_log\\"+productName)
        return jsonify("Path to file = " + filePath)

    # api for each final file
    @app.route('/<string:productName>/<int:caseID>/<string:logType>', methods=['GET'])
    def get_info(productName, caseID, logType):
        if platform.system() == "Linux":
            filePath = ("segregated_log//" + productName +
                        "//" + "FINAL_" + str(caseID) + "_"+logType+"_WITHOUT_DUP_LOG.log")
        else:
            filePath = ("segregated_log\\" + productName +
                        "\\" + "FINAL_" + str(caseID) + "_"+logType+"_WITHOUT_DUP_LOG.log")

        op_dict = dict()
        with open(filePath, 'r') as fn:
            line = fn.readline()
            while line:
                splitLine = line.split(':==:', 1)
                key = splitLine[0]
                if key not in op_dict:
                    op_dict[key] = list()
                op_dict[key].append(str(splitLine[1]))
                line = fn.readline()

        return jsonify(op_dict)

    app.run(debug=False)
