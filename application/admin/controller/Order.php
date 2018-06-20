<?php
namespace app\admin\controller;
class Order extends Auth
{
	//订单管理
	public function index()
	{
		return $this->fetch();
	}

	//物流管理
	public function logistics()
	{
		return $this->fetch();
	}

	//商品分类管理
	public function category()
	{

	}
}