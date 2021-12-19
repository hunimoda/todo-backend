<?php

namespace Core;

/**
 * Error and exception handler
 */
class Error {
    /**
     * Error handler
     * 
     * Convert all errors to exceptions by throwing an ErrorException
     * 
     * @param int       $level      Error level
     * @param string    $message    Error message
     * @param string    $file       Filename the error was raised in
     * @param int       $line       Line number in the file
     * 
     * @return void
     */
    public static function errorHandler($level, $message, $file, $line) {
        if (error_reporting() !== 0) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /** Exception handler
     * 
     * @param Exception $exception  The exception thrown
     * 
     * @return void
     */
    public static function exceptionHandler($exception) {
        $code = $exception->getCode();
        if ($code != 404) {
            $code = 500;
        }
        http_response_code($code);

        $args = [
            'class' => get_class($exception), 
            'message' => $exception->getMessage(), 
            'trace' => $exception->getTraceAsString(), 
            'file' => $exception->getFile(), 
            'line' => $exception->getLine()
        ];

        if (\App\Config::DEBUG) {
            View::render('Error/error.html', $args);
        } else {
            $log_file = ROOT . '/logs/error.log';
            ini_set('error_log', $log_file);

            error_log(View::getTemplate('Error/error.log.template', $args));

            View::render("Error/{$code}.html");
        }
    }
}