<?php
class Logger {
    private $logDir;
    private $maxFileSize = 5242880; // 5MB
    private $logFile;

    public function __construct($logDir = 'logs') {
        // Ensure log directory is absolute and exists
        $this->logDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . $logDir;
        if (!file_exists($this->logDir)) {
            mkdir($this->logDir, 0755, true);
        }
        
        // Create .htaccess to prevent direct access
        $htaccess = $this->logDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Deny from all");
        }

        // Set current log file
        $this->logFile = $this->logDir . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
    }

    public function log($message, $level = 'INFO') {
        // Rotate log if needed
        $this->rotateLogIfNeeded();

        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $entry = sprintf(
            "[%s] [%s] %s\n",
            $timestamp,
            strtoupper($level),
            is_array($message) || is_object($message) ? json_encode($message) : $message
        );

        // Write to log file
        file_put_contents($this->logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    private function rotateLogIfNeeded() {
        if (file_exists($this->logFile) && filesize($this->logFile) > $this->maxFileSize) {
            $info = pathinfo($this->logFile);
            $rotated = sprintf(
                '%s%s%s_%s.log',
                $info['dirname'],
                DIRECTORY_SEPARATOR,
                $info['filename'],
                date('H-i-s')
            );
            rename($this->logFile, $rotated);

            // Clean up old logs (keep last 7 days)
            $this->cleanOldLogs();
        }
    }

    private function cleanOldLogs() {
        $files = glob($this->logDir . DIRECTORY_SEPARATOR . '*.log');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= 7 * 24 * 60 * 60) { // 7 days
                    unlink($file);
                }
            }
        }
    }

    public function error($message) {
        $this->log($message, 'ERROR');
    }

    public function info($message) {
        $this->log($message, 'INFO');
    }

    public function debug($message) {
        $this->log($message, 'DEBUG');
    }
}
?> 