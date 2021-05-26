<?php

use \Hcode\Page;

//inicio da primeira rota
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

?>