<?php

namespace app\controller;

use app\database\builder\DeleteQuery;
use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;
use app\database\builder\UpdateQuery;

class PaymentTerms extends Base
{
    public function lista($request, $response)
    {
        $templaData = [
            'titulo' => 'Lista de termos de pagamento'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listpaymentterms'), $templaData)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'acao' => 'c',
            'titulo' => 'Cadastro de termos de pagamento'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('paymentterms'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function alterar($request, $response, $args)
    {
        $id = $args['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            $dadosTemplate = [
                'acao' => 'c',
                'id' => '',
                'titulo' => 'Cadastro de termos de pagamento',
                'termo' => null,
                'parcelas' => []
            ];
            return $this->getTwig()
                ->render($response, $this->setView('paymentterms'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        }

        $termo = SelectQuery::select()->from('payment_terms')->where('id', '=', $id)->fetch();
        $parcelas = SelectQuery::select()->from('payment_installments')->where('payment_term_id', '=', $id)->fetchAll();

        $dadosTemplate = [
            'acao' => 'e',
            'id' => $id,
            'titulo' => 'Cadastro de termos de pagamento',
            'termo' => $termo,
            'parcelas' => $parcelas
        ];
        return $this->getTwig()
            ->render($response, $this->setView('paymentterms'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }

    public function listTerms($request, $response)
    {
        $form = $request->getParsedBody() ?? [];
        $order = isset($form['order'][0]['column']) && $form['order'][0]['column'] !== '' ? $form['order'][0]['column'] : 0;
        $orderType = isset($form['order'][0]['dir']) && $form['order'][0]['dir'] !== '' ? $form['order'][0]['dir'] : 'desc';
        $start = $form['start'] ?? 0;
        $length = $form['length'] ?? 10;

        $fields = [
            0 => 'id',
            1 => 'descricao',
            2 => 'ativo'
        ];

        $orderField = $fields[$order] ?? 'id';
        $term = $form['search']['value'] ?? '';

        $query = SelectQuery::select('id,descricao,ativo')->from('payment_terms');

        if ($term !== '') {
            $query->where('payment_terms.descricao', 'ilike', "%{$term}%");
        }

        $termos = $query
            ->order($orderField, $orderType)
            ->limit($length, $start)
            ->fetchAll();

        $userData = [];
        foreach ($termos as $key => $value) {
            $userData[$key] = [
                $value['id'],
                $value['descricao'],
                $value['ativo'] ? 'Sim' : 'Não',
                "<a href='/pagamento/alterar/{$value['id']}' class='btn btn-warning btn-sm'>Editar</a>
                 <button type='button' onclick='Delete(" . $value['id'] . ");' class='btn btn-danger btn-sm'>Excluir</button>"
            ];
        }

        $data = [
            'status' => true,
            'recordsTotal' => count($termos),
            'recordsFiltered' => count($termos),
            'data' => $userData
        ];

        $payload = json_encode($data);
        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    }

    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody() ?? [];

            $FieldAndValues = [
                'descricao' => $form['descricao'] ?? '',
                'ativo' => (isset($form['ativo']) && ($form['ativo'] === true || $form['ativo'] === 'true' || $form['ativo'] === '1')) ? 1 : 0
            ];

            $con = \app\database\Connection::connection();
            $IsInsert = InsertQuery::table('payment_terms')->save($FieldAndValues);

            if (!$IsInsert) {
                $data = [
                    'status' => false,
                    'msg' => 'Erro ao inserir termo de pagamento',
                    'id' => 0
                ];
                $payload = json_encode($data);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }

        

            // Inserir parcelas
            $lastId = (int)$con->lastInsertId();
            if (isset($form['parcelas']) && is_array($form['parcelas'])) {
                foreach ($form['parcelas'] as $parcela) {
                    $parcelaData = [
                        'payment_term_id' => $lastId,
                        'numero_parcelas' => (int)($parcela['numero_parcelas'] ?? 0),
                        'intervalo_dias' => (int)($parcela['intervalo_dias'] ?? 0),
                        'alterar_vencimento_dias' => (int)($parcela['alterar_vencimento_dias'] ?? 0)
                    ];
                    InsertQuery::table('payment_installments')->save($parcelaData);
                }
            }

            $data = [
                'status' => true,
                'msg' => 'Termo de pagamento inserido com sucesso',
                'id' => $lastId
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $data = [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage(),
                'id' => 0
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    public function insertInstallment($request, $response)
    {
        #Captura os dados do front-end.
        $form = $request->getParsedBody();

        $dataResponse = [
            'status' => true,
            'msg' => 'Cadastro realizado com sucesso!',
            'id' => 123
        ];

        #Retorno de teste.
        return $this->SendJson($response, $dataResponse, 201);
    }
    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody() ?? [];
            $id = $form['id'] ?? 0;

            $FieldAndValues = [
                'descricao' => $form['descricao'] ?? '',
                'ativo' => (isset($form['ativo']) && ($form['ativo'] === true || $form['ativo'] === 'true' || $form['ativo'] === '1')) ? 1 : 0,
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];

            $IsUpdate = UpdateQuery::table('payment_terms')
                ->set($FieldAndValues)
                ->where('id', '=', $id)
                ->update();

            if (!$IsUpdate) {
                $data = [
                    'status' => false,
                    'msg' => 'Erro ao atualizar termo de pagamento'
                ];
                $payload = json_encode($data);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }

            // Deletar e re-inserir parcelas
            DeleteQuery::table('payment_installments')
                ->where('payment_term_id', '=', $id)
                ->delete();

            if (isset($form['parcelas']) && is_array($form['parcelas'])) {
                foreach ($form['parcelas'] as $parcela) {
                    $parcelaData = [
                        'payment_term_id' => $id,
                        'numero_parcelas' => (int)($parcela['numero_parcelas'] ?? 0),
                        'intervalo_dias' => (int)($parcela['intervalo_dias'] ?? 0),
                        'alterar_vencimento_dias' => (int)($parcela['alterar_vencimento_dias'] ?? 0)
                    ];
                    InsertQuery::table('payment_installments')->save($parcelaData);
                }
            }

            $data = [
                'status' => true,
                'msg' => 'Termo de pagamento atualizado com sucesso'
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $data = [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }

    public function delete($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'];

            $IsDelete = DeleteQuery::table('payment_terms')
                ->where('id', '=', $id)
                ->delete();

            if (!$IsDelete) {
                $data = [
                    'status' => false,
                    'msg' => 'Erro ao deletar termo de pagamento'
                ];
                $payload = json_encode($data);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(200);
            }

            $data = [
                'status' => true,
                'msg' => 'Termo de pagamento deletado com sucesso'
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Exception $e) {
            $data = [
                'status' => false,
                'msg' => 'Erro: ' . $e->getMessage()
            ];
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }
}
