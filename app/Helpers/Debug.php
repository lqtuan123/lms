<?php

namespace App\Helpers;

/**
 * Lớp Helper để debug trong quá trình phát triển
 */
class Debug
{
    /**
     * Ghi log debug với thông tin chi tiết
     *
     * @param mixed $message Thông điệp cần ghi log
     * @param string $level Mức độ log (debug, info, warning, error)
     * @param array $context Thông tin bổ sung
     */
    public static function log($message, $level = 'debug', $context = [])
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($trace[1]) ? $trace[1] : $trace[0];
        
        $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
        $line = isset($caller['line']) ? $caller['line'] : 0;
        $function = isset($caller['function']) ? $caller['function'] : 'unknown';
        $class = isset($caller['class']) ? $caller['class'] : 'unknown';
        
        $contextInfo = !empty($context) ? ' - Context: ' . json_encode($context) : '';
        
        $fullMessage = "[{$class}::{$function}] ({$file}:{$line}) - ";
        
        if (is_array($message) || is_object($message)) {
            $fullMessage .= json_encode($message);
        } else {
            $fullMessage .= $message;
        }
        
        $fullMessage .= $contextInfo;
        
        switch (strtolower($level)) {
            case 'info':
                \Illuminate\Support\Facades\Log::info($fullMessage);
                break;
            case 'warning':
                \Illuminate\Support\Facades\Log::warning($fullMessage);
                break;
            case 'error':
                \Illuminate\Support\Facades\Log::error($fullMessage);
                break;
            case 'debug':
            default:
                \Illuminate\Support\Facades\Log::debug($fullMessage);
                break;
        }
        
        // Nếu app đang ở chế độ debug, ghi thêm vào log lỗi của hệ thống
        if (config('app.debug', false)) {
            error_log($fullMessage);
        }
    }
    
    /**
     * Ghi log debug
     */
    public static function debug($message, $context = [])
    {
        self::log($message, 'debug', $context);
    }
    
    /**
     * Ghi log info
     */
    public static function info($message, $context = [])
    {
        self::log($message, 'info', $context);
    }
    
    /**
     * Ghi log warning
     */
    public static function warning($message, $context = [])
    {
        self::log($message, 'warning', $context);
    }
    
    /**
     * Ghi log error
     */
    public static function error($message, $context = [])
    {
        self::log($message, 'error', $context);
    }
    
    /**
     * Dump and die với định dạng dễ đọc
     */
    public static function dd($data)
    {
        header('Content-Type: text/html; charset=utf-8');
        echo '<pre style="background-color:#f8f9fa;padding:10px;border:1px solid #ddd;border-radius:5px;font-family:monospace;font-size:14px;">';
        var_dump($data);
        echo '</pre>';
        die();
    }
} 