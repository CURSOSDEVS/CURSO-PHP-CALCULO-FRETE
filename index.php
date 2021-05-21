<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

//configura mensagem de erro
$app->config('debug', true);

//inicio da primeira rota
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});
//fim da primeira rota

//inicio da segunda rota, da página Admin
$app->get('/admin', function() {
    
	$page = new PageAdmin();

	$page->setTpl("index");

});
//fim da segunda rota

$app->run();

 ?>