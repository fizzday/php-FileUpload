# php-fileUpload
文件上传下载, 同时支持上传远程目录, 支持多文件上传
## 安装
使用composer
```sh
composer require fizzday/php-fileupload dev-master
```
## 使用
### 上传到本地服务器
```php
<?php

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传本地服务器 *********
$res = $up
    ->type()    // 设置上传类型,默认'jpg,jpeg,gif,png,pdf,bmp,zip,psd,tif';
    ->size()    // 设置允许大小,默认 10000k;
    ->path()    // 设置上传路径,默认 assets;
    ->autoSub('date') // 设置自动子目录,默认为空, 如果执行了该方法则默认位(date函数:日期2017/12/22),可选 sha1 等 hash 函数
    ->upload(); // 执行上传;

if (!$res) die($up->getError());    // 错误信息

print_r($res);
```
结果为(多文件返回)
```json
[
    {
        "file": "assets/1513738132194208.jpg",
        "uptime": 1513738132,
        "fieldname": "file",
        "basename": "1513738132194208.jpg",
        "filename": "1513738132194208",
        "name": "本地图片名字",
        "size": 62692,
        "ext": "jpg",
        "dir": "assets",
        "image": 1,
        "url": "http://localhost/assets/1513738132194208.jpg"
    }
]
```
### 上传到远程服务器
```php
<?php

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传远程服务器 *********
$remote = $up
    ->remoteUrl('http://remotehost.com/demo/upload.php')    // 远程地址
    ->path('pro_head')  // 路径
    ->autoSub('date')   // 自动子目录
    ->host('http://remotehost.com/demo')    // 图片地址前缀
    ->remoteUpload(['/tmp/assets/1513144248222219.jpeg']);  // 本地文件

if (!$remote) die($up->getError());    // 错误信息

print_r($remote);
```
结果为(多文件返回)
```json
[
    {
        "file": "assets/1513738132194208.jpg",
        "uptime": 1513738132,
        "fieldname": "file",
        "basename": "1513738132194208.jpg",
        "filename": "1513738132194208",
        "name": "本地图片名字",
        "size": 62692,
        "ext": "jpg",
        "dir": "assets",
        "image": 1,
        "url": "http://remotehost.com/demo/assets/1513738132194208.jpg"
    }
]
```
## 通过 facade 直接静态调用
需要安装`fizzday\facades`: `composer require fizzday/facades dev-master`
### 使用
```php
<?php

use Fizzday\FileUpload\FileFacade as File;

$res = File::upload();
$res = File::path()->type()->upload();

if (!$res) die($up->getError());    // 错误信息

print_r($res);
```