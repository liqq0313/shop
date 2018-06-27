<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use app\index\model\GoodsCategory as CategoryModel;
use app\index\controller\Common;
use think\Request;
class Category extends Base
{
	public function index()
	{
		$keyword = isset($_POST['keyword'])?$_POST['keyword']:'';
        if(!empty($keyword)){
        	$where['name'] = array("like","%$keyword%");
        	$total = CategoryModel::where($where)->count();
			$data = CategoryModel::where($where)->select();
        }else{
        	$total = CategoryModel::count();
			$data = CategoryModel::column('category_id,pid,name,path,image,sort_order');
			$data = Common::getTree($data , 0 , 'category_id');
			$data = $this->showName($data);
        }
		
		
		$this->assign('total',$total);
		$this->assign('data',$data);
		return $this->fetch();
	}

	public function addCate()
	{
		if (request()->post()) {
			$data = [];
			$data['pid'] = input('pid');
            $data['name'] = input('name');
            $data['sort_order'] = input('sort_order');
          	$info = CategoryModel::create($data);
          	//path
          	$pidPath = CategoryModel::get($info['pid']);
            $pidPath = $pidPath['path'];
            $data['path'] = $pidPath.','.$info['category_id'];
            $res = CategoryModel::where('category_id',$info['category_id'])->update($data);    
            if ($res) {
            	return json(['status'=>1,'msg'=>'新增成功']);
            }else{
            	return json(['status'=>0,'msg'=>'新增失败']);
            }
		} else {
			$cateName = CategoryModel::all();
			$cateName = Common::getTree($cateName , 0 , 'category_id');
			$cateName = $this->showName($cateName);
			$this->assign('cateName' , $cateName);
			return $this->fetch('cate-edit');
		}
	}

	public function updateCate()
	{
		if (request()->post()) {
			$data = [];
			$data['pid'] = input('pid');
            $data['name'] = input('name');
            $data['sort_order'] = input('sort_order');
            $category_id = input('category_id');

            $pidPath = CategoryModel::get($data['pid']);
            $pidPath = $pidPath['path'];
            $data['path'] = $pidPath.','.$category_id;
          	$res = CategoryModel::where('category_id',$category_id)->update($data);
            if ($res) {
            	return json(['status'=>1,'msg'=>'编辑成功']);
            }else{
            	return json(['status'=>0,'msg'=>'编辑失败']);
            }
		} else {
			$request = Request::instance();
			$param = $request->param();
			$cate = CategoryModel::get($param['id']);
			$cateName = CategoryModel::all();
			$cateName = Common::getTree($cateName , 0 , 'category_id');
			$cateName = $this->showName($cateName);
			$this->assign('cateName' , $cateName);
			$this->assign('cate' , $cate);
			return $this->fetch('cate-edit');
		}
	}

	public function delCate()
	{
		$request = Request::instance();
		$id = $request->param('id');
		$res = CategoryModel::where('pid',$id)->select();
		if ($res) {
			return json(['status'=>0,'msg'=>'删除失败,该分类下有子分类']);
		}
		$res = CategoryModel::where('category_id',$id)->delete();
		if ($res) {
			return json(['status'=>1,'msg'=>'删除成功']);
		}else{
			return json(['status'=>0,'msg'=>'删除失败']);
		}
	}

	public function showName($data)
	{
		$new = [];
		foreach ($data as $value) {
			$new[] = $value;
			if ($value['son']) {
				foreach ($value['son'] as $val) {
					$new[] = $val;
					if ($val['son']) {
						foreach ($val['son'] as $v) {
							$new[] = $v;
						}
					}
				}
			}
		}
		return $new;
	}
}