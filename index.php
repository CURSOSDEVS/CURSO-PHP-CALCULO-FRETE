<?php 

session_start();
require_once("vendor/autoload.php");


use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use Hcode\Model\Category;

$app = new Slim();

require_once("functions.php");
require_once("rotas-site.php");
require_once("rotas-admin-login.php");
require_once("rotas-admin-user.php");
require_once("rotas-admin-category.php");
require_once("rotas-admin-products.php");

//configura mensagem de erro
$app->config('debug', true);

//////////////////////////////////////////////////////////
$app->run();

?>