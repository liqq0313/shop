<?php
namespace app\index\controller;
use app\index\model\Address;
use app\index\model\User;
use think\Validate;
class Info extends Auth
{
	protected $is_check_login = ['*'];
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
		//$add = Address::get(1);
		//var_dump($add);
		//var_dump(session('u_id'));
		return $this->fetch();

		//var_dump($add['id']);
		//return $this->fetch();
	}
	public function getAdd()
	{
		$u_id = session('user')['u_id'];
		$add = Address::all(["user_id"=>$u_id]);//获取所有地址
		$default = User::get($u_id)->address_id;//获取默认address_id
		//var_dump($default);
		$address = [];
		foreach($add as $k=>$v){
			//处理手机号
			$phone = $v->phone;
			$search = substr($phone,3,4);
			$phone = str_replace($search,'****',$phone);
		 	$address[$k]['id'] = $v->id;
		 	$address[$k]['name'] = $v->name;
		 	$address[$k]['province'] = $v->province;
		 	$address[$k]['county'] = $v->county;
		 	$address[$k]['city'] = $v->city;
		 	$address[$k]['address'] = $v->address;
		 	$address[$k]['phone'] = $phone;
		 	if($address[$k]['id'] == $default){
		 		$address[$k]['is_default'] = 1;
		 	}else{
		 		$address[$k]['is_default'] = 0;
		 	}
		}
		 //var_dump($address);
		 return json($address);
	}
	//添加地址
	public function doAdd()
	{
		//return 1;
		//查询已添加条数
		//var_dump(input('post.checked'));die;
		$addTotal = Address::all(['user_id'=>session('user')['u_id']]);
		$addCount = count($addTotal);
		if($addCount>=5){
			return json(['status'=>0 ,'msg' => '地址已达上限']);
		}
		//echo $addCount;die;
		$data['name'] = input('post.name');
		$data['phone'] =  input('post.mobile');
		// $data['name'] = $_POST['name'];
		$data['province'] = input('post.province');
		$data['city'] = input('post.city');
		$data['county'] = input('post.county');
		$data['address'] = input('post.details');
		$data['user_id'] = session('user')['u_id'];
 		$default = input('post.checked');
 		//echo $default;die;
		$preg_phone='/^1[345678]\d{9}$/ims';
		if(!preg_match($preg_phone,$data['phone'])){
     		return json(['status'=>0 ,'msg' => '请输入正确的手机号']);
		}
		//var_dump($default);die;
		$result = Address::create($data);
		if($result){
			//echo $default;
			if($default==1){

				$user = User::get($result->user_id);
				//echo $result->user_id;
				$user->address_id = $result->id;
				$user->save();
			}
			// if(!empty($_GET['flag'])){
			// 	return json(['status'=>1 ,'msg' => '新增']);
			// 	header("Location:index/cart/order");
			// 	exit();
			// }
			return json(['status'=>1 ,'msg' => '新增成功']);
		}else{
			return json(['status'=>0 ,'msg' => '添加失败']);
		}

	}
	//删除地址
	public function doDel()
	{
		$add_id = input('post.add_id');
		$user_id = session('user')['u_id'];
		$default_id = User::get(["u_id"=>$user_id])->address_id; //默认地址id
		//echo $default_id;
		//echo $add_id;die;
		$address = Address::get($add_id);
		$result = $address->delete();
		if($result){
			if($default_id==$add_id && $default_id!=null){
				$user = User::get(['address_id'=>$add_id]);
				//echo $user->address_id;
				$user->address_id = null;
				$user->save();
			}
			return json(['status'=>1 ,'msg' => '删除成功']);
		}else{
			return json(['status'=>0 ,'msg' => '删除失败']);
		}
		//echo $default_id;
	}
	//设为默认地址
	public function doSet()
	{
		$add_id = input('post.add_id');
		$user = User::get(session('user')['u_id']);
		//echo $user->address_id;
		$user->address_id = $add_id;
		$user->save();
		return json(['status'=>1 ,'msg' => '设置成功']);
	}

	public function address_edit()
	{
		$address_id = $_GET['id'];
		$u_id = session('user')['u_id'];
		$add_info = Address::get(['user_id'=>$u_id,'id'=>$address_id]);
		//var_dump($add_info);die;
		//$info = '';
		$info['name'] = $add_info->name;
		$info['address_id'] = $add_info->id;
		$info['phone'] = $add_info->phone;
		$info['address'] = $add_info->address;
		$info['province'] = $add_info->province;
		$info['city'] = $add_info->city;
		$info['county'] = $add_info->county;

		//查看默认地址
		$default = User::get($u_id)->address_id;
		if($default == $address_id && $default!=null){
			$info['default'] = true;
		}else{
			$info['default'] = false;
		}
		//var_dump($info);
		$this->assign('info',$info);
		return $this->fetch();
	}
	//修改地址
	public function doEdit()
	{
		$address_id = input('post.address_id');
		$data['name'] = input('post.name');
		$data['phone'] =  input('post.mobile');
		// $data['name'] = $_POST['name'];
		$data['province'] = input('post.province');
		$data['city'] = input('post.city');
		$data['county'] = input('post.county');
		$data['address'] = input('post.details');
		$data['user_id'] = session('user')['u_id'];
 		$default = input('post.checked');
 		//echo $default;die;
		$preg_phone='/^1[345678]\d{9}$/ims';
		if(!preg_match($preg_phone,$data['phone'])){
     		return json(['status'=>0 ,'msg' => '请输入正确的手机号']);
		}
		//$res = Db::name('address')->where('id',$address_id)->update($data);
		$res = Address::where('id',$address_id)->update($data);
		if($default==1){
			$user = User::get(session('user')['u_id']);
			$user->address_id = $address_id;
			$res = $user->save();
		}else{
			$user = User::get(session('user')['u_id']);
			$user->address_id = null;
			$res = $user->save();
		}
		if($res){
			return json(['status'=>1,'msg'=>'修改成功']);
		}else{
			return json(['status'=>0,'msg'=>'修改失败']);
		}
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