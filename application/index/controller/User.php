<?php
namespace app\index\controller;
class User extends Auth
{	
	//个人中心-我的u袋-欢迎页
	public function welcome()
	{
		//$this->checkLogin();

		return $this->fetch();
	}

	//我的订单
	public function order()
	{
		//$this->checkLogin();

		return $this->fetch();
	}

	//收藏
	public function collection()
	{
		return $this->fetch();
	}

	//退款/退货
	public function refund()
	{
		return $this->fetch();
	}

}