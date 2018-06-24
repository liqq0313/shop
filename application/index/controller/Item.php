<?php
namespace app\index\controller;
use think\Request;
use app\index\model\Goods;
use app\index\model\GoodsCategory;
use app\index\model\Comment;
use app\index\model\GoodsImage;
use app\index\model\Cart;
use app\index\model\GoodsDetail;
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
		$comment = Comment::query("select * from shop_comment where goods_id=$gid and status=1 order by create_time desc");
		$this->assign('all_comment',$comment);
		$this->assign('comment',count($comment));
		//好评 中评 差评
		$good_comment = Comment::query("select * from shop_comment where goods_id=$gid and Highpraise is not null and status=1 order by create_time desc");
		$this->assign('good_comment',$good_comment);
		$this->assign('count_good',count($good_comment));
		$mid_comment = Comment::query("select * from shop_comment where goods_id=$gid and mediumreview is not null and status=1 order by create_time desc");
		$this->assign('count_mid',count($mid_comment));
		$this->assign('mid_comment',$mid_comment);
		$bad_comment = Comment::query("select * from shop_comment where goods_id=$gid and negativecomment is not null and status=1 order by create_time desc");
		$this->assign('bad_comment',$bad_comment);
		$this->assign('count_bad',count($bad_comment));
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
		//var_dump(1);
		$goods_id = input('post.goods_id');
		$img = GoodsDetail::where('goods_id',$goods_id)->order('sort','asc')->select();
		//var_dump($img);
		return json($img);
	}
	public function getComment()
	{
		$flag = input('post.flag');
		$goods_id = input('post.goods_id');
		$num = input('post.num');
		$page = input('post.page');
		//$limit = ($page-1)*num;
		if($flag==0){
			//$comment = Comment::query("select * from shop_comment where goods_id=$goods_id and status=1 order by create_time desc");
			$count = count(Comment::where('goods_id',$goods_id)->where('status',1)->select());
			$pageNum = ceil($count/$num);
			if($page<=1){
				$page = 1;
			}
			if($page >=$pageNum){
				$page = $pageNum;
			}
			$limit = ($page-1)*$num;
			if($page==$pageNum){
				$num = $count-($page-1)*$num;
			}
			$comment = Comment::where('goods_id',$goods_id)->where('status',1)->limit("$limit,$num")->order('create_time','desc')->select();
		}
		if($flag==1){
			$count =count(Comment::where('goods_id',$goods_id)->where('status',1)->where('Highpraise','<>','')->select());
			$pageNum = ceil($count/$num);
			if($page<=1){
				$page = 1;
			}
			if($page >=$pageNum){
				$page = $pageNum;
			}
			$limit = ($page-1)*$num;
			if($page==$pageNum){
				$num = $count-($page-1)*$num;
			}
			$comment = Comment::where('goods_id',$goods_id)->where('status',1)->where('Highpraise','<>','')->limit("$limit,$num")->order('create_time','desc')->select();
		}
		if($flag==2){
			$count = count(Comment::where('goods_id',$goods_id)->where('status',1)->where('mediumreview','<>','')->select());
			$pageNum = ceil($count/$num);
			if($page<=1){
				$page = 1;
			}
			if($page >=$pageNum){
				$page = $pageNum;
			}
			$limit = ($page-1)*$num;
			if($page==$pageNum){
				$num = $count-($page-1)*$num;
			}
			$comment = Comment::where('goods_id',$goods_id)->where('status',1)->where('mediumreview','<>','')->limit("$limit,$num")->order('create_time','desc')->select();
		}
		if($flag==3){
			$count = count(Comment::where('goods_id',$goods_id)->where('status',1)->where('negativecomment','<>','')->select());
			$pageNum = ceil($count/$num);
			if($apge<=1){
				$page = 1;
			}
			if($page >=$pageNum){
				$page = $pageNum;
			}
			$limit = ($page-1)*$num;
			if($page==$pageNum){
				$num = $count-($page-1)*$num;
			}
			$comment = Comment::where('goods_id',$goods_id)->where('status',1)->where('negativecomment','<>','')->limit("$limit,$num")->order('create_time','desc')->select();
		}
		//var_dump($comment[3]);die;
		if($comment){
			//return json(['comment'=>$comment,'status'=>1]);
			foreach($comment as $k=>$v){
				$com[$k]['user_id'] = $comment[$k]->getUser->u_id;
				$com[$k]['username'] = $comment[$k]->getUser->username;
				$com[$k]['avg'] = $comment[$k]->getUser->avg;
				//var_dump($comment[3]->getUser->username);die;
				if($flag==0){
					$com[$k]['comment'] = $comment[$k]->content;
					//var_dump($com[$k]['all_comment']);
				}
				if($flag==1){
					$com[$k]['comment'] = $comment[$k]->Highpraise;
					//var_dump($com[$k]['good_comment']);die;
				}
				if($flag==2){
					$com[$k]['comment'] = $comment[$k]->mediumreview;
				}
				if($flag==3){
					$com[$k]['comment'] = $comment[$k]->negativecomment;
				}
				$com[$k]['create_time'] = $comment[$k]->create_time;
			}
			return json(['com'=>$com,'status'=>1,'pageNum'=>$pageNum,'page'=>$page]);
		}else{
			return json(['status'=>0]);
			//return json(['status'=>0]);
		}
	}
}
