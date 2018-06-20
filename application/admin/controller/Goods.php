<?php
namespace app\admin\controller;
use app\index\model\Goods;
use app\index\model\GoodsCategory;
use think\Request;
class Goods extends Auth
{
	//商品管理
	public function index()
	{
		return $this->fetch();
	}

	public function add()
	{
		echo '添加';
	}

	public function showGoods()
	{
		$request = Request::instance();
		$param = $request->param();
		//当前页
		$page = $param['page'];
		//每页显示数
		$num = $param['num'];

		$start = ($page-1)*$num;
		$data = Goods::limit("$start,$num")->select();
		return json($data);
	}

}