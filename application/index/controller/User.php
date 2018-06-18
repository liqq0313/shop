<?php
namespace app\index\controller;
class User extends Auth
{	
	//没有登录状态自动跳转到登录界面
	protected $is_check_login = ['*'];
	//个人中心-我的u袋-欢迎页
	public function welcome()
	{
		return $this->fetch();
	}

	//我的订单
	public function order()
	{
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