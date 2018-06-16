<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\User;
use think\Validate;
use think\Request;

class Auth extends Controller
{
	//protected $is_check_login = [''];

	public $is_login;

	public function _initialize()
	{
		$this->is_login = session('?user');
		/*if (!$this->is_login && (in_array(Request::instance() , $this->is_check_login) || $this->is_check_login[0] == '*')) {

			$this->error('您没有登录请登录',url('index/auth/login'));
		}*/

	}

	//检测是否登录，如果没有登录跳转到登录界面
	public function checkLogin()
	{
		if(!$this->is_login){
			$this->redirect('index/auth/login');
		}
	}

	public function loginout()
	{
		//session(null);
		//echo '退出成功';
	}

	public function test()
	{
		var_dump(session('user'));
	}
	public function login()
	{
		return $this->fetch();
	}

	public function doLogin()
	{
		/*
		$info  = User::where(['username'=>input('post.username'),'password'=>md5(input('post.password'))])->find();

		if ($info) {
			//如果数据有了说明数据库存在 说明成功 别忘记存session
			session('user' , $info->toArray());
			echo '登录成功';

		} else {
			echo '登录失败';
		}
*/

	}
	public function register()
	{
		return $this->fetch();
	}

	public function doRegister()
	{
		/*
		$validate = new Validate([
			'username' => 'require|max:25',
			
			'email' => 'email'
		]);

		$data = [
			'username' => input('post.username'),
			
			'email' => input('post.email')
		];




		if (!$validate->check($data)) {

			return json(['status'=>0 ,'msg' => $validate->getError()]);

		} else {
			//echo 2;

			$data['password'] = md5(input('post.password'));


			$result = User::create($data);


		

			if ($result) {
				return json(['status'=>1 ,'msg' => '注册成功' , 'url' =>url('index/index/index')]);
			} else {
				return json(['status'=>0 ,'msg' => '注册失败' , 'url' =>url('index/index/index')]);
			}


		}
		*/

	}

	public function getpass()
	{
		return $this->fetch();
	}
}