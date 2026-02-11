<?php

namespace app\controller;

use app\database\builder\DeleteQuery;
use app\database\builder\SelectQuery;
use app\database\builder\InsertQuery;
use app\database\builder\UpdateQuery;

class Product extends Base
{

    public function lista($request, $response)
    {
        $dadosTemplate = [
            'titulo' => 'Lista de produtos'
        ];

        return $this->getTwig()
            ->render($response, $this->setView('listproduct'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function cadastro($request, $response)
    {
        // Buscar fornecedores ativos
        $suppliers = SelectQuery::select('id,nome_fantasia')
            ->from('supplier')
            ->fetchAll();


        $dadosTemplate = [
            'titulo' => 'Cadastro de produto',
            'acao' => 'c',
            'id' => '',
            'product' => [],
            'suppliers' => $suppliers // <-- enviar para o template
        ];

        return $this->getTwig()
            ->render($response, $this->setView('product'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function listproductdata($request, $response)
    {
        $form = $request->getParsedBody();
        $term = $form['term'] ?? null;
        $query = SelectQuery::select('id, codigo_barra, nome')->from('product');
        if ($term != null) {
            $query->where('codigo_barra', 'ILIKE', "%$term%", 'or')
                ->where('nome', 'ILIKE', "%$term%");
        }
        $data = [];
        $results = $query->fetchAll();
        foreach ($results as $key => $item) {
            $data['results'][$key] = [
                'id' => $item['id'],
                'text' => 'Cód barra: ' . $item['codigo_barra'] . ' - ' . $item['nome']
            ];
        }
        $data['pagination'] = ['more' => true];
         return $this->SendJson($response, $data);
    }
    public function alterar($request, $response, $args)
    {
        $id = $args['id'] ?? null;

        if (!$id || !is_numeric($id)) {
            $dadosTemplate = [
                'acao' => 'c',
                'id' => '',
                'titulo' => 'Cadastro e alteração de produto',
                'product' => null
            ];

            return $this->getTwig()
                ->render($response, $this->setView('product'), $dadosTemplate)
                ->withHeader('Content-Type', 'text/html')
                ->withStatus(200);
        }

        $product = SelectQuery::select()
            ->from('product')
            ->where('id', '=', $id)
            ->fetch();

        $suppliers = SelectQuery::select('id,nome_fantasia')
            ->from('supplier')
            ->whereRaw('(ativo = true OR ativo IS NULL)')
            ->fetchAll();


        $dadosTemplate = [
            'acao' => 'e',
            'id' => $id,
            'titulo' => 'Cadastro e alteração de produto',
            'product' => $product,
            'suppliers' => $suppliers,
            'isExcluido' => $product['excluido'] ?? false
        ];


        return $this->getTwig()
            ->render($response, $this->setView('product'), $dadosTemplate)
            ->withHeader('Content-Type', 'text/html')
            ->withStatus(200);
    }
    public function delete($request, $response)
    {
        try {
            $id = $_POST['id'];

            $IsDelete = UpdateQuery::table('product')
                ->set(['excluido' => true])
                ->where('id', '=', $id)
                ->update();

            if (!$IsDelete) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao excluir produto',
                    'id' => $id
                ], 200);
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Produto removido com sucesso!',
                'id' => $id
            ], 200);
        } catch (\Throwable $th) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Erro: ' . $th->getMessage(),
                'id' => $_POST['id'] ?? 0
            ], 500);
        }
    }
    public function update($request, $response)
    {
        try {
            $form = $request->getParsedBody();
            $id = $form['id'];

            $FieldsAndValues = [
                'supplier_id' => $form['supplier_id'],
                'nome' => $form['nome'],
                'codigo_barras' => $form['codigo_barras'],
                'descricao_curta' => $form['descricao_curta'],
                'descricao' => $form['descricao'],
                'preco_custo' => $form['preco_custo'],
                'preco_venda' => $form['preco_venda'],
                'ativo' => isset($form['ativo']),
                'excluido' => isset($form['excluido']),
                'data_atualizacao' => date('Y-m-d H:i:s')
            ];

            $IsUpdate = UpdateQuery::table('product')
                ->set($FieldsAndValues)
                ->where('id', '=', $id)
                ->update();

            if (!$IsUpdate) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao atualizar produto',
                    'id' => 0
                ], 200);
            }

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Produto atualizado com sucesso!',
                'id' => $id
            ], 200);
        } catch (\Exception $e) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Exceção: ' . $e->getMessage(),
                'id' => 0
            ], 500);
        }
    }
    public function insert($request, $response)
    {
        try {
            $form = $request->getParsedBody();

            $FieldsAndValues = [
                'supplier_id' => $form['supplier_id'],
                'nome' => $form['nome'],
                'codigo_barras' => $form['codigo_barras'],
                'descricao_curta' => $form['descricao_curta'],
                'descricao' => $form['descricao'],
                'preco_custo' => $form['preco_custo'],
                'preco_venda' => $form['preco_venda'],
                'ativo' => ($form['ativo']),
                'excluido' => ($form['excluido'])
            ];

            $IsSave = InsertQuery::table('product')->save($FieldsAndValues);

            if (!$IsSave) {
                return $this->SendJson($response, [
                    'status' => false,
                    'msg' => 'Erro ao inserir produto',
                    'id' => 0
                ], 200);
            }

            $id = SelectQuery::select('id')
                ->from('product')
                ->order('id', 'desc')
                ->fetch();

            return $this->SendJson($response, [
                'status' => true,
                'msg' => 'Produto cadastrado com sucesso!',
                'id' => $id['id'] ?? 0
            ], 200);
        } catch (\Throwable $th) {
            return $this->SendJson($response, [
                'status' => false,
                'msg' => 'Exceção: ' . $th->getMessage(),
                'id' => 0
            ], 500);
        }
    }
}