<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

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

?>