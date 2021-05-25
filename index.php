<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Categories;
use Hcode\Model\Category;

$app = new Slim();

//configura mensagem de erro
$app->config('debug', true);

//inicio da primeira rota
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});
//fim da primeira rota

//inicio da segunda rota, da página Admin
$app->get('/admin', function() {
    
	//este método estático é para verificar se o usuário está logado
	//corretamente
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("index");

});
//fim da segunda rota

/**rota do login, mas como essa página não possui
 * o template do header e footer, temos que passar
 * algumas opções para o construtor, para desabilita-los.
*/
$app->get('/admin/login', function(){
	//desabilitando o footer e header
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("login");
});

/**rota para validação do login */
$app->post('/admin/login', function(){
	//classe usuário com metodo estático que irá validar o login
	User::login($_POST['login'], $_POST['password']);

	//se não houver erro na validação o usuário será direcionado
	header("Location: /admin");
	//paramos a execução neste ponto
	exit;
});

/**rota para dar um logout da tela admin,
 * no arquivo header.html do admim procuramos 
 * onde está localizado o botão de sign out
 * e informa a rota no href
*/
$app->get('/admin/logout', function()
{
	User::logout();
	header("Location: /admin/login");
	exit;
});

////////////////////////////////////////////////////

/**criando rota para tela que irá listar todos
 * os usuários
 */
$app->get('/admin/users', function()
{
	User::verifyLogin();

	//rotina para listar todos os usuário atraves de um método estático
	$users = User::listAll();

	$page = new PageAdmin();

	//este array terá os dados que foram recuperados da tabela pela função listAll() e 
	//essas informações serão tratadas no template users
	$page->setTpl("users", array(
		"users"=>$users
	));
	
});
/////////////////////////////////////////////////////////

/**criando rota para tela de criação de usuário
 */
$app->get("/admin/users/create", function()
{
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");
});

/////////////////////////////////////////////////////
/**rota para realizar a exclusão*/
$app->get("/admin/users/:iduser/delete", function($iduser)
{
	User::verifyLogin();

	$user = new User();

	//carregando um usuário para ter certeza que ele existe no banco
	$user->get((int)$iduser);

	//utilizando o metodo delete de User vamos apagar o usuário do banco
	$user->delete();

	//atualiza a tela de usuários
	header("Location: /admin/users");
	exit;

});


