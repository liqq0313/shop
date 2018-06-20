<?php
namespace app\index\controller;
use app\index\model\GoodsCategory;
use app\index\controller\Common;
use think\Request;
class Category
{
	public function getCateName()
	{
		$data = GoodsCategory::column('category_id,pid,name,path');
		$data = Common::getTree($data , 0 , 'category_id');
		return json($data);
	}

	public function getChildCate()
	{
		$id = Request::instance()->param('id');

		if (isset($id)) {
			$data = GoodsCategory::all(['pid'=>$id]);
		}else{
			$data = GoodsCategory::column('category_id,pid,name');
		}
		return json($data);
	}

	public function getGoodsIndex()
	{
		
	}
	
}