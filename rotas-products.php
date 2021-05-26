<?php

use \Hcode\Model\Product;
use \Hcode\Model\User;
use \Hcode\PageAdmin;

/**Rota para carregar a página principal com os produtos */
$app->get('/admin/products', function()
{
    User::verifyLogin();

    $products = Product::listAll();

    $page = new PageAdmin();

    $page->setTpl('products', [
        'products'=>$products
    ]);

});

//abrir a página para criação de novos produtos
$app->get('/admin/products/create', function()
{
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl('products-create');

});

//confirmar a criação do novo produto e voltar para página de produtos
$app->post('/admin/products/create', function()
{
    User::verifyLogin();

    $product = new Product();

    $product->setData($_POST);

    $product->save();

    header("Location: /admin/products");

    exit;

});

//carregar a página para edição do produto
$app->get('/admin/products/:idproduct', function($idproduct)
{
    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $page = new PageAdmin();

    $page->setTpl('products-update', [
        'product'=>$product->getValues()
    ]);

});

//realizar a alteração do produto
$app->post('/admin/products/:idproduct', function($idproduct)
{

    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->setData($_POST);

    $product->save();

    //metodo para fazer o upload dos produtos
    if(file_exists($_FILES['file']['tmp_name']) || is_uploaded_file($_FILES['file']['tmp_name'])) 
    {
        $product->setPhoto($_FILES["file"]);
    }

    header("Location: /admin/products");
    exit;
});

//realizar a exclusão do produto
$app->get('/admin/products/:idproduct/delete', function($idproduct)
{
    $product = new Product();

    $product->get((int)$idproduct);

    $product->delete();

    header("Location: /admin/products");
    exit;
});

?>