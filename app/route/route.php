<?php

use app\controller\User;
use app\controller\cliente;
use app\controller\Empresa;
use app\controller\Home;
use app\controller\Fornecedor;
use app\controller\Login;
use app\controller\Sale;
use app\controller\PaymentTerms;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', Home::class . ':home');
$app->get('/home', Home::class . ':home');
$app->get('/login', Login::class . ':login');

$app->group('/login', function (RouteCollectorProxy $group) {
    $group->post('/precadastro', Login::class . ':precadastro');
    $group->post('/autenticar', Login::class . ':autenticar');
    $group->post('/recuperar', Login::class . ':recuperar');
    $group->post('/verificarCodigo', Login::class . ':verificarCodigo');
    $group->post('/atualizarSenha', Login::class . ':atualizarSenha');
}); 

$app->group('/usuario', function (RouteCollectorProxy $group) {
    $group->get('/lista', User::class . ':lista');
    $group->get('/cadastro', User::class . ':cadastro');
    $group->post('/listuser', User::class . ':listuser');
    $group->post('/update', User::class . ':update');
    $group->post('/insert', User::class . ':insert');
    $group->get('/alterar/{id}', User::class . ':alterar'); 
    $group->post('/delete', User::class . ':delete');
    $group->get('/print', User::class . ':print'); 
});
$app->group('/pagamento', function (RouteCollectorProxy $group) {
    $group->get('/lista', PaymentTerms::class . ':lista');
    $group->get('/cadastro', PaymentTerms::class . ':cadastro');
    $group->get('/alterar/{id}', PaymentTerms::class . ':alterar');
    $group->post('/listTerms', PaymentTerms::class . ':listTerms');
    $group->post('/insert', PaymentTerms::class . ':insert');
    $group->post('/update', PaymentTerms::class . ':update');
    $group->post('/delete', PaymentTerms::class . ':delete');
});
$app->group('/venda', function (RouteCollectorProxy $group) {
    $group->get('/lista', Sale::class . ':lista');
    $group->get('/cadastro', Sale::class . ':cadastro');
});
$app->group('/cliente', function (RouteCollectorProxy $group) {
    $group->get('/lista', cliente::class . ':lista'); 
    $group->get('/cadastro', cliente::class . ':cadastro');
    $group->post('/listcliente', cliente::class . ':listcliente');
    $group->post('/update', cliente::class . ':update');
    $group->post('/insert', cliente::class . ':insert');
    $group->get('/alterar/{id}', cliente::class . ':alterar');
    $group->post('/delete', cliente::class . ':delete');
});
$app->group('/empresa', function (RouteCollectorProxy $group) {
    $group->get('/lista', Empresa::class . ':lista'); 
    $group->get('/cadastro', Empresa::class . ':cadastro'); 
    $group->post('/listempresa', Empresa::class . ':listempresa');
    $group->post('/update', Empresa::class . ':update');
    $group->post('/insert', Empresa::class . ':insert');
    $group->get('/alterar/{id}', Empresa::class . ':alterar'); 
    $group->post('/delete', Empresa::class . ':delete');
});
$app->group('/fornecedor', function (RouteCollectorProxy $group) {
    $group->get('/lista', Fornecedor::class . ':lista'); 
    $group->get('/cadastro', Fornecedor::class . ':cadastro'); 
    $group->post('/listfornecedor', Fornecedor::class . ':listfornecedor');
    $group->post('/update', Fornecedor::class . ':update');
    $group->post('/insert', Fornecedor::class . ':insert');
    $group->get('/alterar/{id}', Fornecedor::class . ':alterar'); 
    $group->post('/delete', Fornecedor::class . ':delete');
});
