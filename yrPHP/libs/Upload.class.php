<?php
/**
 * Created by yrPHP.
 * User: Quinn
 * QQ: 284843370
 * Email: quinnH@163.com
 */
namespace libs;


class Upload
{
    static $mimes = null;
    public $savePath = '/';//上传目录
    public $fileName = '';//自定义上传文件后的名称，不含文件后缀 优先级大于isRandName
    public $allowedTypes = array(); //允许上传文件的后缀列表
    public $maxSize = 0; //最大的上传文件 KB
    public $isRandName = true;//设置是否随机重命名文件， false不随机
    public $overwrite = false; //是否覆盖。true则覆盖，false则重命名；


    public $fileExt = null; //文件后缀
    public $isImage = false;//是不是图片
    public $imgWidth = 0;//图片宽度
    public $imgHeight = 0;//图片高度
    public $fileInfo = array();
    public $result = array();
    public $error = array();
    public $fileSize = 0;

    public function __construct($config = array())
    {
        if (!empty($config)) $this->init($config);
    }

    public function init($config = array())
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
        return $this;
    }


    /**
     * 上传文件
     * @param 文件信息数组 $field ，上传文件的表单名称  默认是 $_FILES数组
     */
    public function upload($field = '')
    {
        if ('' === $field) {
            $files = $_FILES;
        } else {
            if (!isset($_FILES[$field])) {
                $this->fileInfo[$field]['errorCode'] = -7;
                return false;
            } else {
                $files[$field] = $_FILES[$field];
            }
        }
        if (empty($files)) {
            $this->fileInfo[$field]['errorCode'] = 4;
            return false;
        }


        foreach ($files as $k => $v) {
            $v['inputName'] = $k;
            if (is_array($v['name'])) {
                $this->uploadMulti($v);
            } else {
                $this->uploadOne($v);
            }
        }

        return !in_array(false, $this->result);

    }

    public function  uploadMulti($files = array())
    {
        $inputName = $files['inputName'];
        unset($files['inputName']);
        foreach ($files['name'] as $k => $v) {
            $uploadFileInfo = array(
                'inputName' => $inputName . $k,
                'name'      => $v,
                'type'      => $files['type'][$k],
                'tmp_name'  => $files['tmp_name'][$k],
                'error'     => $files['error'][$k],
                'size'      => $files['size'][$k],
            );

            $this->uploadOne($uploadFileInfo);
        }

    }

    public function uploadOne($file = array())
    {

        if ($file['error'] > 0) {
            $this->fileInfo[$file['inputName']]['errorCode'] = $file['error'];
            $this->result[] = false;
            return false;
        }

        $this->fileSize = sprintf('%.2f', $file['size'] / 1024);
        if (!$this->checkSize($this->fileSize)) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -2;
            $this->result[] = false;
            return false;
        }
        $this->fileExt = $this->getExtension($file['name']);
        if (!$this->checkFileType($file)) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -1;
            $this->result[] = false;
            return false;
        }

        if (empty($this->fileName)) {
            if ($this->isRandName) {
                $fileName = date('Ymdhis') . mt_rand(100, 999);
            } else {
                $file['name'] = str_replace('.' . $this->fileExt, '', $file['name']);
                $fileName = str_replace('.', '_', $file['name']);
            }
        }
        //目标不存在 则创建
        if (!is_dir($this->savePath)) {
            if (!mkdir($this->savePath, 0777, true)) {
                $this->fileInfo[$file['inputName']]['errorCode'] = -4;
                $this->result[] = false;
                return false;
            }
        }


        if (file_exists($this->savePath . $fileName . '.' . $this->fileExt) && !$this->overwrite) {

            $fileName .= mt_rand(100, 999);
        }


        $fileName .= '.' . $this->fileExt;


        $path = rtrim($this->savePath, '/') . '/';
        $path .= $fileName;


        /* 检查是否合法上传 */
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->fileInfo[$file['inputName']]['errorCode'] = -6;
            $this->result[] = false;
            return false;
        }

        if (move_uploaded_file($file['tmp_name'], $path)) {
            $this->fileInfo[$file['inputName']] = array('fileName'  => $fileName,
                                                        'fileType'  => $file['type'],
                                                        'filePath'  => $path,
                                                        'origName'  => $file['name'],
                                                        'fileExt'   => $this->fileExt,
                                                        'fileSize'  => $this->fileSize,
                                                        'isImage'   => $this->isImage,
                                                        'imgWidth'  => $this->imgWidth,
                                                        'imgHeight' => $this->imgHeight,

            );

            return true;
        } else {
            $this->fileInfo[$file['inputName']]['errorCode'] = -3;
            $this->result[] = false;
            return false;
        }


    }

    /**
     *检查文件大小是否合法
     * @param integer $fileSize 数据
     */
    private function checkSize($fileSize=0)
    {
        return ($fileSize < $this->maxSize) || (0 === $this->maxSize);
    }

    /**
     * 返回文件拓展后缀
     * @param $filename
     * @return string
     */
    public function getExtension($filename)
    {
        $x = explode('.', $filename);

        if (count($x) === 1) {
            return '';
        }

        return strtolower(end($x));

    }

    /** 检查上传的文件MIME类型是否合法
     * @param array $file
     * @return bool
     */
    private function checkFileType($file = array())
    {

        if (($imgSize = getimagesize($file['tmp_name'])) !== false) {
            $this->isImage = true;
            $this->imgWidth = $imgSize[0];
            $this->imgHeight = $imgSize[1];
        }

        if (empty($this->allowedTypes)) return true;

        if (!in_array($this->fileExt, $this->allowedTypes, true)) {
            return false;
        }


        $imgExt = array('gif', 'jpg', 'mpng', 'swf', 'swc', 'psd', 'tiff', 'bmp', 'iff', 'jp2', 'jpx', 'jb2', 'jpc', 'xbm', 'wbmp');
        if (array_intersect($this->allowedTypes, $imgExt) && !$this->isImage) {
            return false;
        }


        if ($mimes = $this->checkMimes($this->fileExt)) {
            return is_array($mimes)
                ? in_array($file['type'], $mimes, true)
                : ($mimes === $file['type']);
        }

        return true;
    }

    public function getFileInfo($inputName = null)
    {
        if(is_null($inputName)){
        return $this->fileInfo;
        }else{
        if(isset($this->fileInfo[$inputName])){
         return    $this->fileInfo[$inputName];
        }else{
               return false;
            }
        }

    }


    /**
     * 根据错误代码获得上传出错信息
     * @param null $errorNum
     * @return string
     */
    public function getError($errorCode = null)
    {
        switch ($errorNum) {
            case 4:
                $str = "没有文件被上传";
                break;
            case 3:
                $str = "文件只有部分被上传";
                break;
            case 2:
                $str = "上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值";
                break;
            case 1:
                $str = "上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值";
                break;
            case -1:
                $str = "不允许该类型上传";
                break;
            case -2:
                $str = "文件过大,上传的文件不能超过{$this->maxSize}KB";
                break;
            case -3:
                $str = "上传失败";
                break;
            case -4:
                $str = "建立存放上传文件目录失败，请重新指定上传目录";
                break;
            case -5:
                $str = "必须指定上传文件的路径";
                break;
            case -6:
                $str = "非法上传文件！";
                break;
            case -7:
                $str = "文件表单不存在";
                break;
            default:
                $str = "未知错误";
        }
        return $str;
    }


    /**
     * | MIME TYPES
    | -------------------------------------------------------------------
    | This file contains an array of mime types.  It is used by the
    | Upload class to help identify allowed file types.
     *
     * @param string $ext
     * @return bool
     */
    private function checkMimes($ext = '')
    {
/*
        if (!self::$mimes)
            self::$mimes = require(BASE_PATH . 'config/mimes.php');

        if (isset(self::$mimes[$this->fileExt])) {
            return is_array(self::$mimes[$this->fileExt])
                ? in_array($file['type'], self::$mimes[$this->fileExt], true)
                : (self::$mimes[$this->fileExt] === $file['type']);
        }
        */

        if (empty($ext)) return false;
        $mimes = array(
            'hqx'   => array('application/mac-binhex40', 'application/mac-binhex', 'application/x-binhex40', 'application/x-mac-binhex40'),
            'cpt'   => 'application/mac-compactpro',
            'csv'   => array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain'),
            'bin'   => array('application/macbinary', 'application/mac-binary', 'application/octet-stream', 'application/x-binary', 'application/x-macbinary'),
            'dms'   => 'application/octet-stream',
            'lha'   => 'application/octet-stream',
            'lzh'   => 'application/octet-stream',
            'exe'   => array('application/octet-stream', 'application/x-msdownload'),
            'class' => 'application/octet-stream',
            'psd'   => array('application/x-photoshop', 'image/vnd.adobe.photoshop'),
            'so'    => 'application/octet-stream',
            'sea'   => 'application/octet-stream',
            'dll'   => 'application/octet-stream',
            'oda'   => 'application/oda',
            'pdf'   => array('application/pdf', 'application/force-download', 'application/x-download', 'binary/octet-stream'),
            'ai'    => array('application/pdf', 'application/postscript'),
            'eps'   => 'application/postscript',
            'ps'    => 'application/postscript',
            'smi'   => 'application/smil',
            'smil'  => 'application/smil',
            'mif'   => 'application/vnd.mif',
            'xls'   => array('application/vnd.ms-excel', 'application/msexcel', 'application/x-msexcel', 'application/x-ms-excel', 'application/x-excel', 'application/x-dos_ms_excel', 'application/xls', 'application/x-xls', 'application/excel', 'application/download', 'application/vnd.ms-office', 'application/msword'),
            'ppt'   => array('application/powerpoint', 'application/vnd.ms-powerpoint', 'application/vnd.ms-office', 'application/msword'),
            'pptx'  => array('application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/x-zip', 'application/zip'),
            'wbxml' => 'application/wbxml',
            'wmlc'  => 'application/wmlc',
            'dcr'   => 'application/x-director',
            'dir'   => 'application/x-director',
            'dxr'   => 'application/x-director',
            'dvi'   => 'application/x-dvi',
            'gtar'  => 'application/x-gtar',
            'gz'    => 'application/x-gzip',
            'gzip'  => 'application/x-gzip',
            'php'   => array('application/x-httpd-php', 'application/php', 'application/x-php', 'text/php', 'text/x-php', 'application/x-httpd-php-source'),
            'php4'  => 'application/x-httpd-php',
            'php3'  => 'application/x-httpd-php',
            'phtml' => 'application/x-httpd-php',
            'phps'  => 'application/x-httpd-php-source',
            'js'    => array('application/x-javascript', 'text/plain'),
            'swf'   => 'application/x-shockwave-flash',
            'sit'   => 'application/x-stuffit',
            'tar'   => 'application/x-tar',
            'tgz'   => array('application/x-tar', 'application/x-gzip-compressed'),
            'z'     => 'application/x-compress',
            'xhtml' => 'application/xhtml+xml',
            'xht'   => 'application/xhtml+xml',
            'zip'   => array('application/x-zip', 'application/zip', 'application/x-zip-compressed', 'application/s-compressed', 'multipart/x-zip'),
            'rar'   => array('application/x-rar', 'application/rar', 'application/x-rar-compressed'),
            'mid'   => 'audio/midi',
            'midi'  => 'audio/midi',
            'mpga'  => 'audio/mpeg',
            'mp2'   => 'audio/mpeg',
            'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
            'aif'   => array('audio/x-aiff', 'audio/aiff'),
            'aiff'  => array('audio/x-aiff', 'audio/aiff'),
            'aifc'  => 'audio/x-aiff',
            'ram'   => 'audio/x-pn-realaudio',
            'rm'    => 'audio/x-pn-realaudio',
            'rpm'   => 'audio/x-pn-realaudio-plugin',
            'ra'    => 'audio/x-realaudio',
            'rv'    => 'video/vnd.rn-realvideo',
            'wav'   => array('audio/x-wav', 'audio/wave', 'audio/wav'),
            'bmp'   => array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
            'gif'   => 'image/gif',
            'jpeg'  => array('image/jpeg', 'image/pjpeg'),
            'jpg'   => array('image/jpeg', 'image/pjpeg'),
            'jpe'   => array('image/jpeg', 'image/pjpeg'),
            'png'   => array('image/png', 'image/x-png'),
            'tiff'  => 'image/tiff',
            'tif'   => 'image/tiff',
            'css'   => array('text/css', 'text/plain'),
            'html'  => array('text/html', 'text/plain'),
            'htm'   => array('text/html', 'text/plain'),
            'shtml' => array('text/html', 'text/plain'),
            'txt'   => 'text/plain',
            'text'  => 'text/plain',
            'log'   => array('text/plain', 'text/x-log'),
            'rtx'   => 'text/richtext',
            'rtf'   => 'text/rtf',
            'xml'   => array('application/xml', 'text/xml', 'text/plain'),
            'xsl'   => array('application/xml', 'text/xsl', 'text/xml'),
            'mpeg'  => 'video/mpeg',
            'mpg'   => 'video/mpeg',
            'mpe'   => 'video/mpeg',
            'qt'    => 'video/quicktime',
            'mov'   => 'video/quicktime',
            'avi'   => array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
            'movie' => 'video/x-sgi-movie',
            'doc'   => array('application/msword', 'application/vnd.ms-office'),
            'docx'  => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword', 'application/x-zip'),
            'dot'   => array('application/msword', 'application/vnd.ms-office'),
            'dotx'  => array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/msword'),
            'xlsx'  => array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/vnd.ms-excel', 'application/msword', 'application/x-zip'),
            'word'  => array('application/msword', 'application/octet-stream'),
            'xl'    => 'application/excel',
            'eml'   => 'message/rfc822',
            'json'  => array('application/json', 'text/json'),
            'pem'   => array('application/x-x509-user-cert', 'application/x-pem-file', 'application/octet-stream'),
            'p10'   => array('application/x-pkcs10', 'application/pkcs10'),
            'p12'   => 'application/x-pkcs12',
            'p7a'   => 'application/x-pkcs7-signature',
            'p7c'   => array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7m'   => array('application/pkcs7-mime', 'application/x-pkcs7-mime'),
            'p7r'   => 'application/x-pkcs7-certreqresp',
            'p7s'   => 'application/pkcs7-signature',
            'crt'   => array('application/x-x509-ca-cert', 'application/x-x509-user-cert', 'application/pkix-cert'),
            'crl'   => array('application/pkix-crl', 'application/pkcs-crl'),
            'der'   => 'application/x-x509-ca-cert',
            'kdb'   => 'application/octet-stream',
            'pgp'   => 'application/pgp',
            'gpg'   => 'application/gpg-keys',
            'sst'   => 'application/octet-stream',
            'csr'   => 'application/octet-stream',
            'rsa'   => 'application/x-pkcs7',
            'cer'   => array('application/pkix-cert', 'application/x-x509-ca-cert'),
            '3g2'   => 'video/3gpp2',
            '3gp'   => 'video/3gp',
            'mp4'   => 'video/mp4',
            'm4a'   => 'audio/x-m4a',
            'f4v'   => 'video/mp4',
            'webm'  => 'video/webm',
            'aac'   => 'audio/x-acc',
            'm4u'   => 'application/vnd.mpegurl',
            'm3u'   => 'text/plain',
            'xspf'  => 'application/xspf+xml',
            'vlc'   => 'application/videolan',
            'wmv'   => array('video/x-ms-wmv', 'video/x-ms-asf'),
            'au'    => 'audio/x-au',
            'ac3'   => 'audio/ac3',
            'flac'  => 'audio/x-flac',
            'ogg'   => 'audio/ogg',
            'kmz'   => array('application/vnd.google-earth.kmz', 'application/zip', 'application/x-zip'),
            'kml'   => array('application/vnd.google-earth.kml+xml', 'application/xml', 'text/xml'),
            'ics'   => 'text/calendar',
            'ical'  => 'text/calendar',
            'zsh'   => 'text/x-scriptzsh',
            '7zip'  => array('application/x-compressed', 'application/x-zip-compressed', 'application/zip', 'multipart/x-zip'),
            'cdr'   => array('application/cdr', 'application/coreldraw', 'application/x-cdr', 'application/x-coreldraw', 'image/cdr', 'image/x-cdr', 'zz-application/zz-winassoc-cdr'),
            'wma'   => array('audio/x-ms-wma', 'video/x-ms-asf'),
            'jar'   => array('application/java-archive', 'application/x-java-application', 'application/x-jar', 'application/x-compressed'),
            'svg'   => array('image/svg+xml', 'application/xml', 'text/xml'),
            'vcf'   => 'text/x-vcard'
        );

        if(isset($mimes[$ext])) return $mimes[$ext];

        return false;
    }

}