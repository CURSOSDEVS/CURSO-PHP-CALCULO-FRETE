<?php

//onde a classe está localizada
namespace Hcode\Model;

use \Hcode\DB\Sql;
use Hcode\Mailer;
use \Hcode\Model;

/**Como a classe User é um model ela sempre terá gets e seters 
* por isso vamos criar uma classe Model que terá os métodos
* para criação dos gets e seters.
*/

class User extends Model
{
    const SESSION = "User";

    //constantes que serão utilizados na criação do código criptografado enviado como link para o usuário
    const SECRET = "HcodePhp7_Secret";
	const SECRET_IV = "HcodePhp7_Secret_IV";

    //metodo para verificar se a sessão já existe
    public static function getFromSession()
    {
        $user = new User();

        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0)
        {           
            $user->setData($_SESSION[User::SESSION]);        
        }

        return $user;
    }

    //metodo pra checar se o usuario está logado
    //sem retornar nenhuma página 
    public static function checkLogin($inadmin = true)
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
        ){
            //não está logado
            return false;
        }else{
            //irá verificar se o usuário está logado como
            //administrador
            if($inadmin === true && (bool)$_SESSION[User::SESSION]['inadmin'] === true)
            {
                return true;
            }else if($inadmin === false){
                //o usuário está logado mas não é um administrador
                return true;
            }else {
                //se algo for diferente desta logica
                //o usuáario não está logado
                return false;
            }

        }
    }

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
    //função que verifica se o usuário continua logado 
    public static function verifyLogin($inadmin = true)
    {
        
        if(!User::checkLogin($inadmin))
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

    ///////////////////////////////////////////////////
    /**Metodo que fará a verificação se e um usuário que esqueceu a senha */
    public static function getForgot($email)
    {
        /**Verificando se o email está cadastrado no bando de dados, para isso vamos criar um objeto
         * da classe Sql que já possui os métodos de banco de dados
        */
        $sql = new Sql();

        /**Utilizando o método selelect da classe Sql, pegaremos o idperson na tabela tb_person através do email
         * informado, com esse idperson vamos pegar na tabela tb_users o id_users para inserir na tabela de 
         * recuperação de senha o id do usuário, a data  */ 
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email",
            array(':email'=>$email));
        
        /**verificando se encontrou o email  */
       // var_dump($results);
        if(count($results) === 0 )
        {
            throw new \Exception("Não foi possível recuperar a senha");
        }
        else
        {
            /**como foi encontrado o email, será necessário criar um novo registro na tabela de recuperação
             * de senha, utilizando uma procedure. passamos o resultado da busca anterior para uma variável
             * para termos acesso aos dados e passados para a storeprocedure
             */
            $data = $results[0];

            /**O resultado será armazenado em uma variável */
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data['iduser'],
				":desip"=>$_SERVER['REMOTE_ADDR']
			));

            /**verificamos se a procedure foi executada e se o resultado foi armazenado em results2 */
            if(count($results2) === 0)
            {
                throw new \Exception("Não foi possível recuperar a senha.");
            }
            else
            {
                /**Como foi realizado a gravação na tabela de userspasswordrecoveries, teremos que gerar
                 * um código criptografado para o email do usuário, esse código criptografado irá utilizar
                 * o idrecovery que foi criado na tabela. Será enviado um link para o email do usuário*/
                $dataRecovery = $results2[0];
                var_dump($dataRecovery);
                
                /**Criamos uma variável code que terá então o código gerado pela função nativa php openssl_encrypt
                 * que possui os parâmetros: texto que será criptografado, tipo de criptografia, uma primeira chave
                 * convertida para string de 16 caracteres(foi utilizado a função pack que irá pegar a constante
                 * SECRET e converterá para 16 caracteres), 0, uma segunda chave no formato de 16 caracteres.
                 */
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET),
                 0, pack("a16", User::SECRET_IV));

                 /**Convertemos o codigo encriptografado para 64 bits com a função php base64_encode */
				$code = base64_encode($code);

                /**vamos agora criar um link que será enviado para o usuário, esse link terá a rota para ele
                 * cadastrar a nova senha e o código encriptografado passado via get.
                 */
                $link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

                /**vamos enviar esse código para o usuário via email utilizando a factory mailer criada para
                 * envio de emails, passando os parâmetros necessário para a classe factory:
                 * - O email do usuário está guardado na variavel $data pois validamos o email informado e guardamos
                 * nesta variável.
                 * - O nome do usuário também está na variável $data
                 * - O assunto do email
                 * - O nome do template que será desenhado e deverá estar em uma das pastas views
                 * - Um array com os dados que serão utilizados dentro do template forgot, basta abrir o template e ver
                 * as variáveis que estão entre chaves no código html. neste caso {name} e {link} 
                 */
                $mailer = new Mailer($data['desemail'],$data['desperson'],'Redefinir Senha da Hcode Store','forgot', array(
                        'name'=>$data['desperson'],
                        'link'=>$link
                ));

                $mailer->send();

                /**faremos ainda o retorno da variável $data que contém os dados do usuário que foi recuperado 
                 * caso o metodo precise para alguma coisa */
                return $data;
            }
        }

    }

    /////////////////////////////////////////////////////
    /**Na função validForgotDecrypt vamos verificar se o 
	 * código fornecido é o mesmo do banco de dados então um usuário é retornado  */
    public static function validForgotDecrypt($code)
    {
        /**vamos agora decriptar o código para ver se é mesmo o usuário  */
        $code = base64_decode($code);

        /**vamos utilizar agora a função openssl_decrypt para verificar se a chave é a correta */
        $idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));
        
        /**agora vamos verificar se o $idrecovery confere com o idrecovery gravado no banco de dados e se ele é valido
         * , se já foi uzado, se está dentro de uma hora que é o tempo que será utilizado. Para isso vamos 
         * consultar o banco de dados
         */
        $sql = new Sql();

        $results = $sql->select("SELECT *
        FROM tb_userspasswordsrecoveries a
        INNER JOIN tb_users b USING(iduser)
        INNER JOIN tb_persons c USING(idperson)
        WHERE
            a.idrecovery = :idrecovery
            AND
            a.dtrecovery IS NULL
            AND
            DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW(); ", array(
        ":idrecovery"=>$idrecovery));

        /**verificando se o foi retornado algum resultado */
        if(count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha");            
        }
        else
        {
            /**vamos então devolver os dos deste usuário recuperado */
            return $results[0];
        }
    }

    //////////////////////////////////////////////////////
    /** metodo que irá cadastrar a data e hora em que foi solicitada a recuperação de senha */
    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();
        /**Vamos utilizar uma query para atualizar a tabela tb_userspasswordsrecoveries com a
         * data e hora que a nova senha foi criada
         */
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery",
            array(':idrecovery'=>$idrecovery));
    }
    /////////////////////////////////////////////////////////
    /**método que irá converter a senha passada para um hash e salvar no bando de dados*/
    public function setPassword($password)
    {
        /**trocando a senha no banco de dados */
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword=:password WHERE iduser=:iduser",array(
            ':password'=>$password,
            ':iduser'=>$this->getiduser()
        ));

    }


}


?>