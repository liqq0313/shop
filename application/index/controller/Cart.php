<?php
namespace app\index\controller;
use app\index\model\Cart;
use app\index\model\Goods;
use app\index\model\Order;
use app\index\model\User;
use app\index\model\Address;
class Cart extends Auth
{
	public function cart()
	{
		return $this->fetch();
	}
	//商品展示
	public function getCart()
	{
		if(!empty(session('user'))){
			$user_id = session('user')['u_id'];
			$cart_goods = Cart::all(['user_id'=>$user_id]);
			if(!empty($cart_goods)){
				foreach($cart_goods as $k=>$v){
					$goods_info[$k] = $v->getGoods;
				}
				//var_dump($cart_goods);die;
				//var_dump($goods_info);die;
				return json(['status'=>1,'cart_info'=>$cart_goods,'goods_info'=>$goods_info]);
			}else{
				return json(['status'=>0]);
			}
		}elseif(!empty($_COOKIE['shop_cart_info'])){
			$cart_goods = unserialize(stripslashes($_COOKIE['shop_cart_info']));
			// $cart_goods = array_values($cart_goods);
			// setcookie("shop_cart_info",serialize($cart_goods),time()+3600*24,'/');
			//var_dump($cart_goods);die;
			$len = count($cart_goods);
			for($i=0;$i<$len;$i++){
				$goods_info[$i] = Goods::get(['goods_id'=>$cart_goods[$i]['goods_id']]);
				$cart_goods[$i]['cart_id'] = $i;
			}
			//var_dump($cart_goods);die;
			return json(['status'=>1,'cart_info'=>$cart_goods,'goods_info'=>$goods_info]);
		}else{
			return ['status'=>0];
		}
	}
	//删除购物车商品
	public function delCart()
	{
		$cart_id = input('post.dataIndex');
		if(!empty(session('user'))){
			$user_id = session('user')['u_id'];
			//$cart_goods = Cart::all(['user_id'=>$user_id]);
			$res = Cart::destroy(['cart_id'=>$cart_id]);
			if($res){
				return json(['status'=>1,'msg'=>'删除成功']);
			}else{
				return json(['status'=>0,'msg'=>'删除失败']);
			}
		}elseif(!empty($_COOKIE['shop_cart_info'])){
			$cart_goods = unserialize(stripslashes($_COOKIE['shop_cart_info'])); 
			//删除该商品在数组中的位置 
			unset($cart_goods[$cart_id]);
			// if(empty($cart_goods[$cart_id])){
			// 	setcookie("shop_cart_info",serialize($cart_goods));
			// }
			if(empty($cart_goods)){
				setcookie("shop_cart_info",'',time()-1,'/');
			}else{
				$cart_goods = array_values($cart_goods);
				setcookie("shop_cart_info",serialize($cart_goods),time()+3600*24,'/');
			}
			return json(['status'=>1,'msg'=>'删除成功']);
		}
	}
	//多选删除
	public function delCheckCart()
	{
		$cart_id = input('post.cart_id');
		$cart_id = substr($cart_id,0,-1);
		$cart_id = explode(',',$cart_id);
		if(!empty(session('user'))){
			$user_id = session('user')['u_id'];
			//$cart_goods = Cart::all(['user_id'=>$user_id]);
			$len = count($cart_id);
			for($i=0;$i<$len;$i++){
				$res = Cart::destroy(['cart_id'=>$cart_id[$i]]);
				if(!$res){
					return json(['status'=>0,'msg'=>'删除失败']);
				}
			}
			return json(['status'=>1,'msg'=>'删除成功']);

		}elseif(!empty($_COOKIE['shop_cart_info'])){
			$cart_goods = unserialize(stripslashes($_COOKIE['shop_cart_info'])); 
			//删除该商品在数组中的位置 
			$len = count($cart_id);
			for($i=0;$i<$len;$i++)
			{
				unset($cart_goods[$cart_id[$i]]);
			}
			// if(empty($cart_goods[$cart_id])){
			// 	setcookie("shop_cart_info",serialize($cart_goods));
			// }
			if(empty($cart_goods)){
				setcookie("shop_cart_info",'',time()-1,'/');
			}else{
				$cart_goods = array_values($cart_goods);
				setcookie("shop_cart_info",serialize($cart_goods),time()+3600*24,'/');
			}
			return json(['status'=>1,'msg'=>'删除成功']);
		}
	}
	//写入商品数目
	public function updateCount()
	{
		$cart_id = input('post.cart_id');
		//var_dump($cart_id);
		$count = input('post.count');
		if(!empty(session('user'))&&!empty($cart_id)){
			$cart = Cart::get(['cart_id'=>$cart_id]);
			$cart->quantity = $count;
			$res = $cart->save();
		}elseif(!empty($_COOKIE['shop_cart_info'])){
			$cart_info = unserialize(stripslashes($_COOKIE['shop_cart_info']));
			//var_dump($cart_info);die;
			// $color = $cart_info[$cart_id]['color'];
			// $size = $cart_info[$cart_id]['size'];
			// $goods_id = $cart_info[$cart_id]['goods_id'];
			$cart_info[$cart_id]['quantity'] = $count;
			// $cart_info[$cart_id]['goods_id'] = $goods_id;
			// $cart_info[$cart_id]['color'] = $color;
			// $cart_info[$cart_id]['size'] = $size;
			//var_dump($cart_info[$cart_id]);die;
			setcookie("shop_cart_info",serialize($cart_info),time()+3600*24,'/');
			//return;
		}
	}
	public function addOrder()
	{
		if(empty(session('user'))){
			return json(['status'=>0,'msg'=>'请登录']);
		}
		$cart_id = input('post.cart_id');
		$cart_id = substr($cart_id,0,-1);
		$cart_id = explode(',',$cart_id);
		$rand = mt_rand(11111,99999);
		$order_num = date("YmdHis").$rand;
		$res = Order::get(['order_num_alias'=>$order_num]);
		while($res){
			$rand = mt_rand(11111,99999);
			$order_num = date("YmdHis").$rand;
			$res = Order::get(['order_num_alias'=>$order_num]);
		}
		$len = count($cart_id);
		//var_dump($len);die;
		$user_id = session('user')['u_id'];
		$user = User::get(['u_id'=>$user_id]);
		for($i=0;$i<$len;$i++){
			$cart_goods = Cart::get(['cart_id'=>$cart_id[$i],'user_id'=>$user_id]);
			//var_dump($cart_goods);die;
			$goods_id = $cart_goods->goods_id;
			$goods = Goods::get(['goods_id'=>$goods_id]);
			if($cart_goods->quantity>$goods->quantity){
				$goods_name = $goods->name;
				$msg = $goods_name.'剩余'.$goods->quantity.$goods->sku;
				return ['status'=>0,'msg'=>$msg];
			}
			if($goods->minimum>$cart_goods->quantity){
				$msg = $goods_name.'最小起订数目'.$goods->minimum.$goods->sku;
				return ['status'=>0,'msg'=>$msg];
			}
			if($cart_goods->status==0){
				$goods_name = $goods->name;
				$msg = $goods_name.'已下架';
				return ['status'=>0,'msg'=>$msg];
			}
		}
		Order::destroy(['user_id'=>$user_id,'status'=>8]);
		for($i=0;$i<$len;$i++){
			$cart_goods = Cart::get(['cart_id'=>$cart_id[$i],'user_id'=>$user_id]);
			//var_dump($cart_goods);die;
			$goods_id = $cart_goods->goods_id;
			$goods = Goods::get(['goods_id'=>$goods_id]);
			if($cart_goods->quantity<=$goods->quantity&&$cart_goods->status==1){
				$goods->quantity = $goods->quantity - $cart_goods->quantity;
				$goods->save();
				$money = $goods->price*$goods->discount*0.1*$cart_goods->quantity;
				$money = sprintf("%.2f",$money);
				$res = Order::create(['user_id'=>$user_id,'goods_id'=>$goods_id,'order_num_alias'=>$order_num,'total'=>$cart_goods->quantity,'status'=>8,'create_time'=>time(),'color'=>$cart_goods->color,'size'=>$cart_goods->size,'pay_price'=>$money]);
				if($res){
					Cart::destroy($cart_id[$i]);
				}
			}
			//var_dump($goods_id);

		}
		return ['status'=>1];
	}
	public function order()
	{
		if(empty(session('user'))){
			$this->success('请您登陆','index/auth/login');
		}
		return $this->fetch();
	}
	public function getOrder()
	{
		$user_id = session('user')['u_id'];
		$order_info = Order::all(['user_id'=>$user_id,'status'=>8]);
		if(empty($order_info)){
			return json(['status'=>0]);
		}
		$adderss_info = Address::all(['user_id'=>$user_id]);
		//var_dump($adderss_info);die;
		$user_info = User::get($user_id);
		if(empty($user_info->address_id)){
			$address_default = 0;
		}else{
			$address_default = $user_info->address_id;
		}
		$len = count($order_info);
		for($i=0;$i<$len;$i++){
			$goods_info[$i] = Goods::get(['goods_id'=>$order_info[$i]->goods_id]);
		}
		//var_dump($adderss_info);
		//die;
		return json(['status'=>1,'order_info'=>$order_info,'goods_info'=>$goods_info,'address_info'=>$adderss_info,'default_address'=>$user_info->address_id]);
	}
}