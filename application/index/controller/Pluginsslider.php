<?php
namespace app\index\controller;
use app\index\model\PluginsSlider;
use app\index\model\Goods;
class Pluginsslider
{
	//获取轮播图
	public function getSlider(Goods $goods)
	{
		$data = PluginsSlider::all();
		return json($data);
	}
}