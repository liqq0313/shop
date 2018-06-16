<?php
namespace app\index\controller;
use app\index\model\GoodsCategory;
use app\index\controller\Common;
class Category extends Auth
{
	public function getCateName()
	{
		$data = GoodsCategory::column('id,pid,name');
		$data = Common::getTree($data);
		return json($data);
	}


	
}