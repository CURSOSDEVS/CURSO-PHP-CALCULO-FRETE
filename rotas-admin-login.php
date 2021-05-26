<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;


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


///////////////////////////////////////////////////////////
/**rota para carregar a tela de esqueceu a senha */
$app->get('/admin/forgot', function()
{
//	User::verifyLogin();

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


?>