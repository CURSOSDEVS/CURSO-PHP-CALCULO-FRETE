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

    //metodo estático para listar todos os dados da tabela
    public static function listAll()
    {
        $sql = new Sql();

        //faremos um join da tabela tb_users e tb_persons, iremos utilizar o argumento USING pois as duas tabelas 
        //possuem um campo com mesmo nome
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    //metodo publico para salvar o novo usuário no banco de dados
    public function save()
    {
        $sql = new Sql();

        //será chamado uma procedure de sql para cadastrar o novo usuário e a nova pessoa e coletar os dados 
        //das duas tabelas e levar para a aplicação
        /*campos da tabela 
        pdesperson VARCHAR(64), 
        pdeslogin VARCHAR(64), 
        pdespassword VARCHAR(256), 
        pdesemail VARCHAR(128), 
        pnrphone BIGINT, 
        pinadmin TINYINT
         */
        //no parâmetro array nos pegaremos os dados que estão no objeto através dos metodos get que foram criados automaticamente
        //e passamos para os parâmetros. pelo metodo saveData da classe Model
        $results =  $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

        //somente nos interessa um alinha desse resultado, vamos retornar esse valor 
        $this->setData($results[0]);

    }

    //metodo publico para obter os dados de um usuário
    public function get($iduser)
    {
        //vamos no banco de dados carregar este usuário
        $sql = new Sql();

        //na variável results passamos a resposta da busca
        //o array é para passar o valor da variável iduser para o parâmetro :iduser
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
                ":iduser"=>$iduser
        ));

        //passamos o resultado para o método que trata os dados
        $this->setData($results[0]);

    }

    //metodo para alteração dos dados do usuário
    public function update()
    {
        $sql = new Sql();

        //será chamado uma procedure de sql para cadastrar o novo usuário e a nova pessoa e coletar os dados 
        //das duas tabelas e levar para a aplicação
        /*campos da tabela 
        pdesperson VARCHAR(64), pdeslogin VARCHAR(64), pdespassword VARCHAR(256), pdesemail VARCHAR(128), pnrphone BIGINT, 
        pinadmin TINYINT
         */
        //no parâmetro array nos pegaremos os dados que estão no objeto através dos metodos get que foram criados automaticamente
        //e passamos para os parâmetros. pelo metodo saveData da classe Model
        $results =  $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()
        ));

        //somente nos interessa um alinha desse resultado, vamos retornar esse valor 
        $this->setData($results[0]);
    }

    //metodo para apagar o usuário
    public function delete()
    {
        $sql = new Sql();

        //utilizando uma store procedure iremos apagar o usuário com o id passado pelo user
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }
}


?>