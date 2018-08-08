<?php

namespace Fizzday\FileUpload;

use Exception;

//error_reporting(null);

class FileUpload
{
    //上传类型
    protected $type = 'jpg,jpeg,gif,png,pdf,bmp,zip,psd,tif';
    //上传文件大小
    protected $size = 10000;
    //上传路径
    protected $path = '';
    //是否自动文件目录
    protected $autoSub = '';
    //上传路径
    protected $subPath = '';
    //错误信息
    protected $error;

    /*************
     * 远程服务器 *
     ************/
    // 远程上传地址
    protected $remoteUrl;
    // 根域名(可能含有子目录,如: http://url.com/img)
    protected $host;
    // 是否本地存储
    protected $localStorage = false;
    // 文件名
    protected $file = '';

    /**
     * 上传初始化
     *
     * @param array $config
     * [
     *  'path'=>'上传目录',
     *  'type'=>'上传类型',
     *  'size'=>'上传大小,单位KB'
     * ]
     *
     * @return $this
     */
    public function config(array $config)
    {
        //上传路径
        if (isset($config['path'])) {
            $this->path = $config['path'];
        }
        //上传类型
        if (isset($config['type'])) {
            $this->type = $config['type'];
        }

        //允许大小
        if (isset($config['size'])) {
            $this->size = $config['size'] * 1024;
        }

        return $this;
    }

    /**
     * 设置上传类型
     * @param $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * 设置上传大小
     * @param $size
     * @return $this
     */
    public function size($size)
    {
        $this->size = $size * 1024;

        return $this;
    }

    /**
     * 设置上传目录
     * @param string $path 目录
     * @param bool $auto_sub 开启后生成自动格式目录(date, hash(sha1))
     * @return $this
     */
    public function path($path = 'assets')
    {
        $this->path = $path;

        return $this;
    }

    /**
     * 是否开启自动目录
     * @param bool $auto (date, hash(sha1) ...)
     * @return $this
     */
    public function autoSub($auto = 'date')
    {
        $this->autoSub = $auto;
        return $this;
    }

    /**
     * 获取自动目录
     * @param string $sub
     */
    public function getSubPath()
    {
        if ($sub = $this->autoSub) {
            switch ($sub) {
                case 'date':
                    $path = date("Y/m/d");
                    $this->subPath = $path;
                    break;
                case 'hash':
                    $path = sha1($this->get_hash(true));
                    $this->subPath = $path;
                    break;
                default:
                    if (function_exists($sub)) {
                        $path = $sub($this->get_hash(true));
                        $this->subPath = $path;
                        break;
                    } else {
                        $this->error = '该hash方法不存在';
                        return false;
                    }
            }
        }
    }

