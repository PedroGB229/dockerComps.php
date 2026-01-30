<?php

namespace app\controller;

use app\database\builder\DeleteQuery;
use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;

class Fornecedor extends Base
{

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista da fornecedor'
        ];
        return $this->getTwig()
            ->render($response, $this->setView('listfornecedor'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Cadastro de fornecedor',
            'acao' => 'c',
            'id' => '',
            'fornecedor' => []
        ];
        return $this->getTwig()
            ->render($response, $this->setView('fornecedor'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function listfornecedor($request, $response)
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
                4 => 'rg_ie'
            ];
            
            #Capturamos o nome do campo a ser ordenado.
            $orderField = $fields[$order] ?? 'id';
            #O termo pesquisado
            $term = $form['search']['value'] ?? '';
            
            $query = SelectQuery::select('id,nome_fantasia,sobrenome_razao,cpf_cnpj,rg_ie')->from('supplier');
            
            $queryTotal = SelectQuery::select('COUNT(*) as total')->from('supplier');
            $totalRecords = $queryTotal->fetch()['total'] ?? 0;
            
            if (!is_null($term) && ($term !== '')) {
                $query->where('supplier.nome_fantasia', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.sobrenome_razao', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.cpf_cnpj', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.rg_ie', 'ilike', "%{$term}%");

                $queryFiltered = SelectQuery::select('COUNT(*) as total')->from('supplier')
                    ->where('supplier.nome_fantasia', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.sobrenome_razao', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.cpf_cnpj', 'ilike', "%{$term}%", 'or')
                    ->where('supplier.rg_ie', 'ilike', "%{$term}%");
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
                    "<a href='/fornecedor/alterar/{$value['id']}' class='btn btn-warning'>Editar</a>
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
        $id = $args['id'] ?? null;
        
        // Validar se o ID é válido
        if (!$id || !is_numeric($id)) {
            $dadosTemplate = [
                'acao' => 'c',
                'id' => '',
                'titulo' => 'Cadastro e alteracao de fornecedor',
                'fornecedor' => null
            ];
            return $this->getTwig()
                ->render($response, $this->setView('fornecedor'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        }
        
        $fornecedor = SelectQuery::select()->from('supplier')->where('id', '=', $id)->fetch();
        $dadosTemplate = [
            'acao' => 'e',
            'id' => $id,
            'titulo' => 'Cadastro e alteracao de fornecedor',
            'fornecedor' => $fornecedor
        ];
        return $this->getTwig()
            ->render($response, $this->setView('fornecedor'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function delete($request, $response)
    {
        try {
            $id = $_POST['id'];
            
            // Primeiro, deleta registros relacionados em contato
            try {
                DeleteQuery::table('contato')
                    ->where('id_supplier', '=', $id)
                    ->delete();
            } catch (\Exception $e) {
                // Log ou ignore se não houver registros
            }

            // Depois, deleta registros relacionados em endereco
            try {
                DeleteQuery::table('endereco')
                    ->where('id_supplier', '=', $id)
                    ->delete();
            } catch (\Exception $e) {
                // Log ou ignore se não houver registros
            }

            // Finalmente, deleta o usuário
            $IsDelete = DeleteQuery::table('supplier')
                ->where('id', '=', $id)
                ->delete();

            if (!$IsDelete) {
                $data = ['status' => false, 'msg' => 'Erro ao deletar fornecedor', 'id' => $id];
                return $this->SendJson($response, $data, 200);
            }
            
            $data = ['status' => true, 'msg' => 'fornecedor removido com sucesso!', 'id' => $id];
            return $this->SendJson($response, $data, 200);
            
        } catch (\Throwable $th) {
            $data = ['status' => false, 'msg' => 'Erro: ' . $th->getMessage(), 'id' => $_POST['id'] ?? 0];
            return $this->SendJson($response, $data, 500);
        }
    }
    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'];
            $FieldAndValues = [
                'nome_fantasia' => $form['nome_fantasia'],
                'sobrenome_razao' => $form['sobrenome_razao'],
                'cpf_cnpj' => $form['cpf_cnpj'],
                'rg_ie' => $form['rg_ie']
            ];
            $IsUpdate = UpdateQuery::table('supplier')->set($FieldAndValues)->where('id', '=', $id)->update();
            if (!$IsUpdate) {
                $data = [
                    'status' => false,
                    'msg' => 'Erro ao atualizar fornecedor',
                    'id' => 0
                ];
                return $this->SendJson($response, $data, 200);
            }
            $data = [
                'status' => true,
                'msg' => 'Dados alterados com sucesso!',
                'id' => $id
            ];
            return $this->SendJson($response, $data, 200);
        } catch (\Exception $e) {
            $data = ['status' => false, 'msg' => 'Exceção: ' . $e->getMessage(), 'id' => 0];
            return $this->SendJson($response, $data, 500);
        }
    }
    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $FieldsAndValues = [
                'nome_fantasia' => $form['nome_fantasia'] ?? null,
                'sobrenome_razao' => $form['sobrenome_razao'] ?? null,
                'cpf_cnpj' => $form['cpf_cnpj'] ?? null,
                'rg_ie' => $form['rg_ie'] ?? null
            ];
            $IsSave = InsertQuery::table('supplier')->save($FieldsAndValues);

            if (!$IsSave) {
                $data = ['status' => false, 'msg' => 'Erro ao inserir fornecedor', 'id' => 0];
                return $this->SendJson($response, $data, 200);
            }
            
            $id = SelectQuery::select('id')->from('supplier')->order('id', 'desc')->fetch();
            $data = [
                'status' => true,
                'msg' => 'fornecedor cadastrado com sucesso!',
                'id' => $id['id'] ?? 0
            ];
            return $this->SendJson($response, $data, 200);
        } catch (\Throwable $th) {
            $data = ['status' => false, 'msg' => 'Exceção: ' . $th->getMessage(), 'id' => 0];
            return $this->SendJson($response, $data, 500);
        }
    }
}
