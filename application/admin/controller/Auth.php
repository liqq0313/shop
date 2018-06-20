<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use app\admin\model\UserAdmin;
class Auth extends Controller
{
	protected $is_check_login = [''];
	public function _initialize()
	{
		$this->is_login = session('?admin');
		if ($this->is_login) {
			$this->assign('adminName' , session('admin')['name']);
		}

		if (!$this->is_login && (in_array(Request::instance() , $this->is_check_login) || $this->is_check_login[0] == '*')) {

			$this->error('您没有登录请登录',url('admin/auth/login'));
		}

	}

	public function loginout()
	{
		session('admin',null);
		$this->success('您退出成功' , 'admin/auth/login');
	}

	public function login()
	{
		return $this->fetch();
	}

	public function doLogin()
	{
		if (empty($_POST)) {
			exit;
		}
		$data['password'] = md5(input('post.userpwd'));
		$data['name'] = input('post.username');

		$result = UserAdmin::get($data);
		if ($result) {
			session('admin' , $result->toArray());
			return json(['status'=>1,'msg'=>'登录成功','url'=>url('admin/index/index')]);
		}else{
			return json(['status'=>0,'msg'=>'登录失败','url'=>url('admin/auth/login')]);
		}
	}
}