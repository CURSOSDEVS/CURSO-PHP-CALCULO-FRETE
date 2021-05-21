<?php

namespace Hcode;

/**esta classe irá herdar da classe page pois é 
 * semelhante, sendo diferente o caminho
 * 
*/
class PageAdmin extends Page
{
    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    {
        //chamamos p construtor da classe pai
        parent::__construct($opts, $tpl_dir);
    }

}