<?php
namespace app\index\controller;
use think\Request;
use app\index\model\Goods;
use app\index\model\GoodsCategory;
use app\index\model\Comment;
use app\index\model\GoodsImage;
use app\index\model\Cart;
class Item extends Auth
{
	public function show()
	{
		$gid = $_GET['gid'];
		$goods = Goods::get($gid);
		$category = Goods::get($gid)->category;
		$str = $category[0]->path;
		$str = substr($str,2);
		$arr = explode(',',$str);
		$category_one = GoodsCategory::find($arr[0])->name;
		$category_two = GoodsCategory::find($arr[1])->name;
		$category_three = GoodsCategory::find($arr[2])->name;

		//查看折扣信息
		$discount = $goods->discount;
		$price = $goods->price;
		if($discount!=10){
			$disprice = sprintf('%.2f',$price*$discount*0.1);
			$this->assign('disprice',$disprice);
			$this->assign('discount',$discount);
		}
		//累计评论
		$comment = Comment::all(["goods_id"=>$gid]);
		//var_dump(count($comment));die;
		$this->assign('comment',count($comment));
		//商品图片
		$pic = GoodsImage::where('goods_id',$gid)->order('sort_order','asc')->select();
		//var_dump($pic);die;
		$this->assign('pic',$pic);
		$this->assign('count_pic',count($pic));
		//爆款推荐
		$recommend = Goods::order('sale_num','desc')->limit(3)->select();
		$this->assign('recommend',$recommend);
		//颜色、尺码
		if(isset($goods->color)){
			$color = explode(' ',$goods->color);
			$this->assign('color',$color);
		}
		if(isset($goods->size)){
			$size = explode(' ',$goods->size);
			$this->assign('size',$size);
		}
		//上架日期
		$date = explode(' ',$goods->create_time)[0];
		//$date = date('Y-m-d',$goods->create_time);
		//var_dump($goods->create_time);die;
		$this->assign('date',$date);
		$this->assign('price',$price);
		$this->assign('category_one',$category_one);
		$this->assign('category_two',$category_two);
		$this->assign('category_three',$category_three);

		$this->assign('goods',$goods);
		return $this->fetch();
	}
	//加入购物车
	public function addCart()
	{
		$user_id = session('user')['u_id'];
		$goods_id = input('post.gid');
		$buy_num = input('post.quantity');
		$find = ['goods_id'=>$goods_id,'user_id'=>$user_id];
		if(input('post.color')){
			$find['color'] = input('post.color');
		}
		if(input('post.size')){
			$find['size'] = input('post.size');
		}
		//检查用户购物车有无同款同样式的商品 累加
		$get_cart = Cart::get($find);
		if(empty($get_cart)){
			$find['quantity'] = input('post.quantity');
			$result = Cart::create($find);
			if($result){
				return json(['status'=>1,'msg'=>'添加成功']);
			}else{
				return json(['status'=>0,'msg'=>'添加失败']);
			}

		}else{
			$cart_id = $get_cart->cart_id;
			$addnum = $quantity = $get_cart->quantity+$buy_num;
			$result = Cart::where('cart_id',$cart_id)->update(['quantity'=>$addnum]);
			if($result){
				return json(['status'=>1,'msg'=>'添加成功']);
			}else{
				return json(['status'=>0,'msg'=>'添加失败']);
			}
		}

	}
	//获取产品的详细描述图
	public function getDetail()
	{
		//$gid = input('post.gid');;
		//echo $gid;
	}
}
