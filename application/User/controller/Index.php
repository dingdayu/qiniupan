<?php
namespace app\user\controller;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use think\Db;

class Index extends Common
{
    public function index()
    {
        $template = [];
        return view('',$template);
    }

    public function uptoken()
    {
        $accessKey = config('qiniu.accessKey');
        $secretKey = config('qiniu.secretKey');
        $auth = new Auth($accessKey, $secretKey);
        $bucket = config('qiniu.bucket');
        // 上传文件到七牛后， 七牛将文件名和文件大小回调给业务服务器

//        $policy = array(
//            'callbackUrl' => 'http://your.domain.com/callback.php',
//            'callbackBody' => 'filename=$(fname)&filesize=$(fsize)'
//        );
        $uptoken = $auth->uploadToken($bucket);

        return json(['uptoken'=>$uptoken]);
    }

    /**
     * 前端用来获取文件列表的访问入口
     * @param string $dir
     * @param int $limit
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
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

    /**
     * 获取文件类型的预览图标链接
     * @param $file string 文件名
     * @param $fileUrl string 七牛文件地址
     * @return string Url链接
     */
    private function getIcon($file, $fileUrl = '')
    {
        $ext = $this->getExt($file);
        $ext = strtolower($ext);
        switch ($ext) {
            case 'jpg':
                $ext_ret = $fileUrl;
                break;
            default :
                $ext_ret = '/static/images/ext/'.$ext.'.png';
        }
        return $ext_ret;
    }

    /**
     * 获取文件后缀
     * @param $file
     * @return mixed
     */
    private function getExt($file)
    {
        return pathinfo($file,PATHINFO_EXTENSION);
    }

    /**
     * 处理从数据库中读取的文件列表
     * @param array $files 文件数组
     * @return array|null 处理后前端可用的数组
     */
    private function handleFiles($files = array())
    {
        if(empty($files))
            return null;

        $temp = [];

        foreach ($files as $k => $v) {
            $temp_arr = ['name' => trim($v['name'],'/'), 'last_time' => date('Y-m-d H:i:s',$v['putTime']),
                'mimeType' => $v['mimeType'], 'fsize' => $v['fsize']
            ];

            $temp_url = 'http://' . config('qiniu.domain');
            $temp_url .= (empty($v['dir'])) ? : $v['dir'];
            $temp_url .= (empty($v['name'])) ? : '/'.$v['name'];
            $temp_arr['url'] = $temp_url;

            $temp_arr['icon'] = $this->getIcon($v['name'], $temp_url);
            $temp[] = $temp_arr;
        }
        return $temp;
    }

    /**
     * 处理从数据库中读取的文件目录列表
     * @param array $dirs 目录数组
     * @return array|null 处理后前端可用的数组
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

}
