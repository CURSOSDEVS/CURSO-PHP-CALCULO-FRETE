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
    const SESSION = "Cart";

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

        return $cart;
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

    //metodo para adiconar produtos ao carrinho
    public function addProducts(Product $product)
    {
        $sql = new Sql();

        $sql->query('INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)', [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
        ]);

    }

    //metodo para remover produtos do carrinho
    public function removeProducts(Product $product, $all = false)
    {
        $sql = new Sql();
        //remove todos os produtos de mesmo id
        if($all)
        {
            //serão removidos todas as quantidades de um mesmo produto
            $sql->query('UPDATE tb_cartsproducts SET dtremoved = NOW() 
                        WHERE idcart = :idcart AND idproduct = :idproduct
                        AND dtremoved IS NULL', [
                            ':idcart'=>$this->getidcart(),
                            ':idproduct'=>$product->getidproduct()
                        ]);
        }else
        {
            //A única diferença será que nesta query será reduzido 
            //a quantidade do produto em 1
            $sql->query('UPDATE tb_cartsproducts SET dtremoved = NOW() 
                        WHERE idcart = :idcart AND idproduct = :idproduct 
                        AND dtremoved IS NULL LIMIT 1', [
                            ':idcart'=>$this->getidcart(),
                            ':idproduct'=>$product->getidproduct()
                        ]);
        }
    }

    //metodo para retornar os produtos que estão em um carrinho
    public function getProducts()
    {
        $sql = new Sql();
        //Retorna todos os produtos que estão dentro do carrinho e que 
        //a data de remoção é nula 
        //A cláusula GROUP BY é utilizada para retornar a quantidade do mesmo
        //produto dentro da tabela 
        
        $rows = $sql->select(
               'SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl,
               COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
               FROM tb_cartsproducts a 
               INNER JOIN tb_products b 
               ON a.idproduct = b.idproduct 
               WHERE a.idcart = :idcart 
               AND a.dtremoved IS NULL
                GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
                ORDER BY b.desproduct', [
                        ':idcart'=>$this->getidcart()
                        ]);

        //utilizamos tambem o metodo estático checkList da classe produto
        //para verificar se o produto possui foto e inclui-la nos objetos de produto
        return Product::checkList($rows);

    }

}


?>