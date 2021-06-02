<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;

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
	//verifica se foi informado o número da página, caso contrário considera 1	
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	$category = new Category();
	
	$category->get((int)$idcategory);

	$pagination = $category->getProductsPage($page);

	//var_dump($pagination);
	//exit;
	//montando o array page que será utilizado na página do template
	$pages = [];

	for($i=1; $i <= $pagination['pages']; $i++)
	{
		//criando o array com as variáveis necessárias no template
		array_push($pages, [
			'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
			'page'=>$i
		]);
	}

	//vamos agora utilizar a classe Page que carregara o template somente da categoria do produto
	$page = new Page();
	
	//utilizaremos o método estático checklist que verificará se o objeto
	//possui o caminho da foto se não tiver será adicionado
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination['data'],
		'pages'=>$pages
		]);
});

////////////////////////////////////////////////////////////
/**Rota para ver a descrição das características do produto */
$app->get('/products/:desurl', function($desurl)
{
	$product = new Product();

	//método da classe produto que irá retornar o objeto com a url informada
	$product->getFromUrl($desurl);

	$page = new Page();

	$page->setTpl('product-detail', [
		'product'=>$product->getValues(),
		'categories'=>$product->getCategories()//metodo criado na classe Product para iformar quais as categorias o produto está relacionado
		
	]);

});

///////////////////////////////////////////////////////////////
//Rota para abrir a página do carrinho de compras
$app->get('/cart', function()
{
	$cart = Cart::getFromSession();

	$page = new Page();

	$page->setTpl('cart');
});

?>