<?php
namespace app\admin\controller;
class Index extends Auth
{
	protected $is_check_login = ['*'];
	public function index()
	{
		return $this->fetch();
	}

	public function rindex()
	{
		return $this->fetch();
	}
}