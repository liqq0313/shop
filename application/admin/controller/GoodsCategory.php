<?php
namespace app\admin\controller;
class GoodsCategory extends Auth
{
	//商品分类管理
	public function index()
	{
		return $this->fetch();
	}

}