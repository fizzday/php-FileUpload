<?php
/**
 * 上传文件接收服务
 */
require '../src/FileUpload.php';

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传本地服务器 *********
$res = $up
    ->type()    // 设置上传类型
    ->size()    // 设置允许大小
    ->path()    // 设置上传路径
    ->autoSub() // 设置自动子目录
    ->upload(); // 执行上传

//if (!$res) echo $up->getError();

echo($res);
/*
[
    {
        "file": "assets/1513738132194208.jpg",
        "uptime": 1513738132,
        "fieldname": "file",
        "basename": "1513738132194208.jpg",
        "filename": "1513738132194208",
        "name": "biaoqing1",
        "size": 62692,
        "ext": "jpg",
        "dir": "assets",
        "image": 1,
        "url": "http://localhost/assets/1513738132194208.jpg"
    }
]
*/

