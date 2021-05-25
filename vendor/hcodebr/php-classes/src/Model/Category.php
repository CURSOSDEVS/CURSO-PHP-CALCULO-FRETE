<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Category extends Model
{
    /**metodo estático para listar todas as categorias */
    public static function listAll()
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories ORDER BY idcategory");

        return $results;
    }


    /**metodo para criar as categorias */
    public function save()
    {
        $sql = new Sql();

        $results =  $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ':idcategory'=>$this->getidcategory(),
            ':descategory'=>$this->getdescategory()
        ));

        $this->setData($results[0]);

        //chamamos o método updateFile para atualizar lista de categorias da página html
        Category::updateFile();

    }

    /**metodo para carregar uma categoria pelo id */
    public function get($idcategory)
    {
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory ]
        );

        $this->setData($results[0]);
    }

    /**Método para exclusão de categorias */
    public function delete()
    {
        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()]
        );

        //chamamos o método updateFile para atualizar lista de categorias da página html
        Category::updateFile();
    }

    /**Metodo estatico para atualizar as categorias na página html principal sempre que
     * for atualizado o menu de categorias
    */
    public static function updateFile()
    {
        //busca do banco de dados as categorias cadastradas
        $categories = Category::listAll();

        //vamos criar a lista de menu dinâmicamente
        $html = [];

        //percorrendo o array categories e incluindo em cada valor as tags
        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }

        //salvando o arquivo no caminho onde está sendo executado o programa e transformando o array numa string 
        //com a função implode
        file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.
        'views'.DIRECTORY_SEPARATOR.'categories-menu.html', implode('',$html));
    }


}


?>