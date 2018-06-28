<?php
namespace app\admin\controller;
use app\index\model\Goods as GoodsModel;
use app\index\model\GoodsToCategory;
use think\Request;
use app\index\model\GoodsImage;
use app\index\model\GoodsDetail;
use app\admin\model\UploadFiles;
class Goods extends Base
{
	protected $is_check_login = ['*'];
	//商品管理
	public function index()
	{
		return $this->fetch();
	}

	public function addGoods()
	{
		if (request()->post()) {
			$request = Request::instance();
			$file = $request->file("image");
			
			$fileSlider = $request->file("imageslider");
			$imagedeta = $request->file('imagedeta');
			$imagedetaTxt = input('post.imagedetaTxt/a');
			/*var_dump($imagedeta);var_dump($imagedetaTxt);
			if (count($imagedetaTxt)!=count($imagedeta)) {
				return json(['status'=>0,'msg'=>'商品详情必须有描述']);	
			}
			return 1;*/
			$data=$request->param();
			$model = new UploadFiles();
			if (empty($data['category'])) {
	      		return json(['status'=>0,'msg'=>'商品必须选择分类']);	
	      	}
			
			if(isset($file)&&!empty($file)){
				//封面图
				$data['image'] = $model->uploadOne($file); 
	      	}
	      	
	      	$goods = new GoodsModel();

      		$data['create_time']=time();
      		
      		$result = $goods->allowField(true)->save($data);
      		$msg = '新增';	      	
	      	if (!$result) {
	      		return json(['status'=>0,'msg'=>$msg.'失败']);	
	      	}
      		$goods_id = $goods['goods_id'];

      		if(isset($fileSlider)&&!empty($fileSlider)){
				//轮播图
				$slider = $model->uploadAll($fileSlider);
				foreach ($slider as $key=>$value) {
					$insert_data = [];
					$insert_data['goods_id'] = $goods_id;
					$insert_data['image'] = $value['s'];
					$insert_data['data_image'] = $value['b'];
					$insert_data['sort_order'] = $key+1;
					GoodsImage::create($insert_data);
				}
	      	}
	      	if(isset($imagedeta)&&!empty($imagedeta)){
	      		//详情图
	      		$deta = $model->uploadAll($imagedeta , 'image/goods' , false);
	      		if (!is_array($deta)) {
	      			return json(['status'=>0,'msg'=>$deta]);
	      		}
	      		foreach ($deta as $key => $value) {
	      			$insert_data = [];
	      			$insert_data['goods_id'] = $goods_id;
					$insert_data['image'] = $value['b'];
					$insert_data['sort'] = $key+1;
					$insert_data['description'] = $imagedetaTxt[$key];
					GoodsDetail::create($insert_data);
	      		}
	      	}

      		
	      	if ($data['category'] && $goods_id) {
	      		$tmp = [];
	      		$tmp['goods_id'] = $goods_id;
	      		$tmp['category_id'] = $data['category'];
	      		GoodsToCategory::create($tmp);
	      	}

	      	return json(['status'=>1,'msg'=>$msg.'成功']);

			
    	} else {
    		$this->assign('title' , '新增');
			return $this->fetch('goods-edit');
    	}
	}

