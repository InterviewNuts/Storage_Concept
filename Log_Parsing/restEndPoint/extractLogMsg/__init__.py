import os
import sys
# sys.path.insert(0, os.path.abspath(os.path.join(os.path.dirname(__file__), '..')))
dir_path = os.path.dirname(os.path.realpath(__file__))
parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))
sys.path.insert(0, dir_path)


from IGetLogMsgBase import GetLogMsgBase
from  GetLogMsg import GetAllLogs
from GetLogsRecursively import ExtractAllLogs
