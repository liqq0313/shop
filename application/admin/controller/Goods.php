<?php
namespace app\admin\controller;
use app\index\model\Goods as GoodsModel;
use app\index\model\GoodsToCategory;
use think\Request;
use think\Image;
use app\index\model\GoodsImage;
class Goods extends Auth
{
	protected $is_check_login = ['*'];
	//商品管理
	public function index()
	{
		return $this->fetch();
	}

	public function add()
	{
		$this->assign('title' , '新增');
		return $this->fetch('edit');
	}

	public function edit()
	{
		$request = Request::instance();
		$param = $request->param();

		$goods = GoodsModel::get($param['id']);
		//$goods['thumb'] = resize($goods['image'] , 360);
		$this->assign('title' , '编辑');
		$this->assign('goods' , $goods);
		
		return $this->fetch();
	}

	public function del()
	{
		$request = Request::instance();
		$param = $request->param();
		return json($param);
	}

	public function showGoods()
	{
		$request = Request::instance();
		$param = $request->param();

		$start = $param['start'];
		//页面显示数
		$length = $param['length'];

		$data = GoodsModel::limit("$start,$length")->select();
		foreach ($data as $key => $value) {
			if ($value->category) {
				$str = $value->category[0]->name;
				$data[$key]->cate = $str;
			}
		}
		
		$result['draw'] = $param['draw'];
		$result['recordsTotal'] =  GoodsModel::count();
  		$result['recordsFiltered'] =  GoodsModel::count();
  		$result['data'] = $data;
		return json($result);
	}

	public function save(GoodsModel $goods)
	{
		$request = Request::instance();
		$file = $request->file('image');
		$data=$request->param();
		if (empty($data['category'])) {
      		return json(['status'=>0,'msg'=>'商品必须选择分类']);	
      	}
		
		if(isset($file)){
			//封面图
			$filePath = 'image/goods';
	        $filePaths = ROOT_PATH . 'public' . DS . 'upload' . DS .$filePath;
		    if(!file_exists($filePaths)){
		        mkdir($filePaths,0777,true);
		    }
		    $info = $file->move($filePaths);
		    if($info){
		        $imgpath = 'static/upload/'.$filePath.'/'.$info->getSaveName();
		        $imgpath = str_replace("\\", "/", $imgpath);
		        $image = \think\Image::open($imgpath);
		        $date_path = 'static/upload/'.$filePath.'/thumb/180';
		        if(!file_exists($date_path)){
		          mkdir($date_path,0777,true);
		        }
		        $thumb_path = $date_path.'/'.$info->getFilename();
		        $image->thumb(180,180)->save($thumb_path);
		        $data['image'] = $imgpath;
	  		}else{  
	            // 上传失败获取错误信息  
	     		echo $file->getError();  
	   		}  
      	}

      	if (empty($data['goods_id'])) {
      		$data['create_time']=time();  

      		$result = $goods->allowField(true)->save($data);

      		$goods_id = $goods->getLastInsID();

      		$msg = '新增';
      	}else{
      		$data['update_time']=time();

      		$result = $goods->allowField(true)->save($data , ['goods_id'=>$data['goods_id']]);

      		$goods_id = $data['goods_id'];

      		$msg = '修改';
      	}
      	
      	if (!$result) {
      		return json(['status'=>0,'msg'=>$msg.'失败']);	
      	}

      	if ($data['category'] && $data['goods_id']) {
      		GoodsToCategory::where('goods_id' , $goods_id )->update(['category_id'=>$data['category']]);
      	}else if($data['category'] && empty($data['goods_id'])){
      		GoodsToCategory::create(['goods_id'=>$goods_id , 'category_id'=>$data['category']]);
      	}

      	return json(['status'=>1,'msg'=>$msg.'成功']);	
		
	}

	public function upload()
	{
		$request = Request::instance();
		$param = $request->param();
		$date = date('Ymd');
		//fileupload($param , "upload/image/goods/$date");
		/*if ($param['id']) {
			GoodsImage::where('goods_id',$)->delete();
		}*/

		//GoodsImage::create()

	}
}