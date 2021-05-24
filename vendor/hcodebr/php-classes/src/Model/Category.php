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

    }


}


?>