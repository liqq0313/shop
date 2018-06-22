<?php
namespace app\admin\controller;
class User extends Auth
{
	protected $is_check_login = ['*'];
	public function index()
	{
		echo '前台用户';
		//return $this->fetch();
	}

	public function admin()
	{
		echo '后台用户';
		//return $this->fetch();
	}
}