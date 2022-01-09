import os
import platform
import re
from os.path import isfile
import magic

from GetLogMsg import GetAllLogs
from downloadModule import UncompressAllFilesRecursively


AllExtensions = set()
AllUniqueFileNames = set()
processedFiles = set()
notProcessedFiles = set()


# The job of this class is to traverse all the folder structure in the log_dir recursively and check if it is a text file
# then pass the file name as a argument to BaseLogger class .
# FYI : The derive class of BaseLogger is to open the file and try to find the keyword ERROR/WARNING,
# if the keyword is found, then extract the lines and write it into a error/warning file.


class ExtractAllLogs:

    def __init__(self, argList) -> None:
        #logGlobal = "//x//eng//cs-data//"
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir//
        self.caseId = argList.caseId
        if platform.system() == "Linux":
            self.logGlobal = "//x//eng//cs-data//"
            self.TroubleshootingAssistantDir = "TroubleshootingAssistant//log_dir//"
            self.TroubleshootingAssistantFolder = self.logGlobal + \
                str(argList.caseId)+"//"+self.TroubleshootingAssistantDir
            #logGlobal = os.path.join(os.getcwd())+"//log_dir"
            file_not_processed = self.logGlobal + \
                str(argList.caseId)+"//" + \
                "TroubleshootingAssistant//file_not_proccesed.txt"
            file_processed = self.logGlobal + \
                str(argList.caseId)+"//" + \
                "TroubleshootingAssistant//file_proccesed.txt"
            print(file_not_processed)
        else:
            logGlobal = os.path.join(os.getcwd())+"\log_dir"
            file_not_processed = os.path.join(
                os.getcwd()) + "\\file_not_proccesed.txt"
            file_processed = os.path.join(os.getcwd()) + "\\file_proccesed.txt"

        if argList.caseId and argList.logType:
            # This will give me the path=//x//eng//cs-data//2008696047//
            # //x//eng//cs-data//2008696047//TroubleshootingAssistant
            self.x_eng_cs_data_case_id = self.logGlobal + \
                str(argList.caseId)+"//"
            self.segregated_log = self.x_eng_cs_data_case_id + \
                "TroubleshootingAssistant//segregated_dir//"

            if not os.path.exists(self.TroubleshootingAssistantFolder):
                os.mkdir(self.x_eng_cs_data_case_id +
                         "//TroubleshootingAssistant")
                os.mkdir(self.TroubleshootingAssistantFolder)
            if not os.path.exists(self.segregated_log):
                os.mkdir(self.segregated_log)

            self.fileNotProcessed = open(file_not_processed, "w")
            self.fileProcessed = open(file_processed, "w")

            # Now get into to //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir//
            os.chdir(self.TroubleshootingAssistantFolder)

            self.extract = str(argList.extract)
            if self.extract == "yes":
                self.extractObj = UncompressAllFilesRecursively(argList, self.TroubleshootingAssistantFolder, self.segregated_log)
                self.extractObj.unzipFileRecursively(self.TroubleshootingAssistantFolder)
                print("All compressed files , un compressed Now (unzipFileRecursively) Now log parsing starts!!!")

            # Now collect all the logs
            self.getAllLogObj = GetAllLogs(
                argList, self.TroubleshootingAssistantFolder, self.segregated_log)

    def getAllErrorLogsRecursively(self, directory: str = None) -> None:
        #curPath = os.getcwd() if directory is None else directory
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant
        #curPath = self.TroubleshootingAssistantFolder
        # for current_path, directories, files in os.walk(self.TroubleshootingAssistantFolder):
        for current_path, directories, files in os.walk(directory):
            os.chdir(current_path)
            for file in os.listdir(os.getcwd()):
                # for file in os.listdir(self.TroubleshootingAssistantFolder):
                if not os.path.isdir(file):
                    uniFile = "".join(re.split("[^a-zA-Z.]+", file))
                    AllUniqueFileNames.add(uniFile)
                    FileExt = os.path.splitext(file)[1]
                    if not (any(num.isdigit() for num in FileExt)):
                        AllExtensions.add(FileExt)

                    if isfile(file):
                        typeoffile = magic.Magic(mime=True).from_file(file)
                        strName = os.getcwd() + " : " + typeoffile + " : " + file
                        if typeoffile.__contains__('text/xml'):
                            self.fileProcessed.write("%s\n" % strName)
                            processedFiles.add(strName)
                            if self.getAllLogObj.error:
                                self.getAllLogObj.getErrorXML(file, os.path.relpath(
                                    current_path, self.TroubleshootingAssistantFolder))
                        if not file.strip().lower().endswith('.sql'):
                            if typeoffile.__contains__('text/plain') or typeoffile.__contains__('application/csv'):
                         
                                self.fileProcessed.write("%s\n" % strName)
                                processedFiles.add(strName)
                                if self.getAllLogObj.error is True:
                                    self.getAllLogObj.getErrorLog(file, os.path.relpath(
                                        current_path, self.TroubleshootingAssistantFolder))
                                if self.getAllLogObj.warn:
                                    self.getAllLogObj.getWarnLog(file, os.path.relpath(
                                        current_path, self.TroubleshootingAssistantFolder))
                                if self.getAllLogObj.info:
                                    self.getAllLogObj.getInfoLog(
                                        file, os.path.relpath(current_path, self.TroubleshootingAssistantFolder))
                                if self.getAllLogObj.debug & ('.log' in file):
                                    self.getAllLogObj.getDebugLog(
                                        file, os.path.relpath(current_path, self.TroubleshootingAssistantFolder))
                                if self.getAllLogObj.trace:
                                    self.getAllLogObj.getTraceLog(
                                        file, os.path.relpath(current_path, self.TroubleshootingAssistantFolder))
                            else:
                                self.fileNotProcessed.write("%s\n" % strName)
                                notProcessedFiles.add(strName)

    def getAllfileNameAndExt(self):
        filePath = os.path.abspath('.')
        os.chdir(filePath)
        self.ExtFile = open(self.logGlobal+str(self.caseId)+"//" +
                            "TroubleshootingAssistant//All_Extensions.txt", "w")
        for curExt in AllExtensions:
            self.ExtFile.write("%s\n" % curExt)
        self.ExtFile.close()

        self.FileNames = open(self.logGlobal+str(self.caseId)+"//" +
                              "TroubleshootingAssistant//All_Unqiue_Files.txt", "w")
        for curFileName in AllUniqueFileNames:
            self.FileNames.write("%s\n" % curFileName)
        self.FileNames.close()

        self.processeFile = open(
            self.logGlobal+str(self.caseId)+"//"+"TroubleshootingAssistant//processed.txt", "w")
        for f in processedFiles:
            self.processeFile.write("%s\n" % f)
        self.processeFile.close()

        self.NotprocesseFile = open(
            self.logGlobal+str(self.caseId)+"//"+"TroubleshootingAssistant//notProcessed.txt", "w")
        for curFileName in notProcessedFiles:
            self.NotprocesseFile.write("%s\n" % curFileName)
        self.NotprocesseFile.close()

    def iterateLo_dir(self):
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir//
        os.chdir(self.TroubleshootingAssistantFolder)
        for dir in os.listdir(os.getcwd()):
            if os.path.isdir(dir):
                os.chdir(dir)
                parent = os.path.dirname(os.getcwd())
                self.getAllErrorLogsRecursively(os.getcwd())
                os.chdir(parent)

        self.fileNotProcessed.close()
        self.fileProcessed.close()

    def removeDuplicateFromFiles(self):
        self.getAllLogObj.removeDuplicateFromFiles()

    def getUserGivenDurationLog(self):

        self.getAllLogObj.getUserGivenDurationLog()

    def cleanUp(self):
        self.getAllLogObj.cleanUp()


def main(argList):
    o = ExtractAllLogs(argList)
    o.iterateLo_dir()
    o.removeDuplicateFromFiles()

    o.getAllfileNameAndExt()
    if argList.logDate:
        if argList.logTime:
            o.getUserGivenDurationLog()
        else:
            print("Please provide Log time!!! ")


if __name__ == "__main__":
    main()
