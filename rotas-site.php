<?php

use \Hcode\Page;
use \Hcode\Model\Product;

//inicio da primeira rota
$app->get('/', function() {
    
	//lista todos os produtos
	$products = Product::listAll();

	//através do método ckecklist, incluimos o caminho da foto
	//no array
	$products = Product::checkList($products);

	$page = new Page();

	$page->setTpl("index", [
		'products'=>$products
	]);

});

?>