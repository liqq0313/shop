<?php
namespace app\index\controller;
class Index extends Auth
{
	protected $is_check_login = [''];
	public function index()
	{
		$this->assign('isLogin' , $this->is_login);
		return $this->fetch();
	}
}