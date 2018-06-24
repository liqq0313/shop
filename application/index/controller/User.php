<?php
namespace app\index\controller;
use app\index\model\Order;
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
		$uid = session('user')['u_id'];
		$data = Order::where(['user_id'=>$uid])->select();

		$this->assign('data' , $data);
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