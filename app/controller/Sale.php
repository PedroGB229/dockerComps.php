<?php

namespace app\controller;

use app\database\builder\InsertQuery;
use app\database\builder\SelectQuery;

class Sale extends Base
{
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página inicial',
            'acao' => 'c'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('sale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Página inicial'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listsale'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function insert($request, $response)
    {
        #captura os dados do formulário
        $form = $request->getParsedBody();
        #Captura o id do produto
        $id_produto = $form['pesquisa'];
        #Verificar se o id do produto esta vasio ou nulo
        if (empty($id_produto) or is_null($id_produto)) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: O ID do produto é obrigatório!',
                'id' => 0
            ], 403);
        }
        #seleciona o id do cliente CONSUMIDOR FINAL para inserir a venda
        $customer = SelectQuery::select('id')
            ->from('customer')
            ->order('id', 'asc')
            ->limit(1)
            ->fetch();
        #Verificar se o cliente não foi encontrado
        if (!$customer) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: Nenhum cliente encontrado!',
                'id' => 0
            ], 403);
        }
        #seleciona o id do cliente CONSUMIDOR FINAL para inserir a venda
        $id_customer = $customer['id'];
        $FieldAndValue = [
            'id_customer' => $id_customer,
            'total_bruto' => 0,
            'total_liquido' => 0,
            'desconto' => 0,
            'acrescimo' => 0,
            'observacao' => ''
        ];
        try {
            #Tenta inserir a venda no banco de dados e captura o resultado da inserção
            $IsInserted = InsertQuery::table('sale')->save($FieldAndValue);
            #Verificar se a inserção falhou
            if (!$IsInserted) {
                return $this->SendJson(
                    $response,
                    [
                        'status' => false,
                        'msg' => 'Restrição: Falha ao inserir a venda!',
                        'id' => 0
                    ],
                    403
                );
            }
            #Seleciona o id da venda inserida mais recente para retornar na resposta
            $sale = SelectQuery::select('id')
                ->from('sale')
                ->order('id', 'desc')
                ->limit(1)
                ->fetch();
            #Verificar se a venda não foi encontrada
            if (!$sale) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Nenhuma venda encontrada!',
                    'id' => 0
                ], 403);
            }
            $id_sale = $sale["id"];
            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Venda inserida com sucesso!',
                'id' => $id_sale
            ], 201);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }
    public function listsale($request, $response)
    {
        try {
            #Captura todas a variaveis de forma mais segura VARIAVEIS POST.
            $form = $request->getParsedBody();
            
            #Qual a coluna da tabela deve ser ordenada.
            $order = $form['order'][0]['column'] ?? 0;
            #Tipo de ordenação
            $orderType = $form['order'][0]['dir'] ?? 'asc';
            #Em qual registro se inicia o retorno dos registro, OFFSET
            $start = $form['start'] ?? 0;
            #Limite de registro a serem retornados do banco de dados LIMIT
            $length = $form['length'] ?? 10;
            
            $fields = [
                0 => 'id',
                1 => 'nome_fantasia',
                2 => 'sobrenome_razao',
                3 => 'cpf_cnpj',
                4 => 'rg_ie',
                5 => 'data_nascimento_abertura'
            ];
            
            #Capturamos o nome do campo a ser ordenado.
            $orderField = $fields[$order] ?? 'id';
            #O termo pesquisado
            $term = $form['search']['value'] ?? '';
            
            $query = SelectQuery::select('id,nome_fantasia,sobrenome_razao,cpf_cnpj,rg_ie,data_nascimento_abertura')->from('company');
            
            $queryTotal = SelectQuery::select('COUNT(*) as total')->from('company');
            $totalRecords = $queryTotal->fetch()['total'] ?? 0;
            
            if (!is_null($term) && ($term !== '')) {
                $query->where('company.nome_fantasia', 'ilike', "%{$term}%", 'or')
                    ->where('company.sobrenome_razao', 'ilike', "%{$term}%", 'or')
                    ->where('company.cpf_cnpj', 'ilike', "%{$term}%", 'or')
                    ->where('company.rg_ie', 'ilike', "%{$term}%")
                    ->where('company.data_nascimento_abertura', 'ilike', "%{$term}%");

                $queryFiltered = SelectQuery::select('COUNT(*) as total')->from('company')
                    ->where('company.nome_fantasia', 'ilike', "%{$term}%", 'or')
                    ->where('company.sobrenome_razao', 'ilike', "%{$term}%", 'or')
                    ->where('company.cpf_cnpj', 'ilike', "%{$term}%", 'or')
                    ->where('company.rg_ie', 'ilike', "%{$term}%")
                    ->where('company.data_nascimento_abertura', 'ilike', "%{$term}%");
                $totalFiltered = $queryFiltered->fetch()['total'] ?? 0;
            } else {
                $totalFiltered = $totalRecords;
            }

            $users = $query
                ->order($orderField, $orderType)
                ->limit($length, $start)
                ->fetchAll();
            
            $userData = [];
            foreach ($users as $key => $value) {
                $userData[$key] = [
                    $value['id'],
                    $value['nome_fantasia'],
                    $value['sobrenome_razao'],
                    $value['cpf_cnpj'],
                    $value['rg_ie'],
                    $value['data_nascimento_abertura'],
                    "<a href='/empresa/alterar/{$value['id']}' class='btn btn-warning'>Editar</a>
                    <button type='button'  onclick='Delete(" . $value['id'] . ");' class='btn btn-danger'>Excluir</button>"
                ];
            }
            
            $data = [
                'draw' => $form['draw'] ?? 1,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $userData
            ];
            
            $payload = json_encode($data);
            $response->getBody()->write($payload);

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Throwable $th) {
            $data = [
                'draw' => $form['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $th->getMessage()
            ];
            $response->getBody()->write(json_encode($data));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
    }
    public function alterar($request, $response, $args)
    {
        $id = $args['id'];
        $sale = SelectQuery::select()
            ->from('sale')
            ->where('id', '=', $id)
            ->fetch();
        if (!$sale) {
            return header('Location: /venda/lista');
            die;
        }
        $dadosTemplate = [
            'titulo' => 'Página inicial',
            'acao' => 'e',
            'id' => $id,
            'sale' => $sale
        ];
        return $this->getTwig()
            ->render($response, $this->setView('sale'), $dadosTemplate)
            ->WithHeader('Content-Type', 'text/html')
            ->WithStatus(200);
    }
    public function InsertItemSale($request, $response)
    {
        $form = $request->getParsedBody();
        $id = $form['id'] ?? null;
        $id_produto = $form['pesquisa'];
        if (empty($id) or is_null($id)) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: O ID da venda é obrigatório!',
                'id' => 0
            ], 403);
        }

        try {
            $produto = SelectQuery::select()->from('product')->where('id', '=', $id_produto)->fetch();
            if (!$produto) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Restrição: Produto não encontrado!',
                    'id' => 0
                ], 403);
            }
            $FieldAndValue = [
                'id_venda' => $id,
                'id_produto' => $id_produto,
                'quantidade' => 1,
                'total_bruto' => $produto['valor'],
                'total_liquido' => $produto['valor'],
                'desconto' => 0,
                'acrescimo' => 0,
                'nome' => $produto['nome'],
            ];
            return $this->SendJson($FieldAndValue);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Restrição: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }
}
