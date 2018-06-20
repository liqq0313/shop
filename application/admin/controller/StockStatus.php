<?php
namespace app\admin\controller;
class StockStatus extends Auth
{
	//库存管理
	public function index()
	{
		return $this->fetch();
	}

}