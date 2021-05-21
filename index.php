<?php 

session_start();
require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

$app->run();

 ?>