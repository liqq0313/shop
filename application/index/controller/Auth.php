<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\User;
use think\Validate;
use think\Request;
use lib\Ucpaas;
use app\index\model\Cart;
use think\Cookie;
class Auth extends Controller
{
	protected $is_check_login = [''];

	public $is_login;

	public function _initialize()
	{
		$this->is_login = session('?user');
		if ($this->is_login) {
			$this->assign('username' , session('user')['username']);
			$this->assign('grade' , session('user')['grade']);
			if(!empty($_COOKIE['shop_cart_info'])){
				Cookie::delete('shop_cart_info');
			}
		}

		$this->assign('isLogin' , $this->is_login);

		if (!$this->is_login && (in_array(Request::instance() , $this->is_check_login) || $this->is_check_login[0] == '*')) {

			$this->error('您没有登录请登录',url('index/auth/login'));
		}

		//初始化必填
		//填写在开发者控制台首页上的Account Sid
		$options['accountsid']='cb5292c1774c3b37979b85159abdfbed';
		//填写在开发者控制台首页上的Auth Token
		$options['token']='76b337633ec453eb614c20811db02b5b';

		//初始化 $options必填
		$this->ucpass = new Ucpaas($options);

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
		session('user',null);
		$this->success('您退出成功，将以游客身份重新浏览' , 'index/index/index');
	}

	public function login()
	{
		if ($this->is_login) {
			$this->success('您已经登录' , 'index/index/index');
		}
		return $this->fetch();
	}

	public function doLogin()
	{
		if (empty($_POST)) {
			exit;
		}

		$info  = User::get(["phone"=>$_POST['phone'],'password'=>md5($_POST['pwd'])]);

		if ($info) {
			session('user' , $info->toArray());
			$user_id = session('user')['u_id'];
			if(!empty($_COOKIE['shop_cart_info'])){
				$cart_info = unserialize(stripslashes($_COOKIE['shop_cart_info']));
				foreach($cart_info as $k=>$v){
					$find = [];
					$find['user_id'] = $user_id;
					$find['goods_id'] = $v['goods_id'];
					$find['color'] = $v['color'];
					$find['size'] = $v['size'];
					$get_cart = Cart::get($find);
					if(empty($get_cart)){
						$find['quantity'] = $v['quantity'];
						$result = Cart::create($find);
					}else{
						$cart_id = $get_cart->cart_id;
						$addnum = $get_cart->quantity+$v['quantity'];
						$result = Cart::where('cart_id',$cart_id)->update(['quantity'=>$addnum]);
					}
				}
				//Cookie::delete('shop_cart_info');
			}
			return json(['status'=>1 ,'msg' => '登录成功' , 'url' =>url('index/index/index')]);
		} else {
			return json(['status'=>0 ,'msg' => '登录失败' , 'url' =>url('index/auth/login')]);
		}


	}
	public function register()
	{
		return $this->fetch();
	}

	public function doRegister()
	{
		if (empty($_POST)) {
			exit;
		}

		$data['phone'] = $_POST['phone'];


		$preg_phone='/^1[345678]\d{9}$/ims';
		if(!preg_match($preg_phone,$data['phone'])){
     		return json(['status'=>0 ,'msg' => '请输入正确的手机号' , 'url' =>url('index/auth/register')]);
		}

		if(!session('?yzm')){
			return json(['status'=>0 ,'msg' => '请发送短信验证码' , 'url' =>url('index/auth/register')]);
		}

		if (session('phone') != $data['phone']) {
			return json(['status'=>0 ,'msg' => '请输入当前接收短信的手机号' , 'url' =>url('index/auth/register')]);
		}

		if(session('yzm') != $_POST['yzm']){
			return json(['status'=>0 ,'msg' => '请输入正确的验证码' , 'url' =>url('index/auth/register')]);
		}

		//清除验证码
		session('yzm', null);


		$info  = User::get(["phone"=>$data['phone']]);
		if ($info) {
			return json(['status'=>0 ,'msg' => '该手机号已被注册，请选择更好的手机号' , 'url' =>url('index/auth/register')]);
		}
		

		$data['password'] = md5($_POST['pwd']);
		$data['username'] = $data['phone'];
		$data['login_count'] = 1;
		$data['create_time'] = time();
		$data['update_time'] = time();
		$data['grade'] = 0;

		$result = User::create($data);

		if ($result) {
			session('user' , $result->toArray());
			return json(['status'=>1 ,'msg' => '注册成功' , 'url' =>url('index/index/index')]);
		} else {
			return json(['status'=>0 ,'msg' => '注册失败' , 'url' =>url('index/auth/register')]);
		}

	}

	public function sendPhone()
	{
		if (empty($_POST)) {
			exit;
		}

		$yzm = join('' , array_rand(range(0,9),4));
		
		//setCookie('phoneYzm' , $yzm , 60*10);

		//17805423536
		//17611707901
		//验证手机
		$appid = "cc1b4c95bc5e42c5b66b730e5d9a50aa";	//应用的ID，可在开发者控制台内的短信产品下查看
		$templateid = "338021";    //可在后台短信产品→选择接入的应用→短信模板-模板ID，查看该模板ID
		$param = $yzm; //多个参数使用英文逗号隔开（如：param=“a,b,c”），如为参数则留空
		$mobile = $_POST['phone'];
		$uid = "";

		//70字内（含70字）计一条，超过70字，按67字/条计费，超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。

		$res = $this->ucpass->SendSms($appid,$templateid,$param,$mobile,$uid);
		if (substr_count($res, 'OK')) {
			session('phone' , $_POST['phone']);
			session('yzm' , $yzm);
			return json(['status'=>1,'msg'=>'发送短信成功，请查收']);
			exit;
		}else{
			return json(['status'=>0,'msg'=>'发送短信失败,请重新输入手机号']);
			exit;
		}

	}

	public function getpass()
	{
		return $this->fetch();
	}

	public function doGetPass()
	{
		if (empty($_POST)) {
			exit;
		}

		$data['phone'] = $_POST['phone'];


		$preg_phone='/^1[345678]\d{9}$/ims';
		if(!preg_match($preg_phone,$data['phone'])){
     		return json(['status'=>0 ,'msg' => '请输入正确的手机号' , 'url' =>url('index/auth/register')]);
		}

		if(!session('?yzm')){
			return json(['status'=>0 ,'msg' => '请发送短信验证码' , 'url' =>url('index/auth/register')]);
		}

		if (session('phone') != $data['phone']) {
			return json(['status'=>0 ,'msg' => '请输入当前接收短信的手机号' , 'url' =>url('index/auth/register')]);
		}

		if(session('yzm') != $_POST['yzm']){
			return json(['status'=>0 ,'msg' => '请输入正确的验证码' , 'url' =>url('index/auth/register')]);
		}

		//清除验证码
		session('yzm', null);


		$info  = User::get(["phone"=>$data['phone']]);
		if (!$info) {
			return json(['status'=>0 ,'msg' => '该手机号没有注册过，请重新注册' , 'url' =>url('index/auth/register')]);
		}
		

		$data['password'] = md5($_POST['pwd']);
		
		$data['update_time'] = time();

		$result = User::where(['phone'=>$data['phone']])->update($data);

		if ($result) {
			$info  = User::get(["phone"=>$data['phone']]);
			session('user' , $info->toArray());
			return json(['status'=>1 ,'msg' => '修改密码成功' , 'url' =>url('index/index/index')]);
		} else {
			return json(['status'=>0 ,'msg' => '修改密码失败' , 'url' =>url('index/auth/getpass')]);
		}
	}
}