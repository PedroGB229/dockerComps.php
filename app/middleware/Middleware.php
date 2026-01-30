<?php

namespace app\middleware;

class Middleware
{
    public static function route()
    {
        $middleware = function ($request, $handler) {
            $response = $handler->handle($request);
            #CAPTURAMOS O METODOS DE REQUISIÇÃO.
            $method = $request->getMethod();
            #CAPTURAMOS A PAGINA SOLICITADA PELO USUÁRIO
            $pagina = $request->getRequestTarget();
            #CASO METODO SEJA GET VALIDAMOS O NIVEL DE ACESSO.
            if ($method == 'GET') {
                # SE O USUÁRIO ESTÁ LOGADO, REGENERA O ID DA SESSÃO PARA RENOVAR O TEMPO DE EXPIRAÇÃO DO COOKIE.
                if (isset($_SESSION['users']) && boolval($_SESSION['users']['logado'])) {
                    # O parâmetro 'true' remove o arquivo de sessão antigo do servidor.
                    session_regenerate_id(true);
                }
                #Se já está logado e tenta acessar /login, redireciona para HOME
                if ($pagina == '/login' && isset($_SESSION['users']) && boolval($_SESSION['users']['logado'])) {
                    return $response->withHeader('Location', HOME)->withStatus(302);
                }
              
            }
            return $response;
        };
        return $middleware;
    }
}