    /**
     * 获取随机hash值作为目录名字
     * @param bool $return_str
     * @return string
     */
    private function get_hash($return_str = false)
    {
        $chars = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()+-');
        $random = $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)] . $chars[mt_rand(0, 73)];//Random 5 times
        $content = uniqid() . $random;  // 类似 5443e09c27bf4aB4uT

        if ($return_str) return $content;
        return sha1($content);
    }

    /**
     * 执行上传
     * @param null $fieldName 字段名
     * @return array|bool
     * @throws Exception
     */
    public function upload($fieldName = null)
    {
        if (!empty($_POST['path'])) $this->path = $_POST['path'];
        if (!empty($_POST['autoSub'])) $this->autoSub = $_POST['autoSub'];
        if (!empty($_POST['host'])) $this->host = $_POST['host'];

        if (!$this->createDir()) {
            return false;
        }
        $files = $this->format($fieldName);
        $uploadedFile = array();
        //验证文件
        if (!empty($files)) {
            foreach ($files as $v) {
                $info = pathinfo($v ['name']);
                $v["ext"] = isset($info ["extension"]) ? $info['extension'] : '';
                $v['filename'] = isset($info['filename']) ? $info['filename'] : '';
                if (!$this->checkFile($v)) {
                    continue;
                }
                $upFile = $this->save($v);
                if ($upFile) {
                    $uploadedFile[] = $upFile;
                }
            }
        }

        return self::jsonReturn($uploadedFile);
    }

    /**
     * 储存文件
     *
     * @param string $file 储存的文件
     *
     * @return boolean
     */
    private function save($file)
    {
        $fileName = $this->getMicroTimeStr() . "." . $file['ext'];
        $filePath = $this->path . '/' . $fileName;
        if (!move_uploaded_file($file ['tmp_name'], $filePath) && is_file($filePath)) {
            $this->error('移动临时文件失败');

            return false;
        }
        $_info = pathinfo($filePath);
        $arr = array();
        $arr['file'] = $filePath;
        $arr['uptime'] = time();
        $arr['fieldname'] = $file['fieldname'];
        $arr['basename'] = $_info['basename'];
        $arr['filename'] = $_info['filename']; //新文件名
        $arr['name'] = $file['filename']; //旧文件名
        $arr['size'] = $file['size'];
        $arr['ext'] = $file['ext'];
        $arr['dir'] = $this->path;
        $arr['image'] = getimagesize($filePath) ? 1 : 0;
        $arr['md5'] = md5_file($filePath);

        // 拼接文件的真实地址
//        if (!$this->host) {
//            $this->host = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'];
//        }
        $arr['url'] = $this->host ? ($this->host . '/' . $filePath) : '';

        return $arr;
    }

    /**
     * 获取microtime字符串
     * @param int $len 长度
     * @return bool|string
     */
    private function getMicroTimeStr($len = 16)
    {
        if ($len > 18) $len = 18;

        $mtime = explode(' ', microtime()); // 0.29348183 1513132695
        $startTime = substr($mtime[1] . $mtime[0] * pow(10, ($len - 10)), 0, $len);

        return $startTime;
    }

    /**
     * 将上传文件整理为标准数组
     * @param $fieldName
     * @return array|bool
     */
    private function format($fieldName)
    {
        if ($fieldName == null) {
            $files = $_FILES;
        } else if (isset($_FILES[$fieldName])) {
            $files[$fieldName] = $_FILES[$fieldName];
        }

        if (!isset($files)) {
            $this->error = '没有任何文件上传';

            return false;
        }
        $info = array();
        $n = 0;
        foreach ($files as $name => $v) {
            if (is_array($v ['name'])) {
                $count = count($v ['name']);
                for ($i = 0; $i < $count; $i++) {
                    foreach ($v as $m => $k) {
                        $info [$n] [$m] = $k [$i];
                    }
                    $info [$n] ['fieldname'] = $name; //字段名
                    $n++;
                }
            } else {
                $info [$n] = $v;
                $info [$n] ['fieldname'] = $name; //字段名
                $n++;
            }
        }

        return $info;
    }

    /**
     * 创建目录
     * @return bool
     * @throws Exception
     */
    private function createDir()
    {
        if (!$this->path) $this->path();

        if ($this->autoSub) $this->getSubPath();

        $this->path = $this->path . ($this->subPath ? ('/' . $this->subPath) : '');

//        die($this->path);

        if (!is_dir($this->path) && !mkdir($this->path, 0755, true)) {
            throw new Exception("上传目录创建失败");
        }

        return true;
    }

    /**
     * 文件合法性校验
     * @param $file
     * @return bool
     */
    private function checkFile($file)
    {
        if ($file ['error'] != 0) {
            $this->error($file ['error']);

            return false;
        }
        if (!is_array($this->type)) {
            $this->type = explode(',', $this->type);
        }
        if (!in_array(strtolower($file['ext']), $this->type)) {
            $this->error = '文件类型不允许';

            return false;
        }
        if (strstr(strtolower($file['type']), "image") && !getimagesize($file['tmp_name'])) {
            $this->error = '上传内容不是一个合法图片';

            return false;
        }
//		if ( $file ['size'] > $this->size ) {
//			$this->error = '上传文件大于' . get_size( $this->size );
//
//			return false;
//		}

        if (!is_uploaded_file($file ['tmp_name'])) {
            $this->error = '非法文件';

            return false;
        }

        return true;
    }

    /**
     * 错误信息
     * @param $error
     */
    private function error($error)
    {
        switch ($error) {
            case UPLOAD_ERR_INI_SIZE :
                $this->error = '上传文件超过PHP.INI配置文件允许的大小';
                break;
            case UPLOAD_ERR_FORM_SIZE :
                $this->error = '文件超过表单限制大小';
                break;
            case UPLOAD_ERR_PARTIAL :
                $this->error = '文件只上有部分上传';
                break;
            case UPLOAD_ERR_NO_FILE :
                $this->error = '没有上传文件';
                break;
            case UPLOAD_ERR_NO_TMP_DIR :
                $this->error = '没有上传临时文件夹';
                break;
            case UPLOAD_ERR_CANT_WRITE :
                $this->error = '写入临时文件夹出错';
                break;
            default:
                $this->error = '未知错误';
        }
    }

    /**
     * 返回上传时发生的错误原因
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * 下载文件
     *
     * @param $filepath
     */
    public function download($filepath, $name = '')
    {
        if (is_file($filepath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . ($name ?: basename($filepath)));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filepath));
            readfile($filepath);
        }
    }

    /**
     * 远程上传地址
     * @param string $remoteUrl
     * @return $this
     */
    public function remoteUrl($remoteUrl = '')
    {
        $this->remoteUrl = $remoteUrl;
//        $parse = parse_url($remoteUrl);
//        $this->remoteHost = $parse['scheme'] . '://' . $parse['host'];

        return $this;
    }

    public function host($host = '')
    {
        $this->host = $host;
//        $parse = parse_url($remoteUrl);
//        $this->remoteHost = $parse['scheme'] . '://' . $parse['host'];

        return $this;
    }

    /**
     * 是否本地存储(默认否)
     * @param bool $localStorage
     * @return $this
     */
    public function localStorage($localStorage = false)
    {
        $this->localStorage = $localStorage;

        return $this;
    }

    /**
     * curl上传到远程服务器
     * @param null $file
     * @return bool|mixed
     */
    public function remoteUpload($file = null)
    {
        if (empty($file)) {
            $this->error = '文件地址不能为空';
            return false;
        }
        if (empty($this->remoteUrl)) {
            $this->error = '远程地址不能为空';
            return false;
        }
        $data["dir"] = $this->path;
        $data["autoSub"] = $this->autoSub;
        $data["host"] = $this->host;
        return self::curlUpload($this->remoteUrl, $data, $file, 'file');
    }

    /**
     * curl 上传文件
     * @param string $url 上传地址
     * @param array $data 上传的其他数据
     * @param string $file 上传的图片
     * @param string $fileKey 图片的 key 值, 相当于form下input的name值
     */
    public static function curlUpload($url = '', $data = array(), $file = '', $fileKey = '')
    {
        if (empty($fileKey)) $fileKey = 'files';

        if (!empty($file)) {
            if (is_array($file)) {
                foreach ($file as $key => $value) {
                    $data[$fileKey . '[' . $key . ']'] = new \CURLFile(realpath($value));
                }
            } else {
                $data[$fileKey . '[0]'] = new \CURLFile(realpath($file));
            }
        }

        $header = array('token:JxRaZezavm3HXM3d9pWnYiqqQC1SJbsU');

        $request = curl_init($url);

        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_HTTPHEADER, $header); //设置头信息的地方
        curl_setopt($request, CURLOPT_TIMEOUT,30);   //只需要设置一个秒的数量就可以

        curl_setopt($request, CURLOPT_POSTFIELDS, $data);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($request);
        curl_close($request);
//die($res);die;
        return $res;
    }

    static function successReturn($data = null, $status = 1, $ext = null)
    {
        $re = array();

        if ($status == 1) {
            $msg = 'success';
        } else {
            if ($data === null) $msg = 'fail';
            else $msg = $data;
        }

        $re['status'] = $status;
        $re['data'] = $data;
        $re['msg'] = $msg;

        if ($ext) $re['ext'] = $ext;

        return $re;
    }

    /**
     * 接口格式化失败返回
     * @param [mixed]       $data       [返回数据或错误信息]
     * @param [int]         $status     [1成功, 2失败, 100验证失败]
     */
    static function failReturn($data = '', $status = 2, $ext = '')
    {
        return self::successReturn($data, $status, $ext);
    }

    static function jsonReturn($data = '', $status = 1, $ext = '')
    {
        return json_encode(successReturn($data, $status, $ext));
    }
}

//$file = new FileUpload();
//
//echo $file->path('hccz')->host('http://upload-cfda.wochacha.com')->autoSub('date')->upload();
