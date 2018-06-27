<?php
namespace app\admin\model;
use think\Model;       // 使用Model
use think\File;           // 使用文件上传类
use think\Validate;    // 使用文件上传验证
use think\Request;   // 接值时使用
use think\Image;

/**
 * 封装文件上传model
 */
class UploadFiles extends Model
{
        
    /**
     * 单文件上传
     * @param  [type] $file   [description]
     * @return [type] string  [description]
     */
    public function uploadOne($file,$filePath='image/goods')
    {
        $filePath = rtrim($filePath,'/');
        $filePath = ROOT_PATH . 'public/static/' . DS . 'upload' . DS .$filePath;
        $rootPath = Request::instance()->root(true);         // 项目根路径
        if (!file_exists($filePath)) {
            mkdir($filePath,0777,true);
        }else{          
            $info = $file
                    ->validate([
                        'size'=>1024*1024*2,
                        'ext'=>'jpg,png,gif,jpeg,bmp'
                      ])
                    ->move($filePath);
            if($info){
                // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                $imgpath = $filePath.'/'.$info->getSaveName();
                $pos = strpos($imgpath, 'upload');
                $imgpath = 'static/' . substr($imgpath, $pos);
                $imgpath = str_replace("\\", "/", $imgpath);
                return  $imgpath;    // 返回带域名的图片路径
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                //return $info->getFilename();
            }else{
                return $file->getError();
            }
        }
    }

    /**
     * 多文件上传
     * @param  [type] $files   [description]
     * @return [type] array    [description]
     */
    public function uploadAll($files,$filePath='image/goods/slider',$isthumb=true)
    {
        $filePath = rtrim($filePath,'/');
        $filePath = ROOT_PATH . 'public/static/' . DS . 'upload' . DS .$filePath;
        $rootPath = Request::instance()->root(true);         // 项目根路径
        $array = array();
        foreach ($files as $key => $file) {
            if (!file_exists($filePath)) {
                mkdir($filePath,0777,true);
            }else{          
                $info = $file
                        ->validate([
                            'size'=>1024*1024*2,
                            'ext'=>'jpg,png,gif,jpeg,bmp'
                          ])
                        ->move($filePath);
                if($info){
                    // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                    $imgpath = $filePath.'/'.$info->getSaveName();
                    $pos = strpos($imgpath, 'upload');
                    $imgpath = 'static/' . substr($imgpath, $pos);
                    $imgpath = str_replace("\\", "/", $imgpath);

                    if($isthumb){
                        $image = \think\Image::open($filePath.'/'.$info->getSaveName());
                        $date_path = $filePath.'/'.date('Ymd').'/thumb';
                        
                        if(!file_exists($date_path)){
                            mkdir($date_path,0777,true);
                        }

                        $thumb_path = $date_path.'/'.$info->getFilename();
                        $image->thumb(430,430)->save($thumb_path);
                        $pos = strpos($thumb_path, 'upload');
                        $thumb_path = 'static/' . substr($thumb_path, $pos);
                        $thumb_path = str_replace("\\", "/", $thumb_path);

                        $arr = ['b'=>$imgpath,'s'=>$thumb_path];
                    }else{
                        $arr = ['b'=>$imgpath];
                    }
                    
                    // 返回带域名的图片路径
                    //array_push($array,$imgPath);
                    $array[] = $arr;
                    // 输出 42a79759f284b767dfcb2a0197904287.jpg
                    //return $info->getFilename();
                }else{
                    return $file->getError();
                }
            }
        }
        return $array;
    }


}
