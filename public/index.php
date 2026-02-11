<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

// Ajusta o base path de forma robusta (suporta /index.php em subdiretórios)
if (isset($_SERVER['SCRIPT_NAME'])) {
	$scriptName = $_SERVER['SCRIPT_NAME'];
	$basePath = str_replace('\\', '/', dirname($scriptName));
	if ($basePath === '/' || $basePath === '.' || $basePath === '\\') {
		$basePath = '';
	}
	$app->setBasePath(rtrim($basePath, '/'));
}

	// Middleware temporário de logging para diagnosticar rotas não encontradas
	$app->add(function ($request, $handler) {
		$uri = $request->getUri();
		$path = $uri->getPath();
		$query = $uri->getQuery();
		error_log('[SLIM-DEBUG] Request path: ' . $path . ' Query: ' . $query);
		error_log('[SLIM-DEBUG] SERVER REQUEST_URI: ' . ($_SERVER['REQUEST_URI'] ?? ''));
		error_log('[SLIM-DEBUG] SERVER SCRIPT_NAME: ' . ($_SERVER['SCRIPT_NAME'] ?? ''));
		return $handler->handle($request);
	});

// Adiciona o middleware de roteamento e de tratamento de erros
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

require __DIR__ . '/../app/helper/settings.php';
require __DIR__ . '/../app/route/route.php';

$app->run();
