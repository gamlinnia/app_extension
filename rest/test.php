<?php

$pid = pcntl_fork(); //在這裡開始產生程式的分岔
if ($pid == -1) {
    die('無法產生子程序');
} else if ($pid) {
    error_log('parent');
} else {
    error_log('child');
}

?>