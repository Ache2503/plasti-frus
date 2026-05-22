<?php
namespace App\Console\Commands;

abstract class BaseCommand {
    abstract public function execute(array $args): void;
    
    protected function info(string $message): void {
        echo "\033[32m[INFO]\033[0m {$message}\n";
    }
    
    protected function error(string $message): void {
        echo "\033[31m[ERROR]\033[0m {$message}\n";
    }
    
    protected function warn(string $message): void {
        echo "\033[33m[WARN]\033[0m {$message}\n";
    }
    
    protected function table(array $headers, array $rows): void {
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = mb_strlen($header);
            foreach ($rows as $row) {
                $len = mb_strlen((string)($row[$i] ?? ''));
                if ($len > $widths[$i]) $widths[$i] = $len;
            }
        }
        
        $drawLine = function() use ($widths) {
            echo '+';
            foreach ($widths as $w) echo str_repeat('-', $w + 2) . '+';
            echo "\n";
        };
        
        $drawRow = function($row) use ($widths) {
            echo '|';
            foreach ($row as $i => $cell) {
                echo ' ' . str_pad((string)$cell, $widths[$i]) . ' |';
            }
            echo "\n";
        };
        
        $drawLine();
        $drawRow($headers);
        $drawLine();
        foreach ($rows as $row) $drawRow($row);
        $drawLine();
    }
}
