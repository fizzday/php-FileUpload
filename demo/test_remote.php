<?php
/**
 * 直接上传到远程服务器
 */
require '../src/FileUpload.php';

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传远程服务器 *********
$remote = $up->remoteUrl('http://www.cc/demo/upload.php')
    ->path('pro_head')
    ->autoSub('date')
    ->host('http://www.cc/demo')
    ->remoteUpload(['/tmp/assets/1513144248222219.jpeg']);

print_r($remote);