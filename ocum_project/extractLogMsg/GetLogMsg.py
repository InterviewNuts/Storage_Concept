import datetime
import os
import platform
import re
import sys

import datefinder
import pandas as pd

from IGetLogMsgBase import GetLogMsgBase
from extractLogMsg.LogFile import LogFileClass

dir_path = os.path.dirname(os.path.realpath(__file__))
parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))
sys.path.insert(0, dir_path)


# This class is responsible to write into file with contents given to it.
# it does remove the duplicate lines from the files. All file related operation it does.


class GetAllLogs(GetLogMsgBase):
    def __init__(self, argList) -> None:
        super(GetAllLogs, self).__init__()
        # print("dir_path ", dir_path)
        # print("parent_dir_path ", parent_dir_path)
        # if argList.caseId and argList.logType and argList.extract:
        self.caseId: str = str(argList.caseId)
        self.logDate: str = argList.logDate
        self.logTime: str = argList.logTime
        self.logTimeDuration: str = str(argList.logDuration)
        self.projectName = argList.prodName
        self.extract = argList.extract
        self.logfile = LogFileClass(self.projectName)
        self._error = False
        self._info = False
        self._warn = False
        self._debug = False
        self._trace = False
        print(argList.logType)
        for log_type in argList.logType:
            if log_type == "error":
                self._error = True
                print(self._error)
            if log_type == "info":
                self._info = True
                print(self._info)
            if log_type == "warn":
                self._warn = True
                print(self._warn)
            if log_type == "debug":
                self._debug = True
                print(self._debug)
            if log_type == "trace":
                self._trace = True
                print(self._trace)

        if platform.system() == "Linux":
            if not os.path.exists("segregated_log/" + self.projectName):
                os.mkdir("segregated_log/" + self.projectName)
        else:
            if not os.path.exists("segregated_log\\" + self.projectName):
                os.mkdir("segregated_log\\" + self.projectName)

        os.chdir(parent_dir_path)

        # print(self.projectName + " COTR parent_dir_path GetLogMsg:  " + os.getcwd())

        if platform.system() == "Linux":
            self.without_dupTraceFN = "segregated_log/" + self.projectName + \
                                      "/" + self.caseId + "_TRACE_WITHOUT_DUP_LOG.log"

            self.without_traceFinalFN = "segregated_log/" + self.projectName + \
                                        "/" + "Final_" + self.caseId + "_TRACE_WITHOUT_DUP_LOG.log"

            self.errorFileName = "segregated_log/" + \
                                 self.projectName + "/" + self.caseId + "_ERROR_LOG.log"

            self.errorJbossFileName = "segregated_log/" + \
                                      self.projectName + "/" + self.caseId + "_JBOSS_ERROR_LOG.log"

            self.errorFileXMLName = "segregated_log/" + \
                                    self.projectName + "/" + self.caseId + "_ERROR_XML_LOG.log"

            self.without_dupWarnFp = "segregated_log/" + self.projectName + \
                                     "/" + self.caseId + "_WARN_WITHOUT_DUP_LOG.log"

            self.without_dupWarnFinalFp = "segregated_log/" + self.projectName + \
                                          "/" + "FINAL_" + self.caseId + "_WARN_WITHOUT_DUP_LOG.log"

            self.without_infoFN = "segregated_log/" + self.projectName + \
                                  "/" + self.caseId + "_INFO_WITHOUT_DUP_LOG.log"

            self.without_infoFinalFN = "segregated_log/" + self.projectName + \
                                       "/" + "FINAL_" + self.caseId + "_INFO_WITHOUT_DUP_LOG.log"

            self.without_debugFN = "segregated_log/" + self.projectName + \
                                   "/" + self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log"

            self.without_debugFinalFN = "segregated_log/" + self.projectName + \
                                        "/" + "FINAL_" + self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log"
            self.without_dupErrFN = "segregated_log/" + self.projectName + \
                                    "/" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"
            self.without_dupErrFinalFN = "segregated_log/" + self.projectName + \
                                         "/" + "FINAL_" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"
            self.without_dupJbossErrFN = "segregated_log/" + self.projectName + \
                                         "/" + self.caseId + "_JBOSS_WITHOUT_DUP_LOG.log"
            self.without_dupJbossErrFinalFN = "segregated_log/" + self.projectName + \
                                              "/" + "FINAL_" + self.caseId + "_JBOSS_WITHOUT_DUP_LOG.log"

            if self._error:
                if self.extract == "yes":
                    print("error file opened ")
                    # print("New Error file name = ", self.errorFileName)
                    self.errorFile = open(self.errorFileName, "w")
                    self.errorXMLFile = open(self.errorFileXMLName, "w")
                    self.errorJbossFile = open(self.errorJbossFileName, "w")
                    self.removeDupFromErrorFile = open(self.without_dupErrFN, "w")
                    self.removeDupFromJbossErrorFile = open(self.without_dupJbossErrFN, "w")
                else:
                    self.errorFile = open(self.errorFileName, "r")
                    self.errorXMLFile = open(self.errorFileXMLName, "w")
                    self.errorJbossFile = open(self.errorJbossFileName, "w")
                    # self.without_dupJbossErrFN = "segregated_log/" + self.projectName + \
                    #     "/" + self.caseId + "_Jboss_WITHOUT_DUP_LOG.log"
                    # self.without_dupJbossErrFinalFN = "segregated_log/" + self.projectName + \
                    #     "/" + "FINAL_" + self.caseId + "_Jboss_WITHOUT_DUP_LOG.log"
                    # self.without_dupErrFN = "segregated_log/" + self.projectName + \
                    #                         "/" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"
                    # self.without_dupErrFinalFN = "segregated_log/" + self.projectName + \
                    #                              "/" + "FINAL_" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"
                    self.removeDupFromJbossErrorFile = open(self.without_dupJbossErrFN, "w")
                    self.removeDupFromErrorFile = open(self.without_dupErrFN, "w")
                    # self.without_dupErrFinalFN = "segregated_log/" + self.projectName + \
                    #                              "/" + "FINAL_" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"

            if self._warn:
                print("WARN file opened ")
                self.warnFile = open(
                    "segregated_log/" + self.projectName + "/" + self.caseId + "_WARN_LOG.log", "w")
                self.removeDupFromWarnFile = open(self.without_dupWarnFp, "w")

            if self._debug:
                print("DEBUG file opened ")
                self.debugFile = open(
                    "segregated_log/" + self.projectName + "/" + self.caseId + "_DEBUG_LOG.log", "w")
                self.removeDupFromDebugFile = open(self.without_debugFN, "w")

            if self._info:
                print("INFO file opened ")
                self.infoFile = open(
                    "segregated_log/" + self.projectName + "/" + self.caseId + "_INFO_LOG.log", "w")
                self.removeDupFromInfoFile = open(self.without_infoFN, "w")

            if self._trace:
                print("TRACE file opened ")
                self.traceFile = open(
                    "segregated_log/" + self.projectName + "/" + self.caseId + "_TRACE_LOG.log", "w")
                self.removeDupFromTraceFile = open(
                    self.without_dupTraceFN, "w")

            ####################################end of Linux####################################
        else:
            ####################################Start of Windows####################################
            self.without_dupTraceFN = "segregated_log\\" + self.projectName + \
                                      "\\" + self.caseId + "_TRACE_WITHOUT_DUP_LOG.log"

            self.without_traceFinalFN = "segregated_log\\" + self.projectName + \
                                        "\\" + "FINAL_" + self.caseId + "_TRACE_WITHOUT_DUP_LOG.log"

            self.errorFileName = "segregated_log\\" + \
                                 self.projectName + "\\" + self.caseId + "_ERROR_LOG.log"
            self.errorJbossFileName = "segregated_log\\" + \
                                      self.projectName + "\\" + self.caseId + "_JBOSS_ERROR_LOG.log"
            self.errorFileXMLName = "segregated_log\\" + \
                                    self.projectName + "\\" + self.caseId + "_ERROR_XML_LOG.log"
            self.without_dupWarnFp = "segregated_log\\" + self.projectName + \
                                     "\\" + self.caseId + "_WARN_WITHOUT_DUP_LOG.log"

            self.without_dupWarnFinalFp = "segregated_log\\" + self.projectName + \
                                          "\\" + "FINAL_" + self.caseId + "_WARN_WITHOUT_DUP_LOG.log"

            self.without_infoFN = "segregated_log\\" + self.projectName + \
                                  "\\" + self.caseId + "_INFO_WITHOUT_DUP_LOG.log"

            self.without_infoFinalFN = "segregated_log\\" + self.projectName + \
                                       "\\" + "FINAL_" + self.caseId + "_INFO_WITHOUT_DUP_LOG.log"

            self.without_debugFN = "segregated_log\\" + self.projectName + \
                                   "\\" + self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log"

            self.without_debugFinalFN = "segregated_log\\" + self.projectName + \
                                        "\\" + "FINAL_" + self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log"
            self.without_dupJbossErrFN = "segregated_log\\" + self.projectName + \
                                         "\\" + self.caseId + "_Jboss_WITHOUT_DUP_LOG.log"
            self.without_dupJbossErrFinalFN = "segregated_log\\" + self.projectName + \
                                              "\\" + "FINAL_" + self.caseId + "_Jboss_WITHOUT_DUP_LOG.log"
            self.without_dupErrFN = "segregated_log\\" + self.projectName + \
                                    "\\" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"
            self.without_dupErrFinalFN = "segregated_log\\" + self.projectName + \
                                         "\\" + "FINAL_" + self.caseId + "_ERROR_WITHOUT_DUP_LOG.log"

            if self._error:
                self.errorFile = open(self.errorFileName, "w")
                self.errorXMLFile = open(self.errorFileXMLName, "w")
                self.errorJbossFile = open(self.errorJbossFileName, "w")
                self.removeDupFromJbossErrorFile = open(
                    self.without_dupJbossErrFN, "w")
                self.removeDupFromErrorFile = open(self.without_dupErrFN, "w")

            if self._warn:
                print("WARN file opened ")
                self.warnFile = open(
                    "segregated_log\\" + self.projectName + "\\" + self.caseId + "_WARN_LOG.log", "w")
                self.removeDupFromWarnFile = open(self.without_dupWarnFp, "w")

            if self._debug:
                print("DEBUG file opened ")
                self.debugFile = open(
                    "segregated_log\\" + self.projectName + "\\" + self.caseId + "_DEBUG_LOG.log", "w")
                self.removeDupFromDebugFile = open(self.without_debugFN, "w")

            if self._info:
                print("INFO file opened ")
                self.infoFile = open(
                    "segregated_log\\" + self.projectName + "\\" + self.caseId + "_INFO_LOG.log", "w")
                self.removeDupFromInfoFile = open(self.without_infoFN, "w")

            if self._trace:
                print("TRACE file opened ")
                self.traceFile = open(
                    "segregated_log\\" + self.projectName + "\\" + self.caseId + "_TRACE_LOG.log", "w")
                self.removeDupFromTraceFile = open(
                    self.without_dupTraceFN, "w")

    # except IOError:
    #     print("could not open file from the Constructor of OCUMLog: ", __name__, __class__)

    @property
    def error(self):
        return self._error

    @property
    def info(self):
        return self._info

    @property
    def debug(self):
        return self._debug

    @property
    def warn(self):
        return self._warn

    @property
    def trace(self):
        return self._trace

    @error.setter
    def error(self, e):
        self._error = e

    @info.setter
    def info(self, s):
        self._info = s

    @debug.setter
    def debug(self, d):
        self._debug = d

    @warn.setter
    def warn(self, w):
        self._warn = w

    @trace.setter
    def trace(self, t):
        self._trace = t

    def __del__(self):
        super(GetAllLogs, self).__del__()
        if self._error:
            self.errorFile.close()
            self.errorJbossFile.close()
            self.errorXMLFile.close()

        if self._info:
            self.infoFile.close()

        if self._debug:
            self.debugFile.close()

        if self._warn:
            self.warnFile.close()

        if self._trace:
            self.traceFile.close()

    def cleanUp(self):
        if not self._error:
            os.remove(self.errorFileName)

        if not self._info:
            os.remove(self.without_infoFN)

        if not self._debug:
            os.remove(self.without_debugFN)

        if not self._warn:
            os.remove(self.without_dupWarnFp)

        if not self._trace:
            os.remove(self.without_dupTraceFN)

    def getUserGivenDurationErrorLog(self):
        os.chdir(parent_dir_path)
        global userGivenDurationLogFileFP

        withoutDupErrLogFp = open(self.errorFileName, "r").readlines()

        print(" Dup file name = ", self.errorFileName)

        durationLogErrFileName = 'USERGIVEN_' + \
                                 self.logTimeDuration + '_MINUTES_ERROR_LOG.log'
        if platform.system() == "Linux":
            userGivenDurationLogFileFP = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + "_" + durationLogErrFileName, "w")
        else:
            userGivenDurationLogFileFP = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + "_" + durationLogErrFileName, "w")

        durationOfLogInMinutes: int = int(self.logTimeDuration)

        deltaTime = datetime.timedelta(minutes=durationOfLogInMinutes)
        # userGivenDateNTime = logDate + " " + logTime

        userGivenDateNTime = pd.Timestamp(self.logDate + " " + self.logTime)

        startDate = pd.Timestamp(userGivenDateNTime - deltaTime).tz_localize(None)

        endDate = pd.Timestamp(userGivenDateNTime + deltaTime).tz_localize(None)

        print("start Date = ", startDate)
        print("End Date = ", endDate)

        from Util import Util

        for line in withoutDupErrLogFp:
            dateFinderFailed = False

            matchFound = False

            matches = []
            try:
                matches = list(datefinder.find_dates(line.split(':==:', 1)[1]))
            except:
                dateFinderFailed = True
            else:
                dateFinderFailed = False

            if dateFinderFailed:
                dt = Util.is_date_valid_NLP(line.split(':==:', 1)[1])

                validdate1 = Util.is_date_valid(dt)
                if validdate1:
                    if ((pd.Timestamp(dt).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dt).tz_localize(None) <= endDate)):
                        # print(dt)
                        userGivenDurationLogFileFP.write("%s" % (line))
            elif len(matches) > 0:
                dateFound = matches[0]

                validdate = Util.is_date_valid(dateFound)
                if validdate:
                    if ((pd.Timestamp(dateFound).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dateFound).tz_localize(None) <= endDate)):
                        # print(dateFound)
                        userGivenDurationLogFileFP.write("%s" % (line))

        userGivenDurationLogFileFP.close()

    def getUserGivenDurationWarnLog(self):
        os.chdir(parent_dir_path)
        global userGivenDurationWarnLogFileFP
        if platform.system() == "Linux":
            withoutDupWarnLogFp = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + "_WARN_LOG.log", "r")
        else:
            withoutDupWarnLogFp = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + "_WARN_LOG.log", "r")

        durationLogWarnFileName = 'USERGIVEN_' + \
                                  self.logTimeDuration + '_MINUTES_WARN_LOG.log'

        if platform.system() == "Linux":
            userGivenDurationWarnLogFileFP = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + "_" + durationLogWarnFileName, "w")
        else:
            userGivenDurationWarnLogFileFP = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + "_" + durationLogWarnFileName, "w")

        durationOfLogInMinutes: int = int(self.logTimeDuration)

        deltaTime = datetime.timedelta(seconds=durationOfLogInMinutes)

        userGivenDateNTime = pd.Timestamp(self.logDate + " " + self.logTime)

        startDate = pd.Timestamp(
            userGivenDateNTime - deltaTime).tz_localize(None)

        endDate = pd.Timestamp(userGivenDateNTime +
                               deltaTime).tz_localize(None)

        print("start Date = ", startDate)
        print("End Date = ", endDate)

        from Util import Util
        for line in withoutDupWarnLogFp:
            split_line = line.split(':==:', 1)
            line_second_part = split_line[1]
            dateFinderFailed = False
            matchFound = False
            matches = []
            try:
                matches = list(datefinder.find_dates(line.split(':==:', 1)[1]))
            except:
                dateFinderFailed = True
            else:
                dateFinderFailed = False

            if dateFinderFailed:
                dt = Util.is_date_valid_NLP(line.split(':==:', 1)[1])
                validdate1 = Util.is_date_valid(dt)
                if validdate1:
                    if ((pd.Timestamp(dt).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dt).tz_localize(None) <= endDate)):
                        # print(dt)
                        userGivenDurationWarnLogFileFP.write("%s" % line)
            elif len(matches) > 0:
                dateFound = matches[0]

                validdate = Util.is_date_valid(dateFound)
                if validdate:
                    if ((pd.Timestamp(dateFound).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dateFound).tz_localize(None) <= endDate)):
                        # print(dateFound)
                        userGivenDurationWarnLogFileFP.write("%s" % line)

        userGivenDurationWarnLogFileFP.close()

    def getUserGivenDurationInfoLog(self):
        os.chdir(parent_dir_path)
        global userGivenDurationInfoLogFileFP
        # withoutDupInfoLogFp = open("segregated_log\\"+self.projectName +"\\NEW_"+self.casenumber + "_INFO_WITHOUT_DUP_LOG.log","r").readlines()

        withoutDupInfoLogFp = open(self.without_infoFinalFN, "r").readlines()

        durationLogInfoFileName = 'USERGIVEN_' + \
                                  self.logTimeDuration + '_MINUTES_INFO_LOG.log'

        if platform.system() == "Linux":
            userGivenDurationInfoLogFileFP = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + durationLogInfoFileName, "w")
        else:
            userGivenDurationInfoLogFileFP = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + durationLogInfoFileName, "w")

        durationOfLogInMinutes: int = int(self.logTimeDuration)

        deltaTime = datetime.timedelta(minutes=durationOfLogInMinutes)

        userGivenDateNTime = pd.Timestamp(self.logDate + " " + self.logTime)

        startDate = pd.Timestamp(
            userGivenDateNTime - deltaTime).tz_localize(None)

        endDate = pd.Timestamp(userGivenDateNTime +
                               deltaTime).tz_localize(None)

        print("start Date = ", startDate)
        print("End Date = ", endDate)

        from Util import Util

        for line in withoutDupInfoLogFp:
            split_line = line.split(':==:', 1)
            line_second_part = split_line[1]
            dateFinderFailed = False
            matchFound = False
            matches = []
            try:
                matches = list(datefinder.find_dates(line.split(':==:', 1)[1]))
            except:
                dateFinderFailed = True
            else:
                dateFinderFailed = False

            if dateFinderFailed:
                dt = Util.is_date_valid_NLP(line.split(':==:', 1)[1])
                validdate1 = Util.is_date_valid(dt)
                if validdate1:
                    if ((pd.Timestamp(dt).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dt).tz_localize(None) <= endDate)):
                        # print(dt)
                        userGivenDurationInfoLogFileFP.write("%s" % line)
            elif len(matches) > 0:
                dateFound = matches[0]

                validdate = Util.is_date_valid(dateFound)
                if validdate:
                    if ((pd.Timestamp(dateFound).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dateFound).tz_localize(None) <= endDate)):
                        # print(dateFound)
                        userGivenDurationInfoLogFileFP.write("%s" % line)

        userGivenDurationInfoLogFileFP.close()

    def getUserGivenDurationTraceLog(self):

        os.chdir(parent_dir_path)
        if platform.system() == "Linux":
            withoutDupTraceLogFp = open(
                self.without_traceFinalFN, "r").readlines()
        else:
            withoutDupTraceLogFp = open(
                self.without_traceFinalFN, "r").readlines()

        print("without_traceFinalFN Reading  :", self.without_traceFinalFN)

        durationLogTraceFileName = 'USERGIVEN_' + \
                                   self.logTimeDuration + '_MINUTES_TRACE_LOG.log'

        if platform.system() == "Linux":
            userGivenDurationTraceLogFileFP = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + durationLogTraceFileName, "w")
        else:
            userGivenDurationTraceLogFileFP = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + durationLogTraceFileName, "w")

        durationOfLogInMinutes: int = int(self.logTimeDuration)

        deltaTime = datetime.timedelta(minutes=durationOfLogInMinutes)

        userGivenDateNTime = pd.Timestamp(self.logDate + " " + self.logTime)

        startDate = pd.Timestamp(
            userGivenDateNTime - deltaTime).tz_localize(None)

        endDate = pd.Timestamp(userGivenDateNTime +
                               deltaTime).tz_localize(None)

        print("start Date = ", startDate)
        print("End Date = ", endDate)

        from Util import Util
        for line in withoutDupTraceLogFp:
            split_line = line.split(':==:', 1)
            line_second_part = split_line[1]
            dateFinderFailed = False
            matchFound = False
            matches = []
            try:
                matches = list(datefinder.find_dates(line.split(':==:', 1)[1]))
            except:
                dateFinderFailed = True
            else:
                dateFinderFailed = False

            if dateFinderFailed:
                dt = Util.is_date_valid_NLP(line.split(':==:', 1)[1])
                validdate1 = Util.is_date_valid(dt)
                if validdate1:
                    if (pd.Timestamp(dt).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dt).tz_localize(None) <= endDate):
                        userGivenDurationTraceLogFileFP.write("%s" % line)
            elif len(matches) > 0:
                dateFound = matches[0]

                validdate = Util.is_date_valid(dateFound)
                if validdate:
                    if ((pd.Timestamp(dateFound).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dateFound).tz_localize(None) <= endDate)):
                        userGivenDurationTraceLogFileFP.write("%s" % line)

        userGivenDurationTraceLogFileFP.close()

    def getUserGivenDurationDebugLog(self):
        os.chdir(parent_dir_path)
        global userGivenDurationDebugLogFileFP
        if platform.system() == "Linux":
            withoutDupDebugLogFp = open("segregated_log/" + self.projectName + "/" + "FINAL_" +
                                        self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log", "r").readlines()
        else:
            withoutDupDebugLogFp = open("segregated_log\\" + self.projectName + "\\FINAL_" +
                                        self.caseId + "_DEBUG_WITHOUT_DUP_LOG.log", "r").readlines()

        durationLogDebugFileName = '_USERGIVEN_' + \
                                   self.logTimeDuration + '_MINUTES_DEBUG_LOG.log'
        if platform.system() == "Linux":
            userGivenDurationDebugLogFileFP = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + durationLogDebugFileName, "w")
        else:
            userGivenDurationDebugLogFileFP = open(
                "segregated_log\\" + self.projectName + "\\" + self.caseId + durationLogDebugFileName, "w")

        durationOfLogInMinutes: int = int(self.logTimeDuration)

        deltaTime = datetime.timedelta(minutes=durationOfLogInMinutes)

        userGivenDateNTime = pd.Timestamp(self.logDate + " " + self.logTime)

        startDate = pd.Timestamp(
            userGivenDateNTime - deltaTime).tz_localize(None)

        endDate = pd.Timestamp(userGivenDateNTime +
                               deltaTime).tz_localize(None)

        print("start Date = ", startDate)

        print("End Date = ", endDate)

        from Util import Util
        for line in withoutDupDebugLogFp:
            split_line = line.split(':==:', 1)
            line_second_part = split_line[1]
            dateFinderFailed = False
            matchFound = False
            matches = []
            try:
                matches = list(datefinder.find_dates(line.split(':==:', 1)[1]))
            except:
                dateFinderFailed = True
            else:
                dateFinderFailed = False

            if dateFinderFailed:
                dt = Util.is_date_valid_NLP(line.split(':==:', 1)[1])
                validdate1 = Util.is_date_valid(dt)
                if validdate1:
                    if (pd.Timestamp(dt).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dt).tz_localize(None) <= endDate):
                        userGivenDurationDebugLogFileFP.write("%s" % line)
            elif len(matches) > 0:
                dateFound = matches[0]

                validdate = Util.is_date_valid(dateFound)
                if validdate:
                    if ((pd.Timestamp(dateFound).tz_localize(None) >= startDate) and (
                            pd.Timestamp(dateFound).tz_localize(None) <= endDate)):
                        userGivenDurationDebugLogFileFP.write("%s" % line)

        userGivenDurationDebugLogFileFP.close()

    def getUserGivenDurationForAllLog(self, *args: str) -> None:
        try:
            print("inside all log errors")
            self.errorWarnFile = open(
                "segregated_log/" + self.projectName + "/" + self.caseId + "allErrorsUserGivenTime.log", "a")
            for filename in args:
                with open(filename, "r", encoding="utf-8-sig", errors='ignore') as fn:
                    for line in fn.readlines():
                        self.errorWarnFile.write(line)

            self.errorWarnFile.close()

        except FileNotFoundError:
            print("file not found in getUserGivenDurationForAllLog func")

    def getUserGivenDurationLog(self):
        if self._error:
            self.getUserGivenDurationErrorLog()
            self.getUserGivenDurationForAllLog(
                userGivenDurationLogFileFP.name)

        if self._warn:
            self.getUserGivenDurationWarnLog()
            self.getUserGivenDurationForAllLog(
                userGivenDurationWarnLogFileFP.name)

        if self._info:
            self.getUserGivenDurationInfoLog()
            self.getUserGivenDurationForAllLog(
                userGivenDurationInfoLogFileFP.name)

        if self._trace:
            self.getUserGivenDurationTraceLog()

        if self._debug:
            self.getUserGivenDurationDebugLog()
            self.getUserGivenDurationForAllLog(
                userGivenDurationDebugLogFileFP.name)

    def removeDuplicateFromFils(self):
        os.chdir(parent_dir_path)

        dictionary = dict()

        setToRemoveDup = set()

        if self._error:
            dictionary.clear()

            setToRemoveDup.clear()

            for line in open(self.errorJbossFileName):
                try:
                    if 'ERROR' in line:
                        split_line = line.split('ERROR', 1)

                        key_time = split_line[0] + 'ERROR'

                        key_value = split_line[1]

                    if 'WARN' in line:
                        split_line = line.split('WARN', 1)

                        key_time = split_line[0] + 'WARN'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = str(searchList[1:4])

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass

                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                # try:
                self.removeDupFromJbossErrorFile.write('%s%s' % (value, key))
                # except:
                #     pass

            self.removeDupFromJbossErrorFile.close()

            dictionary.clear()

            setToRemoveDup.clear()

            new_dup_jboss_error = open(self.without_dupJbossErrFinalFN, 'w')

            # 2nd layer of filteration
            for line in open(self.without_dupJbossErrFN):
                try:
                    if 'ERROR' in line:
                        split_line = line.split('ERROR', 1)

                        key_time = split_line[0] + 'ERROR'

                        key_value = split_line[1]
                    if 'WARN' in line:
                        split_line = line.split('WARN', 1)

                        key_time = split_line[0] + 'WARN'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = ""

                    try:
                        s = str(searchList[0]) + " " + str(searchList[2])
                    except IndexError:
                        pass

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass
                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                try:
                    new_dup_jboss_error.write(
                        '%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromJbossErrorFile.close()

            dictionary.clear()

            setToRemoveDup.clear()

            self.errorFile.close()

            for line in open(self.errorFileName):
                try:
                    if 'ERROR' in line:
                        split_line = line.split('ERROR', 1)

                        key_time = split_line[0] + 'ERROR'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = str(searchList[1:4])

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass

                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                try:
                    self.removeDupFromErrorFile.write('%s%s' % (value, key))
                except:
                    print("Could not write into file")

            self.removeDupFromErrorFile.close()

            errFileTemp = open(self.without_dupErrFN, "r").readlines()

            new_dup_error = open(self.without_dupErrFinalFN, 'w')

            dictionary.clear()

            setToRemoveDup.clear()

            # 2nd layer of filteration
            for line in open(self.without_dupErrFN):
                try:
                    if 'ERROR' in line:
                        split_line = line.split('ERROR', 1)

                        key_time = split_line[0] + 'ERROR'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = ""

                    try:
                        s = str(searchList[0]) + " " + str(searchList[2])
                    except IndexError:
                        pass

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass
                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                try:
                    w1 = value.split(":==:")[0].strip()
                    w = value.split(":==:")[1].strip()

                    if w.split(maxsplit=1)[0].strip() == "ERROR":
                        w = w.replace("ERROR", "")

                    if len(w.strip()) > 0:
                        new_dup_error.write('%s:==: %s %s' % (w1, w, key))
                        # new_dup_error.write("\n")
                    else:
                        new_dup_error.write('%s :==: %s' % (w1, key))
                        # new_dup_error.write("\n")
                except:
                    pass

            self.removeDupFromErrorFile.close()

        if self._warn:
            dictionary.clear()

            setToRemoveDup.clear()

            self.warnFile.close()

            if platform.system() == "Linux":
                warnFile = open("segregated_log/" + self.projectName +
                                "/" + self.caseId + "_WARN_LOG.log", "r").readlines()
            else:
                warnFile = open("segregated_log\\" + self.projectName +
                                "\\" + self.caseId + "_WARN_LOG.log", "r").readlines()
            for line in warnFile:
                try:
                    if 'WARN' in line:
                        split_line = line.split('WARN', 1)

                        key_time = split_line[0] + 'WARN'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = str(searchList[1:4])

                    listToStr = ' '.join(map(str, s))

                    if listToStr not in setToRemoveDup:
                        setToRemoveDup.add(listToStr)
                        dictionary[key_value] = key_time
                except MemoryError:
                    print("Memory Error")
                    pass

            for key, value in dictionary.items():
                try:
                    self.removeDupFromWarnFile.write('%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromWarnFile.close()

            warnFileTemp = open(self.without_dupWarnFp, "r").readlines()

            new_dup_warn = open(self.without_dupWarnFinalFp, 'w')

            dictionary.clear()

            setToRemoveDup.clear()

            # 2nd layer of filtration
            for line in warnFileTemp:
                try:
                    if 'WARN' in line:
                        split_line = line.split('WARN', 1)

                        key_time = split_line[0] + 'WARN'

                        key_value = split_line[1]

                    searchList = key_value.split()

                    s = ""

                    try:
                        s = str(searchList[0]) + " " + str(searchList[2])
                    except IndexError:
                        pass

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass
                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                try:
                    new_dup_warn.write('%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromWarnFile.close()

        if self.debug:
            dictionary.clear()

            setToRemoveDup.clear()

            self.debugFile.close()

            if platform.system() == "Linux":
                debugFile = open("segregated_log/" + self.projectName + "/" +
                                 self.caseId + "_DEBUG_LOG.log", "r").readlines()
            else:
                debugFile = open("segregated_log\\" + self.projectName + "\\" +
                                 self.caseId + "_DEBUG_LOG.log", "r").readlines()

            for line in debugFile:
                try:
                    split_line = line.split('DEBUG', 1)

                    key_time = split_line[0] + 'DEBUG'

                    key_value = split_line[1]

                    searchList = key_value.split()

                    s = str(searchList[1:4])

                    listToStr = ' '.join(map(str, s))

                    if listToStr not in setToRemoveDup:
                        setToRemoveDup.add(listToStr)
                        dictionary[key_value] = key_time
                except MemoryError:
                    print("Memory Error")
                    pass
            for key, value in dictionary.items():
                try:
                    self.removeDupFromDebugFile.write('%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromDebugFile.close()

            # debugFileTemp = open(self.without_debugFN, "r").readlines()

            new_dup_debug = open(self.without_debugFinalFN, 'w')

            dictionary.clear()

            setToRemoveDup.clear()

            # 2nd layer of filtration
            for line in open(self.without_debugFN):
                try:
                    split_line = line.split('DEBUG', 1)

                    key_time = split_line[0] + 'DEBUG'

                    key_value = split_line[1]

                    searchList = key_value.split()

                    s = ""

                    try:
                        s = str(searchList[0]) + " " + str(searchList[2])
                    except IndexError:
                        pass

                    listToStr = ' '.join(map(str, s))
                    if listToStr not in setToRemoveDup:
                        setToRemoveDup.add(listToStr)

                        dictionary[key_value] = key_time
                except MemoryError:
                    print("Memory Error")
                    pass

            for key, value in dictionary.items():
                try:
                    new_dup_debug.write('%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromDebugFile.close()

        if self.info:

            dictionary.clear()

            setToRemoveDup.clear()

            self.infoFile.close()

            if platform.system() == "Linux":
                infoFile = open("segregated_log/" + self.projectName +
                                "/" + self.caseId + "_INFO_LOG.log", "r").readlines()
            else:
                infoFile = open("segregated_log\\" + self.projectName +
                                "\\" + self.caseId + "_INFO_LOG.log", "r").readlines()

                for line in infoFile:
                    try:
                        split_line = line.split('INFO', 1)

                        key_time = split_line[0] + 'INFO'

                        key_value = split_line[1]

                        searchList = key_value.split(" ", 5)

                        s = str(searchList[1:4])
                        listToStr = ' '.join(map(str, s))
                    except MemoryError:
                        pass
                    if listToStr not in setToRemoveDup:
                        setToRemoveDup.add(listToStr)
                        dictionary[key_value] = key_time

                for key, value in dictionary.items():
                    try:
                        self.removeDupFromInfoFile.write('%s%s' % (value, key))
                    except:
                        pass

            self.removeDupFromInfoFile.close()
            # self.without_infoFN = "segregated_log/" + self.projectName + "/" + self.casenumber + "_INFO_WITHOUT_DUP_LOG.log"
            # It reads the first layer removal file
            # infoFileTemp = open(self.without_infoFN, "r").readlines()

            new_dup_info = open(self.without_infoFinalFN, 'w')

            dictionary.clear()

            setToRemoveDup.clear()

            # 2nd layer of filtration
            for line in open(self.without_infoFN):
                try:
                    split_line = line.split('INFO', 1)

                    key_time = split_line[0] + 'INFO'

                    key_value = split_line[1]

                    searchList = key_value.split()

                    s = ""

                    try:
                        s = str(searchList[0]) + " " + str(searchList[2])
                    except IndexError:
                        pass

                    listToStr = ' '.join(map(str, s))
                except MemoryError:
                    print("Memory Error")
                    pass
                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                try:
                    new_dup_info.write('%s%s' % (value, key))
                except:
                    pass

            self.removeDupFromInfoFile.close()

        if self.trace:

            dictionary.clear()

            setToRemoveDup.clear()

            if self.extract == "yes":
                self.traceFile.close()

            if platform.system() == "Linux":
                traceFile = open("segregated_log\\" + self.projectName + "\\" +
                                 self.caseId + "_TRACE_LOG.log", "r").readlines()
            else:
                traceFile = open("segregated_log\\" + self.projectName + "\\" +
                                 self.caseId + "_TRACE_LOG.log", "r").readlines()

            for line in traceFile:
                split_line = line.split('TRACE', 1)

                key_time = split_line[0] + 'TRACE'

                key_value = split_line[1]

                searchList = key_value.split()

                s = str(searchList[1:4])

                listToStr = ' '.join(map(str, s))

                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)
                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                self.removeDupFromTraceFile.write('%s%s' % (value, key))

            self.removeDupFromTraceFile.close()

            traceFileTemp = open(self.without_dupTraceFN, "r").readlines()

            new_dup_trace = open(self.without_traceFinalFN, 'w')

            dictionary.clear()

            setToRemoveDup.clear()

            # 2nd layer of filtration
            for line in traceFileTemp:
                split_line = line.split('TRACE', 1)

                key_time = split_line[0] + 'TRACE'

                key_value = split_line[1]

                searchList = key_value.split()

                s = ""

                try:
                    s = str(searchList[0]) + " " + str(searchList[2])
                except IndexError:
                    pass

                listToStr = ' '.join(map(str, s))
                if listToStr not in setToRemoveDup:
                    setToRemoveDup.add(listToStr)

                    dictionary[key_value] = key_time

            for key, value in dictionary.items():
                new_dup_trace.write('%s%s' % (value, key))

            self.removeDupFromTraceFile.close()

    def getErrorXML(self, filename: str, filepath: str) -> None:
        with open(filename, "r", encoding="utf-8-sig", errors='ignore') as fn:
            line = fn.readline()
            while line:
                if re.search(r"<*[^<]+(?i)error\b[^>]*>", line) and '</error>' not in line:
                    self.errorXMLFile.write(line)
                line = fn.readline()

    def getErrorLog(self, filename: str, filepath: str) -> None:
        try:
            print(filename, filepath)
            err = "error"
            err6 = ":error"
            err7 = "error:"
            err1 = "error."
            err2 = "error)"
            err3 = "error]"
            err4 = "[Error Code: -1]"
            err5 = "failed with"
            err8 = "<volume-errors>"
            err9 = "<last-op-error>"
            err10 = ".VPServerException:"
            err11 = "java.net.ConnectException:"
            err12 = "javax.xml.ws.WebServiceException:"

            with open(filename, "r", encoding="utf-8-sig", errors='ignore') as fn:
                line = fn.readline()
                while line:
                    if platform.system() == "Linux":
                        text_line = filepath + "/"
                    else:
                        text_line = filepath + "\\"
                    if 'ERROR' in line:
                        text_line += filename.title()

                        text_line += " :==: " + line

                        self.errorFile.write(text_line)
                    else:
                        found = (' ' + err.casefold() +
                                 ' ') in (' ' + line.lower() + ' ')
                        found1 = (' ' + err1) in (' ' + line.lower() + ' ')
                        found2 = (' ' + err2) in (' ' + line.lower() + ' ')
                        found3 = (' ' + err3) in (' ' + line.lower() + ' ')
                        found4 = (' ' + err4) in (' ' + line.lower() + ' ')
                        found5 = (' ' + err5) in (' ' + line.lower() + ' ')
                        found6 = (err6) in (' ' + line.lower() + ' ')
                        found7 = (err7.casefold()) in (
                                ' ' + line.casefold() + ' ')
                        found8 = (err8.casefold()) in (
                                ' ' + line.casefold() + ' ')
                        found9 = (err9.casefold()) in (
                                ' ' + line.casefold() + ' ')
                        found10 = (err10.casefold()) in (
                                ' ' + line.casefold() + ' ')
                        found11 = (err11.casefold()) in (
                                ' ' + line.casefold() + ' ')
                        found12 = (err12.casefold()) in (
                                ' ' + line.casefold() + ' ')

                        foundERR = line.find("ERROR") > 0
                        if (found or found1 or found2 or found3 or found4 or found5 or found6 or found7 or
                            found8 or found9 or found10 or found11 or found12) and (not foundERR):
                            text_line += filename.title()
                            text_line += " :==: " + " ERROR " + line
                            self.errorFile.write(text_line)

                    line = fn.readline()
        except FileNotFoundError:
            print("file not found", filename)

    def getInfoLog(self, filename: str, filepath: str) -> None:
        with open(filename, "r", encoding='utf-8-sig',
                  errors='ignore') as fn:
            line = fn.readline()
            while line:
                if 'INFO' in line:
                    if platform.system() == "Linux":
                        text_line = filepath + "/"
                    else:
                        text_line = filepath + "\\"

                    text_line += filename.title()

                    text_line += " :==: " + line

                    self.infoFile.write(text_line)

                line = fn.readline()

    def getWarnLog(self, filename: str, filepath: str) -> None:
        # print(os.listdir())
        with open(filename, "r", encoding='utf-8-sig',
                  errors='ignore') as fn:
            line = fn.readline()
            while line:
                if 'WARN' in line:
                    if platform.system() == "Linux":
                        text_line = filepath + "/"
                    else:
                        text_line = filepath + "\\"

                    text_line += filename.title()

                    text_line += "  :==: " + line

                    self.warnFile.write(text_line)

                line = fn.readline()

    def getDebugLog(self, filename: str, filepath: str) -> None:
        with open(filename, "r", encoding='utf-8-sig',
                  errors='ignore') as fn:
            line = fn.readline()
            while line:
                if 'DEBUG' in line:
                    text_line = filepath + "\\"

                    text_line += filename.title()

                    text_line += "  :==: " + line

                    self.debugFile.write(text_line)

                line = fn.readline()

    def getJbossErrorLog(self, filename: str, filepath: str) -> None:
        try:
            with open(filename, "r", encoding="utf-8-sig", errors='ignore') as fn:
                line = fn.readline()
                while line:
                    if (('ERROR' in line) or ('WARN' in line)):
                        if platform.system() == "Linux":
                            text_line = filepath + "/"
                        else:
                            text_line = filepath + "\\"

                        text_line += filename.title()

                        text_line += " :==: " + line

                        self.errorJbossFile.write(text_line)
                    line = fn.readline()
        except FileNotFoundError:
            print("file not found", filename)

    def getTraceLog(self, filename: str, filepath: str) -> None:
        with open(filename, "r") as fn:
            line = fn.readline()
            while line:
                if line.find("TRACE") > 0:
                    text_line = filepath + "\\"

                    text_line += filename.title()

                    text_line += " :==: " + line

                    self.traceFile.write(text_line)

                line = fn.readline()

# if __name__ == "__main__":
#     log = OCUMLog()
#     log.getLogError(file_name)
#     log.getLogDebug()
#     log.getLogInfo()
