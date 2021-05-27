<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Product extends Model
{
    /**metodo estático para listar todos os produtos */
    public static function listAll()
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products ORDER BY idproduct");

        return $results;
    }

    //método para verificar se na lista possui a foto e se nao incluir nos dados passados para
    //a página
    public static function checkList($list)
    {
        //percorre o array dos produtos buscados e como no banco de dados 
        //não existe a informação da foto, esta informação
        foreach ($list as &$row) {
            $p = new Product();
            $p->setData($row);

            //alteramos a o valor da linha com o valor da url setada no método checkphoto 
            //que está dentro do método getValues
            $row = $p->getValues();
        }
        //retorna o array list com os dados já tratados
        return $list;
    }

    /**metodo para criar os produtos */
    public function save()
    {
        $sql = new Sql();

        $results =  $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth,
           :vlheight, :vllength, :vlweight, :desurl )", array(
            ':idproduct'=>$this->getidproduct(),
            ':desproduct'=>$this->getdesproduct(),
            ':vlprice'=>$this->getvlprice(),
            ':vlwidth'=>$this->getvlwidth(),
            ':vlheight'=>$this->getvlheight(),
            ':vllength'=>$this->getvllength(),
            ':vlweight'=>$this->getvlweight(),
            ':desurl'=>$this->getdesurl() 
        ));

       // var_dump($results[0]);

        $this->setData($results[0]);

    
    }

    /**metodo para carregar um produto pelo id */
    public function get($idproduct)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$idproduct ]
        );

        $this->setData($results[0]);
    }

    /**Método para exclusão de produtos */
    public function delete()
    {
        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()]
        );
    }

    //método para verificar se existe uma photo na pasta
    public function checkPhoto()
    {
        if(file_exists($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
                'res'.DIRECTORY_SEPARATOR.
                'site'.DIRECTORY_SEPARATOR.
                'img'.DIRECTORY_SEPARATOR.
                'products'.DIRECTORY_SEPARATOR.
                $this->getidproduct() . ".jpg"    
            ))
        {
            $url = '/res/site/img/products/'.$this->getidproduct().'.jpg';
        }
        else
        {
            //se não existir esta foto cadastrada retornamos uma foto padrão
            $url = '/res/site/img/product.jpg';
        }

        //utilizando a classe Model setamos o atributo desphoto
        return $this->setdesphoto($url);

    }   
    

    //reencrevendo o método getValues da classe Model para incluirmos de forma automática
    //o método para chechar se a foto existe 
    public function getValues()
    {
        
        //metodo para verificar se foi adicionado uma foto do produto, podendo fornecer uma foto padrão
        //em caso de não ser fornecido uma foto
        $this->checkPhoto();
        
        $values = parent::getValues();

        return $values;
    }

    //metodo para setar a foto
    public function setPhoto($file)
    {   
        //verificando qual a extenção do arquivo, utilizamos o método explode para encontrar o 
        //ponto e apartir dai criar um array do restante
        $extension = explode('.', $file['name']);

        //falamos que a extenção é ultima posição do array 
        $extension = end($extension);
        

        //faremos uma seleção do tipo de extenção fornecido
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                //utilizando a função do GD utilizando o nome temporário da variável name que está no servidor
                $image = imagecreatefromjpeg($file['tmp_name']);
                break;
            case 'gif':
                $image = imagecreatefromgif($file['tmp_name']);
                break;
            case 'png':
                $image = imagecreatefrompng($file['tmp_name']);
                break;     
        }

        //Destino da foto
        $dist = $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        'res'.DIRECTORY_SEPARATOR.
        'site'.DIRECTORY_SEPARATOR.
        'img'.DIRECTORY_SEPARATOR.
        'products'.DIRECTORY_SEPARATOR.
        $this->getidproduct() . '.jpg' ;

        //função para salvar a imagem no destino 
        imagejpeg($image, $dist);

        //destruido a imagem da memória
        imagedestroy($image);

        //seta a foto 
        $this->checkPhoto();

    }

}


?>