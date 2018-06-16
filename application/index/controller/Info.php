<?php
namespace app\index\controller;
class Info extends Auth
{
	//个人资料
	public function setting()
	{
		return $this->fetch();
	}

	//资金管理
	public function treasurer()
	{
		return $this->fetch();
	}

	//积分
	public function integral()
	{
		return $this->fetch();
	}

	//收货地址
	public function address()
	{
		return $this->fetch();
	}

	//优惠劵
	public function coupon()
	{
		return $this->fetch();
	}

	//支付密码
	public function paypwd()
	{
		return $this->fetch();
	}

	//修改密码
	public function updatepwd()
	{
		return $this->fetch();
	}

}