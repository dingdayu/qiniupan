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

    public function lists($prefix = '', $limit = 10)
    {
        $prefix = trim($prefix);
        $limit = intval($limit);
        $accessKey = 'ScLfBuX6OeDNb0lVvk5qrCbgEjneoDpgvR9GfAun';
        $secretKey = 'lcfLxYhNBi2wrbctTCtv4l46m77kLUgVAlcjWGbo';
        $auth = new Auth($accessKey, $secretKey);
        $bucketMgr = new BucketManager($auth);

        // Ҫ��ȡ�Ŀռ�����
        $bucket = 'qzonebackgroundmusic';

        $marker = '';

        $file_list = [];

        list($iterms, $marker, $err) = $bucketMgr->listFiles($bucket, $prefix, $marker, $limit);
        if ($err !== null) {
            return json(['code' => 200,'msg' => $err]);
        } else {
            foreach($iterms as $v => $k) {
                if(!empty($prefix)) {
                    $file = ltrim($k['key'],$prefix.'/');
                } else {
                    $file = $k['key'];
                }
                $file = explode('/',$file);
                if(count($file) > 1) {
                    if(!empty($prefix)){
                        $prefix = $prefix.'/'.$file[0];
                    }
                    $file_list[$file[0]]['key'] = empty($prefix) ? $file[0] : $prefix;
                    $file_list[$file[0]]['fileName'] = $file[0];
                    $file_list[$file[0]]['fsize'] = (empty($file_list[$file[0]]['fsize'])) ? $file_list[$file[0]]['fsize'] = $k['fsize'] : $file_list[$file[0]]['fsize'] + $k['fsize'];
                    $putTime = (!empty($file[$file[0]]['putTime']) && strtotime($file[$file[0]]['putTime']) > $k['putTime']) ?: $k['putTime'];
                    $putTime = date('Y-m-d H:i:s',$putTime/10000000);
                    $file_list[$file[0]]['putTime'] = $putTime;
                    $file_list[$file[0]]['icon'] = '/static/images/dir2.png';
                    $file_list[$file[0]]['mimeType'] = 'directory';
                } else {
                    $k['fileName'] = pathinfo($k['key'],PATHINFO_BASENAME);
                    $file_list[$k['key']] = $k;
                    $file_list[$k['key']]['icon'] = $this->getIcon($k['key']);
                    $file_list[$k['key']]['putTime'] = date('Y-m-d H:i:s',$k['putTime']/10000000);
                }
            }
            return json(['code' => 200, 'msg' => 'sucess','count'=>count($file_list), 'dir'=>$prefix, 'data' => $file_list]);
        }
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

    public function crontab($prefix = '', $limit = 3)
    {
        $prefix = trim($prefix);
        $limit = intval($limit);
        $accessKey = config('qiniu.accessKey');
        $secretKey = config('qiniu.secretKey');
        $auth = new Auth($accessKey, $secretKey);
        $bucketMgr = new BucketManager($auth);

        // Ҫ��ȡ�Ŀռ�����
        $bucket = config('qiniu.bucket');

        // ͨ�������¼��ȡ�ϴ�����λ��
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

        // ���Ŀ¼�Ƿ��Ѵ��ڲ�ȡ��Ŀ¼
        $dir = $this->checkDir($fileInfo['dirname']);
        // ����ļ��Ƿ��Ѵ���
        $file = $this->checkFile($fileInfo['dirname'], $fileInfo['basename'], $item);

        dump($dir);
        dump($file);
    }

    private function checkFile($dir = '',$key = '', $item = array())
    {
        if(empty($key))
            return null;

        defined('NOW_TIME') or define('NOW_TIME',time());

        $dir = (empty($dir) || $dir == '.') ? '/' : $dir;

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
        // �ȼ���Ƿ񶥼�Ŀ¼
        if($dir == '.'){
            $dir = $delimiter;
            $dirInfo = Db::table('tp_directory')->where('name',$dir)->find();
            if(empty($dirInfo)) {
                // �鲻�������˵��ʹ���
                $dirInfo = $this->createFistDir($item, $delimiter);
            }
            return $dirInfo;
        }

        // ֱ�Ӽ�����Ŀ¼
        $dirInfo = Db::table('tp_directory')->where('name',$dir)->find();
        if(!empty($dirInfo))
            return $dirInfo;

        // ���Ŀ¼
        $dirArray = explode('/',$dir);
        $temp_dir = '';
        $temp_dir_info = '';
        $temp_dir_id = Db::table('tp_directory')->where('name',$delimiter)->value('id');

        // �鲻�������˵��ʹ���
        if(empty($temp_dir_id)) {
            $first = $this->createFistDir($item, $delimiter);
            $temp_dir_id = $first['id'];
        }

        // ѭ���ָ���Ŀ¼
        foreach ($dirArray as $k => $v) {
            $temp_dir .=  $delimiter .$v;
            $tempDirInfo = Db::table('tp_directory')->where('name',$temp_dir)->find();
            // ������Ŀ¼�Ѿ�����
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
