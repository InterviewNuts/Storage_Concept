try:
    import os
    import sys
    import zipfile
    from pathlib import Path
    import platform
    import patoolib
    from pyunpack import Archive, PatoolError
    import filetype
    import py7zr
    import re
    #import subprocess
except ImportError:
    os.system('python -m pip install zipfile')
    os.system('python -m pip install pathlib')
    os.system('python -m pip install platform')
    os.system('pip install patool')
    #subprocess.check_call([sys.executable, "-m", "pip", "install", patool])
    os.system('python -m pip install pyunpack')
    os.system('python -m pip install filetype')
    os.system('python -m pip install py7zr')

    import zipfile
    from pathlib import Path
    import platform
    import patoolib
    from pyunpack import Archive, PatoolError
    import filetype
    import py7zr
    import re
    from os.path import isfile

curDir = os.getcwd()

# This file is responsibele to uncompress all the file recursively.


class UncompressAllFilesRecursively:
    def unzipBundle(self,argList=None, TA_log_dir=None, segregated_log=None):
        self.fileNameFromDataframe = list()
        self.singleWordFileList = list()
        self.bundleName=str(argList.bundleName)
    
        print(self.bundleName, " Bundlename given")

        from os import listdir
        from os.path import isfile, join
        import os
        import mimetypes
        import zipfile
        import tarfile

        self.caseId = str(argList.caseId)
        if platform.system() == "Linux":
            #pathDownloadGlobal = "//x//eng//cs-data//" + self.caseId
            # this will hol to download folder
            self.x_eng_cs_data_case_id = "//x//eng//cs-data//" + self.caseId

            #self.x_eng_cs_data_caseid_TroubleshootingAssistant = pathDownloadGlobal +"TroubleshootingAssistant"
            # this will hol to log_dir folder
            # //x//eng//cs-data//2008696047//TroubleshootingAssistant
            self.TroubleshootingAssistantDir = TA_log_dir
            self.segregated_log = segregated_log
        else:
            # windows
            basePath = "C:\\x\\eng\cs-data\\"
            pathDownloadGlobal = os.path.join(basePath, self.caseId)
            TA_log_dir = os.path.join(os.getcwd()) + "\\log_dir"

        dir_path = os.path.dirname(os.path.realpath(__file__))

        parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))

        sys.path.insert(0, dir_path)

        #print("self.x_eng_cs_data_case_id", self.x_eng_cs_data_case_id)
        os.chdir(self.x_eng_cs_data_case_id)

        # this will hold to log_dir folder
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir
        os.chmod(self.TroubleshootingAssistantDir, 0o777)
        import time
        fileMap = dict()
        from datetime import datetime

        #print("Cur Dir in UncompressAllFilesRecursively :", os.getcwd())
        # Loop is not required, butt in fute it may come as a list of file, hen keeping it, 
        # at this moment, I break the loop, after one iteration.
        for file in os.listdir(self.x_eng_cs_data_case_id):
            if self.bundleName == file:
                self.dirname = Path(file).stem
                try:
                    if platform.system() == "Linux":
                        self.mydir: str = self.TroubleshootingAssistantDir + "//" + self.dirname
                    else:
                        # windows
                        self.mydir: str = self.TroubleshootingAssistantDir + "\\" + self.dirname

                    if not os.path.exists(self.mydir) and '.sql' not in file.strip().lower():
                        os.mkdir(self.mydir)
                    #if not os.path.exists(self.mydir):
                    #    os.mkdir(self.mydir)
                    if ".7z" in file.lower().strip():
                        from pyunpack import Archive
                        Archive(file.strip()).extractall(self.mydir)
                        #with py7zr.SevenZipFile(file.strip(), mode='r') as z:
                        #    z.extractall(self.mydir)
                    #elif ".gz" in file.strip() or ".tgz" in file.strip():
                    elif ".gz" in file.strip() or ".tgz" in file.strip() and '.sql' not in file.strip().lower():
                        patoolib.extract_archive(file.strip(), outdir=self.mydir, interactive=True, verbosity=-1)
                    else:
                        #Archive(file.strip()).extractall(self.mydir, True)
                        try:
                            # file.strip().lower()
                            if '.sql' not in file.strip().lower():
                                z = zipfile.ZipFile(file.strip())
                                z.extractall(self.mydir)
                        except:
                            print("Exception occured while unzipping .zip file ",file,"\nPath= ", os.getcwd(), file)
                            pass
                except FileNotFoundError:
                    print("file not found", file)
                    pass
                except PatoolError as pe:
                    print("Could not unzip from Constructor of (UncompressAllFilesRecursively) :",file, pe)
                    pass
                except KeyError:
                    pass
        os.chdir(self.TroubleshootingAssistantDir)


    # TA_log_dir = //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir//
    def __init__(self, argList=None, TA_log_dir=None, segregated_log=None) -> None:
        from os import listdir
        from os.path import isfile, join
        import os
        import mimetypes
        import zipfile
        import tarfile

        self.caseId = str(argList.caseId)
    
        if argList.bundleName is not None:
            self.unzipBundle(argList,TA_log_dir,segregated_log)
            return


        if platform.system() == "Linux":
            #pathDownloadGlobal = "//x//eng//cs-data//" + self.caseId
            # this will hol to download folder
            self.x_eng_cs_data_case_id = "//x//eng//cs-data//" + self.caseId

            #self.x_eng_cs_data_caseid_TroubleshootingAssistant = pathDownloadGlobal +"TroubleshootingAssistant"
            # this will hol to log_dir folder
            # //x//eng//cs-data//2008696047//TroubleshootingAssistant
            self.TroubleshootingAssistantDir = TA_log_dir
            self.segregated_log = segregated_log

            # if not os.path.exists(self.TroubleshootingAssistantDir):
            #    os.mkdir(self.TroubleshootingAssistantDir)
            # if not os.path.exists(self.TroubleshootingAssistantDir):
            #    os.mkdir(self.TroubleshootingAssistantDir+"//segregated_log")

        else:
            # windows
            basePath = "C:\\x\\eng\cs-data\\"
            pathDownloadGlobal = os.path.join(basePath, self.caseId)
            TA_log_dir = os.path.join(os.getcwd()) + "\\log_dir"

        dir_path = os.path.dirname(os.path.realpath(__file__))

        parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))

        sys.path.insert(0, dir_path)

        #print("self.x_eng_cs_data_case_id", self.x_eng_cs_data_case_id)
        os.chdir(self.x_eng_cs_data_case_id)

        # this will hold to log_dir folder
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir
        os.chmod(self.TroubleshootingAssistantDir, 0o777)
        import time
        fileMap = dict()
        from datetime import datetime

        print("Cur Dir in UncompressAllFilesRecursively :", os.getcwd())
        filedict = dict()
        filedictnum = dict()
        zipFormats = ['.zip', '.7z', '.gzip', '.rar', '.xz', '.tgz', '.bzip2']

        for file in os.listdir(os.getcwd()):
            if isfile(file) and file.lower().strip().endswith(tuple(zipFormats)) and '.sql' not in file.strip().lower():
                try:
                    newFile = (re.split('_|-', file, maxsplit=1)[0])
                    remFile = (re.split('_|-', file, maxsplit=1)[1])
                except IndexError:
                    filedict[file] = os.path.getctime(file)
                    continue
                if (any(num.isdigit() for num in remFile)):
                    filedictnum[newFile] = os.path.getctime(file)
                    if file in filedict.keys():
                        if (filedictnum.get(newFile) < os.path.getctime(file)):
                            filedictnum[newFile] = os.path.getctime(file)
                else:
                    if newFile in filedict.keys():
                        if (filedict.get(newFile) < os.path.getctime(file)):
                            filedict[file] = os.path.getctime(file)
                    if newFile not in filedict.keys():
                        # this will ocuure where a compresed file does not have a digit in the file name
                        # snapmirror_audit.zip
                        filedict[file] = os.path.getctime(file)

        filedict.update(filedictnum)
        print(filedict)

        for file in os.listdir(self.x_eng_cs_data_case_id):
            if os.path.getctime(file) in filedict.values():
                self.dirname = Path(file).stem
                try:
                    if platform.system() == "Linux":
                        self.mydir: str = self.TroubleshootingAssistantDir + "//" + self.dirname
                    else:
                        # windows
                        self.mydir: str = self.TroubleshootingAssistantDir + "\\" + self.dirname

                    if not os.path.exists(self.mydir) and '.sql' not in file.strip().lower():
                        os.mkdir(self.mydir)
                    #if not os.path.exists(self.mydir):
                    #    os.mkdir(self.mydir)
                    if ".7z" in file.strip().lower():
                        from pyunpack import Archive
                        Archive(file.strip()).extractall(self.mydir)
                        #with py7zr.SevenZipFile(file.strip(), mode='r') as z:
                        #    z.extractall(self.mydir)
                    #elif ".gz" in file.strip() or ".tgz" in file.strip():
                    elif ".gz" in file.strip().lower() or ".tgz" in file.strip().lower() and '.sql' not in file.strip().lower():
                        patoolib.extract_archive(file.strip(), outdir=self.mydir, interactive=True, verbosity=-1)
                    else:
                        #Archive(file.strip()).extractall(self.mydir, True)
                        try:
                            if '.sql' not in file.strip().lower():
                                z = zipfile.ZipFile(file.strip())
                                z.extractall(self.mydir)
                        except:
                            print("Exception occured while unzipping .zip file ",file,"\nPath= ", os.getcwd(), file)
                            pass
                except FileNotFoundError:
                    print("file not found", file)
                    pass
                except PatoolError as pe:
                    print("Could not unzip from Constructor of (UncompressAllFilesRecursively) :",file, pe)
                    pass
                except KeyError:
                    pass
        os.chdir(self.TroubleshootingAssistantDir)

    def __del__(self) -> None:
        pass

    def unzipFileRecursively(self, directory: str = None) -> None:
        # //x//eng//cs-data//2008696047//TroubleshootingAssistant//log_dir//
        curPath = os.getcwd() if directory is None else directory
        for current_path, directories, files in os.walk(curPath):
            os.chdir(current_path)

            parent = os.path.dirname(os.getcwd())
            # if not (os.getcwd().__contains__('jboss') or os.getcwd().__contains__('recording')):
            if not os.getcwd().__contains__('recording'):
                for file in os.listdir(os.getcwd()):
                    try:
                        file_name, file_extension = os.path.splitext(file)
                        if not os.path.isdir(file) and file.lower().endswith((".zip", ".gz", ".bzip2", ".7z", ".tgz")):
                            if file.lower().endswith('.7z'):
                                self.extract7Z(file.strip())
                            elif file.lower().endswith((".gz", ".tgz")):
                                self.extractGZ(file, os.getcwd())
                            else:
                                self.extractZip(file, os.getcwd())
                    except:
                        print("Could not Unzip(unzipFileRecursively) ",file, __name__, __class__)
                        pass
        os.chdir(parent)

    def extractZip(self, filename: str, curDir):
        z = zipfile.ZipFile(filename)

        print("extractZip Function called with file name : ", filename)

        dirname = Path(filename).stem

        if not os.path.exists(dirname):
            if platform.system() == "Linux":
                # create new directory
                os.mkdir(curDir + "//" + dirname)
            else:
                os.mkdir(curDir + "\\" + dirname)

            if filename.lower().endswith('.zip'):
                print(" Path specifiled = ", dirname)
                try:
                    z.extractall(dirname)
                except FileNotFoundError:
                    print(" File is not exist ", filename)

                except:
                    pass
                    #print("extractZip raised exception as it could not unzip file :",filename)
                os.chdir(dirname)

                parent = os.path.dirname(os.getcwd())

                self.unzipFileRecursively(os.getcwd())

                os.chdir(parent)

    def extract7Z(self, filename: str) -> None:
        dirname = Path(filename).stem
        if platform.system() == "Linux":
            # create new directory
            os.mkdir(self.TroubleshootingAssistantDir + "//" + dirname)
        else:
            os.mkdir(self.TroubleshootingAssistantDir + "\\" + dirname)
        try:
            # Archive(filename.strip()).extractall(True)
            Archive(filename.strip()).extractall(dirname)
        except:
            #print("extract7Z  raised exception as it could not unzip file (7z) ",filename)
            with py7zr.SevenZipFile(filename.strip(), mode='r') as z:
                z.extractall(dirname)
            pass

    def extractGZ(self, file: str, curdir: str) -> None:
        """
        :param file: file name
        :param curdir:  current dir
        :rtype: None
        """
        # for item in os.listdir(pathDownload): # loop through items in dir
        # get directory name from file
        dirname = Path(file).stem
        ext = file.rsplit(".", 1)[1]
        if platform.system() == "Linux":
            # create new directory
            mydir = curdir + "//" + dirname
        else:
            mydir = curdir + "\\" + dirname + "_" + ext

        os.mkdir(mydir)
        try:
            patoolib.extract_archive(file, outdir=mydir, interactive=True, verbosity=-1)
        except:
            #print("extractGZ  raised exception as it could not unzip file (.gz) ",file)
            pass


if __name__ == '__main__':
    import argparse
    parser = argparse.ArgumentParser()

    # Add an argument
    parser.add_argument('--caseId', type=int, required=True)
    args = parser.parse_args()

    TA_log_dir = "/root/NetAppTroubleshootingAssistant//1974//TroubleshootingAssistant//log_dir//"
    seg_log = "/root/NetAppTroubleshootingAssistant//1974//TroubleshootingAssistant//segregated_log//"
    extractObj = UncompressAllFilesRecursively(args, TA_log_dir, seg_log)
    print(" ================================================start unzip")
    extractObj.unzipFileRecursively()
    print(" ================================================ENDs  unzip")