	public function updateGoods()
	{
		if (request()->post()) {
			$request = Request::instance();
			$file = $request->file("image");
			
			$fileSlider = $request->file("imageslider");
			$imagedeta = $request->file('imagedeta');
			$imagedetaTxt = input('post.imagedetaTxt/a');
			/*var_dump($imagedeta);var_dump($imagedetaTxt);
			if (count($imagedetaTxt)!=count($imagedeta)) {
				return json(['status'=>0,'msg'=>'商品详情必须有描述']);	
			}
			return 1;*/
			$data=$request->param();
			$model = new UploadFiles();
			if (empty($data['category'])) {
	      		return json(['status'=>0,'msg'=>'商品必须选择分类']);	
	      	}
			
			if(isset($file)&&!empty($file)){
				//封面图
				$data['image'] = $model->uploadOne($file); 
	      	}

	      	if(isset($fileSlider)&&!empty($fileSlider)){
				//轮播图
				$slider = $model->uploadAll($fileSlider);
				GoodsImage::where('goods_id',$data['goods_id'])->delete();
				foreach ($slider as $key=>$value) {
					$insert_data = [];
					$insert_data['goods_id'] = $data['goods_id'];
					$insert_data['image'] = $value['s'];
					$insert_data['data_image'] = $value['b'];
					$insert_data['sort_order'] = $key+1;
					GoodsImage::create($insert_data);
				}
	      	}
	      	if(isset($imagedeta)&&!empty($imagedeta)){
	      		//详情图
	      		$deta = $model->uploadAll($imagedeta , 'image/goods' , false);
	      		if (!is_array($deta)) {
	      			return json(['status'=>0,'msg'=>$deta]);
	      		}
	      		GoodsDetail::where('goods_id',$data['goods_id'])->delete();
	      		foreach ($deta as $key => $value) {
	      			$insert_data = [];
	      			$insert_data['goods_id'] = $data['goods_id'];
					$insert_data['image'] = $value['b'];
					$insert_data['sort'] = $key+1;
					$insert_data['description'] = $imagedetaTxt[$key];
					GoodsDetail::create($insert_data);
	      		}
	      	}
	      	
	      	$goods = new GoodsModel();

      		$data['update_time']=time();
      		
      		$result = $goods->allowField(true)->save($data , ['goods_id'=>$data['goods_id']]);
      		$goods_id = $data['goods_id'];
      		$msg = '修改';	      	
	      	if (!$result) {
	      		return json(['status'=>0,'msg'=>$msg.'失败']);	
	      	}

	      	if ($data['category'] && $data['goods_id']) {
	      		GoodsToCategory::where('goods_id' , $goods_id )->update(['category_id'=>$data['category']]);
	      	}else if($data['category'] && empty($data['goods_id'])){
	      		GoodsToCategory::create(['goods_id'=>$goods_id , 'category_id'=>$data['category']]);
	      	}

	      	return json(['status'=>1,'msg'=>$msg.'成功']);
    	} else {
    		$request = Request::instance();
			$param = $request->param();

			$goods = GoodsModel::get($param['id']);
			//$goods['thumb'] = resize($goods['image'] , 360);
			$this->assign('title' , '编辑');
			$this->assign('goods' , $goods);
			
			return $this->fetch('goods-edit');
    	}
	}

	public function delGoods()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$res = GoodsModel::where('goods_id',$id)->delete();
		GoodsImage::where('goods_id',$id)->delete();
		GoodsDetail::where('goods_id',$id)->delete();
		GoodsToCategory::where('goods_id',$id)->delete();
		if ($res) {
			return json(['status'=>1,'msg'=>'删除成功']);
		}else{
			return json(['status'=>0,'msg'=>'删除失败']);
		}
		
	}

	public function showGoods()
	{
		$request = Request::instance();
		$param = $request->param();
		
		$page = $param['page'];
		//页面显示数
		$num = $param['num'];
		$start = ($page-1)*$num;
		$data = GoodsModel::limit("$start,$num")->select();

		$total = GoodsModel::count();
		$pageCount = ceil($total/$num);
		$prev = $page-1;
		$next = $page+1;
		if ($prev<=1) {
			$prev=1;
		}
		if ($next>=$pageCount) {
			$next=$pageCount;
		}
		foreach ($data as $key => $value) {
			if ($value->category) {
				$str = $value->category[0]->name;
				$data[$key]->cate = $str;
			}
		}
		return json(['data'=>$data,'total'=>$total,'pageCount'=>$pageCount,'prev'=>$prev,'next'=>$next,'page'=>$page]);
	}

	public function save($request , GoodsModel $goods)
	{
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
		        $imgpath = 'upload/'.$filePath.'/'.$info->getSaveName();
		        $imgpath = str_replace("\\", "/", $imgpath);
		        $image = \think\Image::open($imgpath);
		        $date_path = 'upload/'.$filePath.'/thumb/180';
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