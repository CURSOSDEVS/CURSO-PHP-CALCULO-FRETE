<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;
use \Hcode\Model\Product;

////////////////////////////////////////////////////////////////////////
/**criando rota para acessar o template de categorias */
$app->get('/admin/categories', function()
{
	/**sempre devemos veficar se há uma sessão valida */
	User::verifyLogin();

	/**carregando todas as categorias cadastradas no banco de dados */
	$categories = Category::listAll();
	
	/**passando os parâmetros para criação da página */
	$page = new PageAdmin();
	$page->setTpl('categories',[
		'categories'=>$categories
	]);
});

//////////////////////////////////////////////////////////////////////////////
/**criando rota para abriar a página de criação das categorias quando o usuário
 * clicar em nova categoria
 */
$app->get('/admin/categories/create', function()
{
	User::verifyLogin();

	$page = new PageAdmin();
	$page->setTpl('categories-create');
});

//////////////////////////////////////////////////////
/**criando rota para executar a criação das categorias no banco de dados
 * quand o usuário clicar em cadastrar
 */
$app->post('/admin/categories/create', function()
{
	User::verifyLogin();

	$category = new Category();

	//setando os valores dos atributos na classe category por meio do metodo setData  da classe Model
	$category->setData($_POST);

	$category->save();

	/**redirecionando para a página de lista de categorias */
	header("Location: /admin/categories");
	exit;

});

///////////////////////////////////////////////////////////
/**Rota para exclusão de categorias */
$app->get("/admin/categories/:idcategory/delete", function($idcategory)
{

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	
	$category->delete();

	/**redirecionando para a página de lista de categorias */
	header("Location: /admin/categories");
	exit;

});
////////////////////////////////////////////////////////////
/**rota para abrir tela da edição de categorias */
$app->get('/admin/categories/:idcategory', function($idcategory)
{
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	/**a variavel category no template categories-update é um array então para converter
	 * devemos após o objeto category, chamar o metodo getValues da classe model
	*/
	$page->setTpl('categories-update', [
		'category'=>$category->getValues()
	]);

});

////////////////////////////////////////////////////////////
/**rota para realizar a edição das categorias */
$app->post('/admin/categories/:idcategory', function($idcategory)
{
	User::verifyLogin();

	$category = new Category();
	
	//carrega a categoria que será alterada 
	$category->get((int)$idcategory);
	
	//atualiza a informação que veio do formulário no banco de dados
	$category->setData($_POST);
	
	$category->save((int)$idcategory, $_POST);

	header("Location: /admin/categories");
	exit;

});



//////////////////////////////////////////////////////////////
//rota para acessar a tela de correlação de produtos e categorias
$app->get('/admin/categories/:idcategory/products', function($idcategory)
{
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);

	$page = new PageAdmin();

	//utilizando o método getProducts da classe Category, buscamos todos os produtos que estão
	//relacionados a categoria e os que não estão para carregarmos no template
	$page->setTpl('categories-products', [
		'category'=>$category->getValues(),
		'productsNotRelated'=>$category->getProducts(false),
		'productsRelated'=>$category->getProducts()
	]);
});


/////////////////////////////////////////////////////////////////////
//rota para adicionar produtos nas categorias
$app->get('/admin/categories/:idcategory/products/:idproduct/add', function($idcategory, $idproduct)
{

	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	
	$product = new Product();

	$product->get((int)$idproduct);

	$category->addProducts($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

/////////////////////////////////////////////////////////////////////
//rota para remover produtos nas categorias
$app->get('/admin/categories/:idcategory/products/:idproduct/remove', function($idcategory, $idproduct)
{
	User::verifyLogin();

	$category = new Category();

	$category->get((int)$idcategory);
	
	$product = new Product();

	$product->get((int)$idproduct);

	$category->removeProducts($product);

	header("Location: /admin/categories/".$idcategory."/products");
	exit;

});

?>
