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
    const SESSION_ERROR = "CartError";

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
                    ':nrdays'=>$this->getnrdays()
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

        //atualizando o campo de total, subtotal e frete da página do carrinho
        $this->getCalculateTotal();

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

         //atualizando o campo de total, subtotal e frete da página do carrinho
        $this->getCalculateTotal();
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

    //metodo para retornar os totais da lista de produtos para
    //facilitar calculo de frete entre outros
    public function getProductsTotals()
    {
        $sql = new Sql();

        $results = $sql->select(
           'SELECT SUM(vlprice) AS vlprice, 
            SUM(vlwidth) AS vlwidth, 
            SUM(vlheight) AS vlheight, 
            SUM(vllength) AS vllength,
            SUM(vlweight) AS vlweight, 
            COUNT(*) AS nrqtd 
            FROM tb_products a
            INNER JOIN tb_cartsproducts b
            ON a.idproduct = b.idproduct
            WHERE b.idcart = :idcart
            AND b.dtremoved IS NULL;
        ', [ ':idcart'=>$this->getidcart()]);

        if(count($results) > 0)
        {
            return $results[0];
        }else
        {
            return [];
        }
    }

    //metodo para calculo do valor do frete 
    public function setFreight($nrzipcode)
    {   
        //utilizamos esta função para retirar o caracter - 
        //informado como padrão no campo de input
        $nrzipcode = str_replace('-', '', $nrzipcode);

        //vamos retornar os totais do carrinho 
        $total = $this->getProductsTotals();

        //var_dump($total);
        //exit;

        //altura minima é 2 cm
        if($total['vlheight']<2 ) $total['vlheight'] = 2;
        
        //comprimento minimo 16cm
        if($total['vllength']<16) $total['vllength']=16;
        
        //larguma minima 11 cm
        if($total['vlwidth']<11) $total['vlwidth']=11;

        //vamos verificar se tem algum produto dentro do carrinho
        if($total['nrqtd'] > 0)
        {
            //irá conter as variáveis passadas com todos os dados
            $qs = http_build_query([
                'nCdEmpresa'=>'',
                'sDsSenha'=>'',
                'nCdServico'=>'40010',//tipo de serviço SEDEX Varejo
                'sCepOrigem'=>'09853120',//cep de origem
                'sCepDestino'=>$nrzipcode, //cep de destino informado pelo usuário
                'nVlPeso'=>$total['vlweight'], //peso dos objetos
                'nCdFormato'=>'1', //tipo de formato da encomenda está especificado no manual dos correios
                'nVlComprimento'=>$total['vllength'],
                'nVlAltura'=>$total['vlheight'],
                'nVlLargura'=>$total['vlwidth'],
                'nVlDiametro'=>'0',
                'sCdMaoPropria'=>'S',
                'nVlValorDeclarado'=>$total['vlprice'],
                'sCdAvisoRecebimento'=>'S'
            ]);
            
            $xml = simplexml_load_file('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?'.$qs);

            //$xml = (array)simplexml_load_file('http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?'.$qs);
            //echo json_encode($xml);
           // exit;
            
            //recebe o resultado da consulta ao sistema dos correios
            $results = $xml->Servicos->cServico;
            
            //configurando mensagem de erro caso retorne um erro do serviço do correio
            if($results->MsgErro != '')
            {
                //metodo para setar a mensagem
                Cart::setMsError($results->MsgErro);
            }
            else
            {
                Cart::clearMsgError();
            }


            //pega o prazo de entrega no resultado e seta no objeto cart
            $this->setnrdays($results->PrazoEntrega);

            //pega o valor do frete e seta no cart
            $this->setvlfreight(Cart::formatValueToDecimal($results->Valor));

            //seta o número do cep informado
            $this->setdeszipcode($nrzipcode);

            //salva os dados no banco
            $this->save();

            return $results;
           
        }
        else
        {

        }

    }

    //método para atualizar o carrinho toda vez que um produto e inserido
    //ou removido do carrinho
    public function updateFreight()
    {
        //verifica primeiramente se há um cep inserido no carrinho
        if($this->getdeszipcode() != '')
        {   
            //se houver, o frete será novamente recalculado
            $this->setFreight($this->getdeszipcode());
        }
    }

    //função para formatar a data para o formato brasil
    public static function formatValueToDecimal($value):float
    {
        $value = str_replace('.','', $value); //retira o ponto se tiver
        return str_replace(',','.', $value); //substitui a vírgula por ponto
    }

    //metodo para setar a mensagem através de uma session
    public static function setMsError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    //metodo para receber a mensagem de erro caso exista
    public static function getMsgError()
    {
        //caso tenha uma mensagem de erro ela será retornada, senão não retorna nada
        $msg = (isset($_SESSION[Cart::SESSION_ERROR]))?  $_SESSION[Cart::SESSION_ERROR] : "";

        //limpa a session após pegar a mensagem se existir
        Cart::clearMsgError();

        //retorna a mensagem
        return $msg;
    }

    //metodo para limpar a sessão
    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }

    //sobreescrevendo o metodo getValues() da classe model
    //adicionando o método getCalculateTotal para soma de total e 
    //sub total acidionando estas informações no objeto
    public function getValues()
    {
        $this->getCalculateTotal();

        //returna os valores
        return parent::getValues();
    }

    //metodo para calcular o total e subtotal e incluir no objeto
    public function getCalculateTotal()
    {   
        //atualiza o valor do frete
        $this->updateFreight();

        //o metodo getProductsTotal retoran todos os totais do carrinho
        //preço, largura, comprimento etc...
        $totals = $this->getProductsTotals();

        //criando o campo vlsubtotal que é a soma dos produtos que estao no carrinho
        $this->setvlsubtotal($totals['vlprice']);

        //criando o campo vltotal que é a soma dos produtos e frete do carrinho
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
    }



}


?>
