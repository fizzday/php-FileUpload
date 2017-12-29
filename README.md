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
    ->host('http://url.com')    // 图片域名前缀, 用于拼接上传图片后的路径, 成为完整的图片链接
    ->type('jpg,png')    // 设置上传类型,默认'jpg,jpeg,gif,png,pdf,bmp,zip,psd,tif';
    ->size('10000')    // 设置允许大小,默认 10000k;
    ->path('assets')    // 设置上传路径,默认 assets;
    ->autoSub('date') // 设置自动子目录,默认为空, 如果执行了该方法则默认位(date函数:日期2017/12/22),可选 sha1/md5 等 hash 函数
    ->upload(); // 执行上传;

if (!$res) die($up->getError());    // 错误信息

print_r($res);
```
结果为(多文件返回)
```json
{
    "status": 1,
    "data": [
        {
            "file": "assets/2017/12/20/151376543344429.jpg",
            "uptime": 1513765433,
            "fieldname": "aaa",
            "basename": "151376543344429.jpg",
            "filename": "151376543344429",
            "name": "本地图片名字",
            "size": 62692,
            "ext": "jpg",
            "dir": "assets/2017/12/20",
            "image": 1,
            "url": "http://url.com/assets/2017/12/20/151376543344429.jpg"
        }
    ],
    "msg": "success"
}
```
- status: 1成功, 2失败  
- data: 成功后的数据, 为多文件数组  
- msg: 失败后存放失败信息  

### 上传到远程服务器
```php
<?php

use Fizzday\FileUpload\FileUpload;

// 实例化
$up = new FileUpload();

// ********* 上传远程服务器 *********
$remote = $up
    ->remoteUrl('http://remotehost.com/demo/upload.php')    // 远程地址
    ->path('remote_path')  // 路径
    ->autoSub('date')   // 自动子目录
    ->host('http://remotehost.com/demo')    // 图片地址前缀
    ->remoteUpload(['/tmp/assets/1513144248222219.jpeg']);  // 本地文件

echo $remote;
```
结果为(多文件返回)
```json
{
    "status": 1,
    "data": [
        {
            "file": "assets/2017/12/20/151376543344429.jpg",
            "uptime": 1513765433,
            "fieldname": "aaa",
            "basename": "151376543344429.jpg",
            "filename": "151376543344429",
            "name": "本地图片名字",
            "size": 62692,
            "ext": "jpg",
            "dir": "assets/2017/12/20",
            "image": 1,
            "url": "http://remotehost.com/demo/remote_path/2017/12/20/151376543344429.jpg"
        }
    ],
    "msg": "success"
}
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