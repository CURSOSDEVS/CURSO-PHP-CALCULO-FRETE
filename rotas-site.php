<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;

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

///////////////////////////////////////////////////////////
/**Rota para carregar a pagina  categoria específica*/
$app->get('/categories/:idcategory', function($idcategory)
{
	
	$category = new Category();
	
	$category->get((int)$idcategory);

	//vamos agora utilizar a classe Page que carregara o template somente da categoria do produto
	$page = new Page();
	
	//utilizaremos o método estático checklist que verificará se o objeto
	//possui o caminho da foto se não tiver será adicionado
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>Product::checkList($category->getProducts())]
	);
});

?>