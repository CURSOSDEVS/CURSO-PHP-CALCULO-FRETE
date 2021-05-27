<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use Hcode\Model\Category;

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

///////////////////////////////////////////////////////////
/**Rota para carregar a pagina  categoria específica*/
$app->get('/categories/:idcategory', function($idcategory)
{
	$category = new Category();
	
	$category->get((int)$idcategory);
	
	//vamos agora utilizar a classe Page que carregara o template somente da categoria do produto
	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>[]
	]);
});

?>
