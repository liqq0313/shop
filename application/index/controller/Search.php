<?php
namespace app\index\controller;
use app\index\model\Goods;
use app\index\model\GoodsCategory;
use app\index\controller\Common;
use think\Request;
class Search extends Auth
{
	public function index()
	{
		$keyword = isset($_GET['keyword'])?$_GET['keyword']:null;

		if(!isset($_GET['cid'])){
			$this->redirect(url('index/index/index'));
		}
		$cid = $_GET['cid'];

		$option = [];
		
		$cate = GoodsCategory::get($cid);
		if (empty($cate)) {
			$this->redirect(url('index/index/index'));
		}

		if ($cate['pid']!=0) {
			$tmp = GoodsCategory::get($cate['pid']);
			if ($tmp['pid']!=0) {
				$tmp2 = GoodsCategory::get($tmp['pid']);
				$option['root'] = $tmp2;
				$option['second'] = $tmp;
			}else{
				$option['root'] = $tmp;
			}
		}

		if (!empty($cate)) {
			$option['cate'] = $cate;
		}

		//每页显示数
		$num=5;
		$page = isset($_GET['page'])?$_GET['page']:1;
		$start = ($page-1)*$num;
		

		$goods = [];
		if (empty($keyword)) {
			$goods = Goods::query('select g.goods_id,g.name,g.image,g.price,g.sale_num,g.viewed,g.comment_num,g.discount from shop_goods g ,shop_goods_to_category gc ,(select c.category_id from shop_goods_category c where c.path like "'.$cate['path'].',%") a where g.goods_id=gc.goods_id and g.status=1 and a.category_id=gc.category_id  order by g.viewed desc limit '.$start.','.$num);
			$total = Goods::query('select count(g.goods_id) count from shop_goods g ,shop_goods_to_category gc ,(select c.category_id from shop_goods_category c where c.path like "'.$cate['path'].',%") a where g.goods_id=gc.goods_id and g.status=1 and a.category_id=gc.category_id');
			$total = $total[0]['count'];
		}else{
			$goods = Goods::where('name','like',"%$keyword%")->where('status',1)->whereOr('meta_keyword','like',"%$keyword%")->limit($start,$num)->select();

			$total = Goods::where('name','like',"%$keyword%")->where('status',1)->whereOr('meta_keyword','like',"%$keyword%")->count();
		}

		$bao = Goods::field('goods_id,name,price,image,discount')->where('status',1)->order('sale_num desc')->limit(10)->select();
		

		$pageCount = ceil($total/$num);
		
		if (isset($_GET['mPrice']) && isset($_GET['lPrice'])) {
			$m = $_GET['mPrice'];
			$l = $_GET['lPrice'];
			$option['price']['name'] = '价格:'.$m.'-'.$l;
		}

		if (isset($_GET['ev'])) {
			$tmp = explode('l', $_GET['ev']);
			if (!isset($tmp[0][0])) {
				goto a;
			}
			if($tmp[0][0]=='m'){
				$m = substr($tmp[0], 1);	
				$option['price']['name'] = '价格:'.$m;
			}

			if (isset($tmp[1])) {
				$l = $tmp[1];
				$option['price']['name'] .='-'.$tmp[1];
			}
		}
		a:
		//根据价格大小排序
		if (!empty($goods)) {
			$old = [];
			if (isset($m) && isset($l)) {
				foreach ($goods as $value) {
					if ($value['price']>=$m && $value['price']<=$l) {
						$old[] = $value;
					}
				}

				$goods = $old;
			}else if(isset($m)){
				foreach ($goods as $value) {
					if ($value['price']>=$m) {
						$old[] = $value;
					}
				}

				$goods = $old;
			}
		}

		if (!isset($_GET['sort'])) {
			$sort = 'all';
		}else{
			//根据评论、销量、价格排序  默认按照点击量排序
			switch ($_GET['sort']) {
				case 'comment_desc':
					$sort = 'comment_desc';
					array_multisort(array_column($goods,'comment_num'),SORT_DESC,$goods);
					break;
				case 'sales_desc':
					$sort = 'sales_desc';
					array_multisort(array_column($goods,'sale_num'),SORT_DESC,$goods);
					break;
				case 'price_desc':
					$sort = 'price_desc';
					array_multisort(array_column($goods,'price'),SORT_DESC,$goods);
					break;
				case 'price_asc':
					$sort = 'price_asc';
					array_multisort(array_column($goods,'price'),SORT_ASC,$goods);
					break;
				default:
					$sort = 'all';
					array_multisort(array_column($goods,'viewed'),SORT_DESC,$goods);
					break;
			}
		}


		$fenlei = GoodsCategory::where('pid',$cid)->select();
		$dalei = GoodsCategory::where('pid',0)->select();

		$prev = $page-1;
		$next = $page+1;
		if ($prev<=1) {
			$prev=1;
		}
		if ($next>=$pageCount) {
			$next=$pageCount;
		}

		$this->assign('keyword' , $keyword);
		$this->assign('bao' , $bao);
		$this->assign('goods' , $goods);
		$this->assign('page' , $page);
		$this->assign('prev' , $prev);
		$this->assign('next' , $next);
		$this->assign('num' , $num);
		$this->assign('pageCount' , $pageCount);
		$this->assign('total' , $total);
		$this->assign('cate' , $cate);
		$this->assign('fenlei' , $fenlei);
		$this->assign('dalei' , $dalei);
		$this->assign('option' , $option);
		$this->assign('sort' , $sort);
		return $this->fetch();
	}
	
}