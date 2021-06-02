<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;
use Hcode\Mailer;
use Hcode\Model\User;

class Cart extends Model
{

    //constante que irá armazenar a sessão atual para ser utilizada na 
    //inserção do carrinho
    const SESSION = "CartSession";

    //método estático que irá verificar se existe uma sessão aberta e 
    //se o id da sessão ainda é valido.
    public static function getFromSession()
    {
        //cria um objeto de carrinho vazio
        $cart = new Cart();

        //verifica se a sessão já existe e se o id for maior que zero
        //isso significa que o carrinho já foi inserido no banco e já está na sessão
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0 )
        {
            //então será carregado o carrinho
            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }
        else
        {
            $cart->getFromSessionID();

            //se não for encontrado o carrinho será 
            //criado os dados do carrinho
            if(!(int)$cart->getidcart() > 0)
            {
                //captura a id da sessão
                $data = [
                    'dessessionid'=>session_id()
                ];
                //temos que capturar o usuário caso ele esteja logado
                //se não será retornado o usuário novo
                if(User::checkLogin(false))
                {   
                    //pelo método checklogin verificamos se o usuário está
                    //logado para então pegar o id da sessão e salvar os 
                    //dados
                    $user = User::getFromSession();

                    //salva o id do usuário
                    $data['iduser']= $user->getiduser();
                }

                //seta os dados com os métodos get e set
                $cart->setData($data);

                //salva os dados no banco de dados;
                $cart->save();
                
                //vamos salvar o carrinho na sessão para
                //que não seja criado outro carrinho enquando
                //a sessão estiver ativa
                $cart->setToSession();

                
            }
        }
    }

    //colamos o carrinho na sessão para que não seja 
    //criado outro carrinho ainda com a sessão ativa
    public function setToSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    //metodo para carregar o carrinho pelo session_ID
    public function getFromSessionID()
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_carts WHERE idcart = :idcart', [
                        ':idcart'=>session_id()
        ]);
        
        //somente será setado os dados em caso da busca
        //retornar um carrinho
        if(count($results) > 0)
        {
            $this->setData($results[0]);
        }
        
    }

    //metodo para carregar o carrinho pelo id
    public function get(int $idcart)
    {
        $sql = new Sql();

        $results = $sql->select('SELECT * FROM tb_carts WHERE idcart = :idcart', [
                        ':idcart'=>$idcart
        ]);
        
        //se houver algum carrinho será realizado o setData
        if(count($results) > 0)
        {
            $this->setData($results[0]);
        }
        
    }

    //metodo para salvar o carrinho
    public function save()
    {
        $sql = new Sql();

        $results = $sql->select('CALL sp_carts_save(:idcart , 
                :dessessionid, :iduser , :deszipcode, :vlfreight, :nrdays )',[
                    ':idcart'=>$this->getidcart(), 
                    ':dessessionid'=>$this->getdessessionid(),
                    ':iduser'=>$this->getiduser(),
                    ':deszipcode'=>$this->getdeszipcode(),
                    ':vlfreight'=>$this->getvlfreight(),
                    ':nrdays'=>$this->getpnrdays()
                ]);

        $this->setData($results[0]);
    }

}


?>