/////////////////////////////////////////////////////////
/**criando rota para carregar a tela de alteração de usuário */
$app->get("/admin/users/:iduser", function($iduser)
{
	User::verifyLogin();

	/**criando um objeto de usuário e pegando o id do usuario
	 * pelo método get criado na classe User, mas antes foi feito um casting para int
	 */
	$user = new User();
	$user->get((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

///////////////////////////////////////////////////////////////////////
/**rota para salvar efetivamente a criação, a rota é a mesma a diferença é que se for
 * acessada via post significa que se quer salvar um usuário
*/
$app->post("/admin/users/create", function()
{
	User::verifyLogin();

	//verificando se os dados estão sendo recebidos
	//var_dump($_POST);

	//vamos criar um usuário novo
	$user = new User();

	//vamos verificar se o campo administrador foi selecionado no templete users-create e
	//passaremos um valor um caso ele tenha sido selecinado e 0 caso contrário
	$_POST['inadmin']=(isset($_POST['inadmin']))?1:0;
	
	//utilizando o método setData será gerado automaticamente os metodos get e set
	//e o o novo objeto será criado com todos os atributos
	$user->setData($_POST);

	//agora salvamos o usuário
	$user->save();

	//após cadastrar o usário a tela será atualizada
	header("Location: /admin/users");
	exit;


});

//////////////////////////////////////////////////////
/**rota para salvar  a alteração, a unica diferença é o método*/
$app->post("/admin/users/:iduser", function($iduser)
{
	User::verifyLogin();

	/**Criando um objeto de User */
	$user = new User();

	/**Caregando os dados atuais pois a alteração pode ter sido realizada somente em um campo */
	$user->get((int)$iduser);

	//vamos verificar se o campo administrador foi selecionado no templete users-create e
	//passaremos um valor um caso ele tenha sido selecinado e 0 caso contrário
	$_POST['inadmin']=(isset($_POST['inadmin']))?1:0;

	//tratando os dados com o setData dos dados que foram passados via post
	$user->setData($_POST);

	//metodo upadate criado na classe User
	$user->update();

	//por último a lista de usuários será novamente carregada com as alterações
	header("Location: /admin/users");
	exit;
});

////////////////////////////////////////////////////////////
/**rota para carregar a tela de esqueceu a senha */
$app->get('/admin/forgot', function()
{
	//User::verifyLogin();

	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl('forgot');
});

/////////////////////////////////////////////////////////////
/**já na tela de esqueceu a senha  quando o usuário clica em enviar o email
 * e necessário validar se o email é de um usuário cadastrado e se
 * for. vai ser gerado um codigo que terá validade de 1 hora
 */
$app->post('/admin/forgot', function()
{
	/**metodo da classe usuário que irá receber o valor do input email
	 * da tela forgot e fará a verificação se é mesmo um usuario que 
	 * esqueceu a senha. Vamos guardar o resultado em uma variavel para tratamento
	*/
	$user = User::getForgot($_POST['email']);

	/**feito o forgot vamos fazer um redirect para o usuário informando que o email
	 * foi enviado com sucesso. utilizando a classe header e a rota
	 */
	header("Location: /admin/forgot/sent");
	exit;
});

////////////////////////////////////////////////////////////////
/**Após enviar o email o usuário receberá uma tela informando que o email foi enviado
 * com sucesso
 */
$app->get('/admin/forgot/sent', function()
{
	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);

	$page->setTpl('forgot-sent');
});

///////////////////////////////////////////////////////////////////////
/**Agora vamos criar uma rota para que quando o usuário clicar no link que foi enviado
 * por email. O usuário seja redirecionado para a página onde ele fará a correção da 
 * senha
 */
$app->get('/admin/forgot/reset', function()
{
	/**Temos que validar qual usuário está validando a senha pelo método validForgotDecrypt
	 * da classe User passando o código que foi gerado na classe getForgot.Esse código foi passado
	 * na página forgot-reset via get. Na função validForgotDecrypt vamos verificar se o 
	 * código fornecido é o mesmo do banco de dados então um usuário é retornado
	 */
	$user = User::validForgotDecrypt($_GET['code']);

	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);
	
	/**Passamos os dados que são pedidos no template e que foram recuperados na função validForgotDecrypt */
	$page->setTpl('forgot-reset', array(
			'name'=>$user['desperson'],
			'code'=>$_GET['code']
	));
});

////////////////////////////////////////////////////////////////////////
/**Rota que será utilizada quando o cliente digitar uma senha no templete
 * forgot-reset e enviar
 */
$app->post('/admin/forgot/reset', function()
{
	/**Temos que validar qual usuário está validando a senha pelo método validForgotDecrypt
	 * da classe User passando o código que foi gerado na classe getForgot.Esse código foi passado
	 * na página forgot-reset via get. Na função validForgotDecrypt vamos verificar se o 
	 * código fornecido é o mesmo do banco de dados então um usuário é retornado
	 */
	$forgot = User::validForgotDecrypt($_POST['code']);

	/**Vamos agora criar um método que irá falar para o banco de dados que esta recuperação já foi utilizada */
	User::setForgotUsed($forgot['idrecovery']);

	/**cria um novo objeto que pegará o iduser do usuário que solicitou a alteração da senha */
	$user  = new User();
	$user->get((int)$forgot['iduser']);

	/**temos que criptografar a senha para ser gravada no banco utilizando a api de criptografia do php 
	 * password_hash
	*/
	$password = password_hash($_POST['password'],PASSWORD_DEFAULT,['cost'=>12]);
	
	/**esta função irá cadastrar a nova senha que foi passada via post e foi convertida em um hash */
	$user->setPassword($password);

	/**vamo enviar uma tela para o usuário para confirmar que a tela foi alterada */
	$page = new PageAdmin([
		'header'=>false,
		'footer'=>false
	]);
	
	/**Aqui geramos o template */
	$page->setTpl('forgot-reset-success');

});


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

//////////////////////////////////////////////////////////
$app->run();

 ?>