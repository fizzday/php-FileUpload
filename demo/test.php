<?php
/**
 * 先上传到本地服务器, 再上传到远程服务器
 */
require '../src/FileUpload.php';

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传本地服务器 *********
$res = $up->path('assets')->upload();

$res = json_decode($res, true);

if (!$res) die($up->getError());

// ********* 上传远程服务器 *********
$remote = $up->remoteUrl('http://www.cc/demo/upload.php')
    ->path('remote')
    ->host('http://www.cc/demo')
    ->remoteUpload(array_column($res, 'file'));

$remote = json_decode($remote, true);

if (!$remote) die($up->getError());

// 返回带名字的远程图片地址
$res_arr = array_column($res, 'filename');

// 将远程返回的名字, 修改为原始名字
foreach ($remote as &$item) {
    $index = array_search($item['name'], $res_arr);
    $item['name'] = $res[$index]['name'];
}

print_r($remote);
