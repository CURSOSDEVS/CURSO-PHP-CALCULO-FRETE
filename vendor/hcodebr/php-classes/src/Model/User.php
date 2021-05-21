<?php

//onde a classe está localizada
namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

/**Como a classe User é um model ela sempre terá gets e seters 
* por isso vamos criar uma classe Model que terá os métodos
* para criação dos gets e seters.
*/

class User extends Model
{
    const SESSION = "User";

    public static function login($login, $password)
    {
        //vamos acessar o banco de dados 
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN"=>$login
        ));

        /**se não encontrou algum resultado 
         * será enviado uma excessão
         * */
        if(count($results) === 0)
        {
            //como é uma exceção criada deve-se
            //adiciona a contrabarra
            throw new \Exception("Usuário inexistente ou senha inválida.",1);
        }

        //caso encontre um usuário o resultado 
        //será armazenado em $data
        $data = $results[0];

        /**vamos verificar a senha do usuário com uma
         * função nativa do php, se a senha for a correta
         * criaremos uma novo objeto de User
         */
        if (password_verify($password, $data["despassword"]) === true)
        {
            $user = new User();
            
            /** definindo um método que não foi criado
             * aqui nesta classe mas será criado automaticamente
             * na classe Model
             */
            $user->setData($data);

            //aqui vamos criar uma sessão do login
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;

            //var_dump($user);
            //exit;

        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.",1);
        }

    }

    public static function verifyLogin($inadmin = true)
    {
        /**verifica se a sessão não foi definida
         * , se não é falsa, se o id do usuário não existe,
         * e se ele não é um administrador e redireciona
         * para a tela de login
         * */
       
        if(
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
            )
        {
            header("Location: /admin/login");
            exit;
        }
    }

    /**função para sair e apagar a sessão */
    public static function logout()
    {
        $_SESSION[User::SESSION] = NULL;
    }
}


?>