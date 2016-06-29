<?php
namespace app\user\controller;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use think\Db;

class Index extends Common
{
    private $HOST = 'http://qzonebackgroundmusic.qiniudn.com/';
    public function index()
    {
        $template = [];
        return view('',$template);
    }

    public function lists($dir = '/', $limit = 10)
    {
        $dir = trim($dir);
        $limit = intval($limit);

        // 如果不是以斜杠开头添加一个斜杠，与下面的去除目录中的/对应
        if(substr( $dir, 0, 1 ) != '/') {
            $dir = '/' . $dir;
        }

        // 先获得当前所在目录
        $temp_dir_id = Db::table('tp_directory')->where('name',$dir)->value('id');
        // 获取文件夹下所有的文件夹
        $dirs = Db::table('tp_directory')->where('pid',$temp_dir_id)->select();
        // 获取文件夹下所有文件
        $files = Db::table('tp_files')->where('dir',$dir)->select();

        $dirs = $this->handleDirs($dirs);
        $files = $this->handleFiles($files);

        $data = [];
        if(!empty($dirs) && !empty($files)) {
            $data = array_merge($dirs, $files);
        } else {
            $data = (empty($dirs)) ? $files : $dirs;
            $data = (empty($data)) ? array() : $data;
        }



        return json(['code' => 200, 'msg' => 'sucess','count'=>count($data), 'dir'=>$dir, 'data' => $data]);
    }

    private function getIcon($file)
    {
        $ext = $this->getExt($file);
        $ext = strtolower($ext);
        switch ($ext) {
            case 'jpg':
                $ext_ret = $this->HOST . $file;
                break;
            default :
                $ext_ret = '/static/images/ext/'.$ext.'.png';
        }
        return $ext_ret;
    }

    private function getExt($file)
    {
        return pathinfo($file,PATHINFO_EXTENSION);
    }

    private function handleFiles($files = array())
    {
        if(empty($files))
            return null;

        $temp = [];

        foreach ($files as $k => $v) {
            $temp_arr = ['name' => trim($v['name'],'/'), 'last_time' => date('Y-m-d H:i:s',$v['putTime']),
                'mimeType' => $v['mimeType'], 'fsize' => $v['fsize']
            ];
            $temp_arr['icon'] = $this->getIcon($v['name']);
            $temp[] = $temp_arr;
        }
        return $temp;
    }

    /**
     * 处理从数据库中读取的文件目录列表
     * @param array $dirs
     * @return array
     */
    private function handleDirs($dirs = array())
    {
        if(empty($dirs))
            return null;

        $temp = [];

        foreach ($dirs as $k => $v) {
            // 去除文件目录中开头的/
            $name = trim($v['name'],'/');

            $temp_arr = ['name' => $name, 'last_time' => date('Y-m-d H:i:s',$v['last_time'])];
            $temp_arr['icon'] = '/static/images/dir2.png';
            $temp_arr['mimeType'] = 'directory';
            $temp[] = $temp_arr;
        }
        return $temp;
    }






    /********************************* 定时任务 ***********************/

    public function crontab($prefix = '', $limit = 3)
    {
        $prefix = trim($prefix);
        $limit = intval($limit);
        $accessKey = config('qiniu.accessKey');
        $secretKey = config('qiniu.secretKey');
        $auth = new Auth($accessKey, $secretKey);
        $bucketMgr = new BucketManager($auth);

        // 要列取的空间名称
        $bucket = config('qiniu.bucket');

        // 通过缓存记录读取上次所处位置
        $marker = cache('marker');

        list($iterms, $marker, $err) = $bucketMgr->listFiles($bucket, $prefix, $marker, $limit);
        cache('marker',$marker);
        if ($err !== null) {
            return json(['code' => 200,'msg' => $err]);
        } else {
            foreach ($iterms as $k => $v) {
                $this->expleFile($v);
            }

//            return json($iterms);
        }
    }

    private function expleFile($item = array())
    {
        if(empty($item['key']))
            return null;

        $fileInfo = pathinfo($item['key']);

        // 检查目录是否已存在并取回目录
        $dir = $this->checkDir($fileInfo['dirname']);
        // 检查文件是否已存在
        $file = $this->checkFile($fileInfo['dirname'], $fileInfo['basename'], $item);

        dump($dir);
        dump($file);
    }

    private function checkFile($dir = '',$key = '', $item = array())
    {
        if(empty($key))
            return null;

        defined('NOW_TIME') or define('NOW_TIME',time());

        $dir = (empty($dir) || $dir == '.') ? '/' : '/'.$dir;

        $data = Db::table('tp_files')->where(['name' => $key, 'dir' => $dir])->find();
        if(empty($data)) {
            $data = ['name' => $key, 'dir' => $dir,
                'hash'=> $item['hash'], 'fsize' => $item['fsize'], 'mimeType' => $item['mimeType'], 'putTime' => ($item['putTime']/10000000),
                'create_time' => NOW_TIME, 'update_time' => NOW_TIME
            ];
            $fileId = Db::table('tp_files')->insertGetId($data);
            $data['id'] = $fileId;
            return $data;
        }
        return $data;
    }

    private function checkDir($dir = '', $item = array(), $delimiter = '/')
    {
        defined('NOW_TIME') or define('NOW_TIME',time());
        // 先检查是否顶级目录
        if($dir == '.'){
            $dir = $delimiter;
            $dirInfo = Db::table('tp_directory')->where('name',$dir)->find();
            if(empty($dirInfo)) {
                // 查不到顶级菜单就创建
                $dirInfo = $this->createFistDir($item, $delimiter);
            }
            return $dirInfo;
        }

        // 直接检查相关目录
        $dirInfo = Db::table('tp_directory')->where('name',$dir)->find();
        if(!empty($dirInfo))
            return $dirInfo;

        // 拆分目录
        $dirArray = explode('/',$dir);
        $temp_dir = '';
        $temp_dir_info = '';
        $temp_dir_id = Db::table('tp_directory')->where('name',$delimiter)->value('id');

        // 查不到顶级菜单就创建
        if(empty($temp_dir_id)) {
            $first = $this->createFistDir($item, $delimiter);
            $temp_dir_id = $first['id'];
        }

        // 循环分割后的目录
        foreach ($dirArray as $k => $v) {
            $temp_dir .=  $delimiter .$v;
            $tempDirInfo = Db::table('tp_directory')->where('name',$temp_dir)->find();
            // 如果这个目录已经存在
            if(!empty($tempDirInfo)) {
                $temp_dir_info = $tempDirInfo;
            } else {
                $time = (empty($item)) ? NOW_TIME : ($item['putTime']/10000000);
                $data = ['name' => $temp_dir, 'pid' => $temp_dir_id, 'last_time' => $time, 'create_time' => NOW_TIME, 'update_time' => NOW_TIME];
                $dirId = Db::table('tp_directory')->insertGetId($data);
                $temp_dir_id = $dirId;
                $data['id'] = $dirId;
                $temp_dir_info = $data;
            }
        }
        return $temp_dir_info;
    }

    private function createFistDir($item = array(), $delimiter = '/')
    {
        $time = (empty($item)) ? NOW_TIME : ($item['putTime']/10000000);
        $data = ['name' => $delimiter, 'pid' => 0, 'last_time' => $time, 'create_time' => NOW_TIME, 'update_time' => NOW_TIME];
        $dirId = Db::table('tp_directory')->insertGetId($data);
        $data['id'] = $dirId;
        return $data;
    }
}
