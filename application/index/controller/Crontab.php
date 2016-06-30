<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2016 http://www.mocentre.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: dingdayu <614422099@qq.com>
// +----------------------------------------------------------------------
// | DATE: 2016/6/30 14:49
// +----------------------------------------------------------------------

namespace app\index\controller;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use think\Db;

class Crontab
{
    public function index($prefix = '', $limit = 10)
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

    /**
     * 自动处理一条记录中的目录和文件
     * @param array $item
     * @return null
     */
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

    /**
     * 检查数据库中此文件是否存在，不存在则添加
     * @param string $dir
     * @param string $key
     * @param array $item
     * @return array|false|null|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
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

    /**
     * 检查数据库中此目录是否存在，不存在则添加
     * @param string $dir
     * @param array $item
     * @param string $delimiter
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
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

    /**
     * 创建顶级目录
     * @param array $item
     * @param string $delimiter
     * @return array
     */
    private function createFistDir($item = array(), $delimiter = '/')
    {
        $time = (empty($item)) ? NOW_TIME : ($item['putTime']/10000000);
        $data = ['name' => $delimiter, 'pid' => 0, 'last_time' => $time, 'create_time' => NOW_TIME, 'update_time' => NOW_TIME];
        $dirId = Db::table('tp_directory')->insertGetId($data);
        $data['id'] = $dirId;
        return $data;
    }

}