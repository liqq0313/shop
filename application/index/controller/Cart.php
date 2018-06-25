<?php
namespace app\index\controller;
use app\index\model\User;
class Cart extends Auth
{
	public function cart()
	{
		return $this->fetch();
	}
	public function getCart()
	{
		
	}
}