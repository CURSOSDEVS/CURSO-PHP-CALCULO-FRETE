<?php

/** este namespace foi configurado
 * no autoload.php do vendor
 */
namespace Hcode;

/**vamos utilizar outras classes
 * de outros namespaces por isso
 * usamos uses
 */
use Rain\Tpl;

class Page{

    private $tpl;
    private $options = [];
    private $defaults = [
        "data"=>[]
    ];

    public function __construct($opts = array(), $tpl_dir = "/views/")
    {
        /** será mesclado o array defaults e opts no array options
         * caso não seja informado nada será considerado o defaults
        */
        $this->options = array_merge($this->defaults, $opts);
        // configura 
	    $config = array(
            /**aqui vamos configurar qual o caminho onde
             * estarão as paginas
             */
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"].$tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );

        //criamos o template
        $this->tpl = new Tpl;

        /**criamos um loop para percorrer o array de opções
         * informados. nesse array estão as variáveis informadas
         * 
         */

        $this->setData($this->options["data"]);
        
        //abrirá o arquivo header que estará na pasta /views/
        $this->tpl->draw("header");

    }

    /** criando um metódo que receberá os dados */
    private function setData($data = array())
    {
        foreach($data as $Key => $value){
            $this->tpl->assign($Key, $value);
        }
    }

    /**criando método do conteúdo da página que
     * recebe o template da pagina e os dados,
    */
    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        //recebendo os dados
        $this->setData($data);

        //recebendo o template
        return $this->tpl->draw($name, $returnHTML);

    }


    //ao ser destruido a página será exibido um rodapé
    public function __destruct()
    {

        $this->tpl->draw("footer");
    }
}

?>