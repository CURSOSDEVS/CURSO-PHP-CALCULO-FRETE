<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;

$app = new Slim();

//configura mensagem de erro
$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->run();

 ?>