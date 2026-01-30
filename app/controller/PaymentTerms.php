<?php

namespace app\controller;

class PaymentTerms extends Base
{
    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de termos de pagamento'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listpaymentterms'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
}