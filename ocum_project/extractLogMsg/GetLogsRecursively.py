import os
import platform
import re
from os.path import isfile
import magic

from GetLogMsg import GetAllLogs
from downloadModule import UncompressAllFilesRecursively

if platform.system() == "Linux":
    logGlobal = os.path.join(os.getcwd())+"//log_dir"
    file_not_processed = os.path.join(os.getcwd()) + "/file_not_proccesed.txt"
    file_processed = os.path.join(os.getcwd()) + "/file_proccesed.txt"

else:
    logGlobal = os.path.join(os.getcwd())+"\log_dir"
    file_not_processed = os.path.join(os.getcwd()) + "\\file_not_proccesed.txt"
    file_processed = os.path.join(os.getcwd()) + "\\file_proccesed.txt"

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
        self.fileNotProcessed = open(file_not_processed, "w")
        self.fileProcessed = open(file_processed, "w")
        if argList.caseId and argList.logType and argList.extract:
            self.getAllLogObj = GetAllLogs(argList)
            self.extractObj = UncompressAllFilesRecursively(argList)
            self.extract = str(argList.extract)
            if self.extract == "yes":
                self.extractObj.unzipFileRecursively()

    def getAllErrorLogsRecursively(self, directory: str = None) -> None:
        # assert os.path.isdir(directory)
        # os.chmod(directory, 0o777)
        curPath = os.getcwd() if directory is None else directory
        print(curPath)
        for current_path, directories, files in os.walk(curPath):
            os.chdir(current_path)
            if (os.getcwd().__contains__('jboss')):
                for file in os.listdir(os.getcwd()):
                    if isfile(file):
                        typeoffile = magic.Magic(mime=True).from_file(file)
                        if typeoffile.__contains__('text/plain'):
                            # print("jboss", file)
                            if self.getAllLogObj.error:
                                self.getAllLogObj.getJbossErrorLog(
                                    file, os.path.relpath(current_path, logGlobal))

            if not (os.getcwd().__contains__('jboss')):
                for file in os.listdir(os.getcwd()):
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
                                    self.getAllLogObj.getErrorXML(
                                        file, os.path.relpath(current_path, logGlobal))
                            if typeoffile.__contains__('text/plain'):
                                self.fileProcessed.write("%s\n" % strName)
                                processedFiles.add(strName)
                                if self.getAllLogObj.error:
                                    self.getAllLogObj.getErrorLog(file, os.path.relpath(current_path, logGlobal))
                                if self.getAllLogObj.warn:
                                    self.getAllLogObj.getWarnLog(file, os.path.relpath(current_path, logGlobal))
                                if self.getAllLogObj.info:
                                    self.getAllLogObj.getInfoLog(file, os.path.relpath(current_path, logGlobal))
                                if self.getAllLogObj.debug & ('.log' in file):
                                    self.getAllLogObj.getDebugLog(file, os.path.relpath(current_path, logGlobal))
                                if self.getAllLogObj.trace:
                                    self.getAllLogObj.getTraceLog(file, os.path.relpath(current_path, logGlobal))
                            else:
                                print(" Type of file Sahu: ", typeoffile, file)
                                self.fileNotProcessed.write("%s\n" % strName)
                                notProcessedFiles.add(strName)

    def getAllfileNameAndExt(self):
        filePath = os.path.abspath('.')
        os.chdir(filePath)
        self.ExtFile = open("All_Extensions.txt", "w")
        for curExt in AllExtensions:
            self.ExtFile.write("%s\n" % curExt)
        self.ExtFile.close()

        self.FileNames = open("All_Unqiue_Files.txt", "w")
        for curFileName in AllUniqueFileNames:
            self.FileNames.write("%s\n" % curFileName)
        self.FileNames.close()

        self.processeFile = open("processed.txt", "w")
        for f in processedFiles:
            self.processeFile.write("%s\n" % f)
        self.processeFile.close()

        self.NotprocesseFile = open("notProcessed.txt", "w")
        for curFileName in notProcessedFiles:
            self.NotprocesseFile.write("%s\n" % curFileName)
        self.NotprocesseFile.close()

    def iterateLo_dir(self):
        os.chdir(logGlobal)
        for dir in os.listdir(os.getcwd()):
            if os.listdir(dir):

                os.chdir(dir)

                parent = os.path.dirname(os.getcwd())

                self.getAllErrorLogsRecursively(os.getcwd())

                os.chdir(parent)

    def removeDuplicateFromFils(self):
        self.getAllLogObj.removeDuplicateFromFils()

    def getUserGivenDurationLog(self):

        self.getAllLogObj.getUserGivenDurationLog()

    def cleanUp(self):
        self.getAllLogObj.cleanUp()


def main(argList):
    o = ExtractAllLogs(argList)
    o.iterateLo_dir()
    o.removeDuplicateFromFils()

    o.getAllfileNameAndExt()
    if argList.logDate:
        if argList.logTime:
            o.getUserGivenDurationLog()
        else:
            print("Please provide Log time!!! ")

# if __name__ == "__main__":
#     main()
