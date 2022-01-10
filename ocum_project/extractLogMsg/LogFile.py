import logging
# from logging import DEBUG, INFO, ERROR, WARN, WARNING
from logging import  INFO

class LogFileClass(object):
    def __init__(self, name, format="%(asctime)s | %(levelname)s | %(message)s", level=INFO):
        # Initial construct.
        self.format = format
        self.level = level
        self.name = name+"LOG.log"

        # Logger configuration.
        # self.console_formatter = logging.Formatter(self.format)
        # self.console_logger = logging.StreamHandler(sys.stdout)
        # self.console_logger.setFormatter(self.console_formatter)

        # create file handler which logs info messages
        # Logger configuration.
        self.fh = logging.FileHandler(self.name, 'w', 'utf-8')
        self.fh.setLevel(logging.INFO)


        # Complete logging config.
        self.logger = logging.getLogger("NetApp")
        self.logger.setLevel(self.level)
        self.logger.addHandler(self.fh)

    def info(self, msg, extra=None):
        self.logger.info(msg, extra=extra)

    def error(self, msg, extra=None):
        self.logger.error(msg, extra=extra)

    def warn(self, msg, extra=None):
        self.logger.warning(msg, extra=extra)

    def debug(self, msg, extra=None):
        self.logger.debug(msg, extra=extra)