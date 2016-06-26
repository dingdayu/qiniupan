<?php
namespace app\user\controller;

use Qiniu\Auth;
use Qiniu\Storage\BucketManager;

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

        // 要列取的空间名称
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
}
