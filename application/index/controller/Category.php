<?php
namespace app\index\controller;
use app\index\model\GoodsCategory;
use app\index\controller\Common;
class Category
{
	public function getCateName()
	{
		$data = GoodsCategory::column('category_id,pid,name');
		$data = Common::getTree($data , 0 , 'category_id');
		return json($data);
	}

	public function getGoodsIndex()
	{
		$data = GoodsCategory::column('category_id,pid,name');
		$data = Common::getTree($data , 0 , 'category_id');
		
		
		$data = $this->add($data);
		
		return json($data);
		//$data = GoodsCategory::get(6);
		//var_dump($data->getGoods);

		//$data = $this->add($data);
		//return json($data);
	}

	public function add($data){
		foreach ($data as $key => $value) {
			if ($value['son']) {
				$this->goods($value['son'][0]['category_id']);
				$this->add($value['son']);
			}
		}

		return $data;
	}

	public function goods($id){
		return GoodsCategory::get($id)->getGoods;
	}

	public function test(){
		$count = GoodsCategory::get(6)->getGoods;
		var_dump($count);
		//var_dump(count($count));
	}
	
}