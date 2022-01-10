import os
import sys
import zipfile
from pathlib import Path
import platform
import patoolib
from pyunpack import Archive, PatoolError
import py7zr

curDir = os.getcwd()

# This file is responsibele to un compress all the file recursively.


class UncompressAllFilesRecursively:

    def __init__(self, argList, sourcedir: str = None, targetdir: str = None) -> None:

        self.caseId = str(argList.caseId)

        if platform.system() == "Linux":
            pathDownloadGlobal = "//x//eng//cs-data//" + self.caseId
            logGlobal = os.path.join(os.getcwd()) + "//log_dir"
        else:
            basePath = "C:\\x\\eng\cs-data\\"
            pathDownloadGlobal = os.path.join(basePath, self.caseId)
            logGlobal = os.path.join(os.getcwd()) + "\\log_dir"

        dir_path = os.path.dirname(os.path.realpath(__file__))

        parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))

        sys.path.insert(0, dir_path)

        # this will hol to download folder
        self.pathDownload = pathDownloadGlobal if sourcedir is None else sourcedir

        print("self.pathDownload", self.pathDownload)
        os.chdir(self.pathDownload)

        # this will hol to log_dir folder
        self.log = logGlobal if targetdir is None else targetdir

        os.chmod(self.log, 0o777)

        print("Cur Dir in UncompressAllFilesRecursively :", os.getcwd())
        for file in os.listdir(self.pathDownload):

            self.dirname = Path(file).stem
            if platform.system() == "Linux":
                self.mydir: str = self.log + "//" + self.dirname

            else:
                self.mydir: str = self.log + "\\" + self.dirname

            if not os.path.exists(self.mydir):
                os.mkdir(self.mydir)
            try:
                if platform.system() == "Linux" and ".7z" in file.strip():
                    with py7zr.SevenZipFile(file.strip(), mode='r') as z:
                        z.extractall(self.mydir)
                else:
                    Archive(file.strip()).extractall(self.mydir, True)
            except FileNotFoundError:
                print("file not found", file)
                pass
            except PatoolError:
                print("Could not unzip from Constructor of (UncompressAllFilesRecursively) :",
                      file, __name__, __class__)
                pass

        os.chdir(self.log)

    def __del__(self) -> None:
        pass

    def unzipFileRecursively(self, directory: str = None) -> None:
        curPath = os.getcwd() if directory is None else directory
        for current_path, directories, files in os.walk(curPath):
            os.chdir(current_path)

            parent = os.path.dirname(os.getcwd())
            if not (os.getcwd().__contains__('jboss') or os.getcwd().__contains__('recording')):
                for file in os.listdir(os.getcwd()):
                    # try:
                    # file_name, file_extension = os.path.splitext(file)
                    if not os.path.isdir(file) and file.endswith((".zip", ".gz", ".bzip2", ".7z", ".tgz")):
                        if file.endswith('.7z'):
                            self.extract7Z(file.strip())
                        elif file.endswith((".gz", ".tgz")):
                            self.extractGZ(file, os.getcwd())
                        else:
                            self.extractZip(file, os.getcwd())
                    # except:
                    #     print("Could not Unzip(unzipFileRecursively) ",
                    #           file, __name__, __class__)
                    #     pass
        os.chdir(parent)

    def extractZip(self, filename: str, curDir):
        z = zipfile.ZipFile(filename)

        print(" extractZip ----File name = ", filename)

        dirname = Path(filename).stem

        if not os.path.exists(dirname):
            if platform.system() == "Linux":
                # create new directory
                os.mkdir(curDir + "//" + dirname)
            else:
                os.mkdir(curDir + "\\" + dirname)

            if filename.endswith('.zip'):
                print(" Path specifiled = ", dirname)
                try:
                    z.extractall(dirname)
                except FileNotFoundError:
                    print(" File is not exist ", filename)

                os.chdir(dirname)

                parent = os.path.dirname(os.getcwd())

                self.unzipFileRecursively(os.getcwd())

                os.chdir(parent)

    def extract7Z(self, filename: str) -> None:
        dirname = Path(filename).stem
        if platform.system() == "Linux":
            # create new directory
            os.mkdir(self.log + "//" + dirname)
        else:
            os.mkdir(self.log + "\\" + dirname)
        try:
            Archive(filename.strip()).extractall(True)
        except:
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
            patoolib.extract_archive(
                file, outdir=mydir, interactive=True, verbosity=-1)
        except:
            pass

# if __name__ == '__main__':
#     extractObj = UncompressAllFilesRecursively()
#
#     extractObj.unzipFileRecursively()
