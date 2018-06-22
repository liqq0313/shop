<?php
namespace app\admin\controller;
class GoodsCategory extends Auth
{
	protected $is_check_login = ['*'];
	public function index()
	{
		echo '商品分类';
		//return $this->fetch();
	}
}