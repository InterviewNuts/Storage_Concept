from abc import ABC,abstractmethod
import os, sys
dir_path = os.path.dirname(os.path.realpath(__file__))
parent_dir_path = os.path.abspath(os.path.join(dir_path, os.pardir))
sys.path.insert(0, dir_path)

class GetLogMsgBase(ABC):
    def __init__(self)->None:
        pass

    def __del__(self)->None:
        pass

    @abstractmethod
    def getErrorLog(self,filename:str,filepath:str)->None:
        pass

    @abstractmethod
    def getDebugLog(self,filename:str,filepath:str)->None:
        pass

    @abstractmethod
    def getInfoLog(self,filename:str,filepath:str)->None:
        pass

    @abstractmethod
    def getWarnLog(self, filename: str, filepath: str) -> None:
        pass

    @abstractmethod
    def getTraceLog(self, filename: str, filepath: str) -> None:
        pass