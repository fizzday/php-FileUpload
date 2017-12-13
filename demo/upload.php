<?php
/**
 * 上传文件接收服务
 */
require '../src/FileUpload.php';

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传本地服务器 *********
$res = $up->upload();

print_r($res);

