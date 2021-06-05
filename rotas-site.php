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

	//var_dump($cart->getProducts());
	//exit;
	

	$page->setTpl('cart',[
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
		//passa a mensagem de erro para o template caso exista
		'error'=>Cart::getMsgError()
	]);
});

/////////////////////////////////////////////////////////
//rota para adicionar produtos ao carrinho
$app->get('/cart/:idproduct/add', function($idproduct)
{
	$product = new Product();

	$product->get((int)$idproduct);

	//recuperando o carrinho da sessão
	$cart = Cart::getFromSession();

	//verifica quantidade informada na variável qtd do template de detalhe do produto se não for informado nada
	//qtd será igual a 1
	$qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

	//através do for faremos a inclusão de tantos produtos quanto o valor de qtd for informado
	for( $i = 0; $i < $qtd; $i++)
	{
		//adicionando o produto ao carrinho
		$cart->addProducts($product);
	}
	//var_dump($product);
	//var_dump($cart);
	//exit;
	
	//uma vez adicionado o produto o usuário será redirecionado para a
	//pagina do carrinho para ver como ficou
	header('Location: /cart ');
	exit;
});

/////////////////////////////////////////////////////////
//rota para remover um produto do carrinho
$app->get('/cart/:idproduct/minus', function($idproduct)
{
	$product = new Product();

	$product->get((int)$idproduct);

	//recuperando o carrinho da sessão
	$cart = Cart::getFromSession();

	//adicionando o produto ao carrinho
	$cart->removeProducts($product);

	//uma vez adicionado o produto o usuário será redirecionado para a
	//pagina do carrinho para ver como ficou
	header('Location: /cart ');
	exit;
});

/////////////////////////////////////////////////////////
//rota para remover todos os produtos de mesmo id do carrinho
$app->get('/cart/:idproduct/remove', function($idproduct)
{
	$product = new Product();

	$product->get((int)$idproduct);

	//recuperando o carrinho da sessão
	$cart = Cart::getFromSession();

	//adicionando o produto ao carrinho
	$cart->removeProducts($product, true);

	//uma vez adicionado o produto o usuário será redirecionado para a
	//pagina do carrinho para ver como ficou
	header('Location: /cart ');
	exit;
});

//////////////////////////////////////////////////////
//Rota para calcular o valor do frete de acordo com o CEP informado
$app->post('/cart/freight', function()
{
	$cart = Cart::getFromSession();

	$cart->setFreight($_POST['zipcode']);

	header('Location: /cart');
	exit;
});

?>