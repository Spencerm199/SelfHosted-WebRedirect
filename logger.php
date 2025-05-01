<?php
class Logger {
    private $logDir;
    private $logFile;

    public function __construct($directory = 'logs') {
        $this->logDir = __DIR__ . DIRECTORY_SEPARATOR . $directory;
        if (!is_dir($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        $this->logFile = $this->logDir . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
    }

    private function write($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }

    public function info($message) {
        $this->write('INFO', $message);
    }

    public function error($message) {
        $this->write('ERROR', $message);
    }

    public function debug($message) {
        $this->write('DEBUG', $message);
    }
} 