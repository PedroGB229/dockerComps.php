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
    public function listproduct($request, $response)
    {
        try {
            $form = $request->getParsedBody();

            $order = $form['order'][0]['column'] ?? 0;
            $orderType = $form['order'][0]['dir'] ?? 'asc';
            $start = $form['start'] ?? 0;
            $length = $form['length'] ?? 10;

            $fields = [
                0 => 'id',
                1 => 'nome',
                2 => 'codigo_barra',
                3 => 'descricao_curta',
                4 => 'supplier_id',
                5 => 'preco_custo',
                6 => 'preco_venda'
            ];

            $orderField = $fields[$order] ?? 'id';
            $term = $form['search']['value'] ?? '';

            $query = SelectQuery::select()
                ->from('vw_product');

            if (!is_null($term) && $term !== '') {
                $query->where('product.nome', 'ilike', "%{$term}%", 'or')
                    ->where('product.descricao_curta', 'ilike', "%{$term}%");
            }

            $produtos = $query
                ->order($orderField, $orderType)
                ->limit($length, $start)
                ->fetchAll();
            $totalRecords = count($produtos);
            $dataRows = [];
            foreach ($produtos as $key => $value) {
                $dataRows[$key] = [
                    $value['id'],
                    $value['nome'],
                    $value['codigo_barra'],
                    $value['descricao_curta'],
                    $value['supplier_id'],
                    $value['preco_custo'],
                    $value['preco_venda'],
                    "<a href='/produto/alterar/{$value['id']}' class='btn btn-warning'>Editar</a>
                     <button type='button' onclick='Delete(" . $value['id'] . ");' class='btn btn-danger'>Excluir</button>"
                ];
            }

            $data = [
                'draw' => $form['draw'] ?? 1,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $dataRows
            ];

            $response->getBody()->write(json_encode($data));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode([
                'draw' => $form['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $th->getMessage()
            ]));

            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        }
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
                'nome' => $form['nome'],
                'codigo_barra' => $form['codigo_barra'],
                'descricao_curta' => $form['descricao_curta'],
                'descricao' => $form['descricao'],
                'preco_custo' => $form['preco_custo'],
                'preco_venda' => $form['preco_venda'],
                'ativo' => (isset($form['ativo'])) ? $form['ativo'] : 'false',
            ];
            if (isset($form['supplier_id']) and $form['supplier_id'] !== '') {
                $FieldsAndValues['supplier_id'] = $form['supplier_id'];
            }
            $IsUpdate = UpdateQuery::table('product')->set($FieldsAndValues)->where('id', '=', $id)->update();
            if (!$IsUpdate) {
                $data = [
                    'status' => false,
                    'msg' => 'Erro ao atualizar produto',
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
                'nome' => $form['nome'],
                'codigo_barra' => $form['codigo_barra'],
                'descricao_curta' => $form['descricao_curta'],
                'descricao' => $form['descricao'],
                'preco_custo' => $form['preco_custo'],
                'preco_venda' => $form['preco_venda'],
                'ativo' => ($form['ativo'])
            ];
            if (isset($form['supplier_id']) and $form['supplier_id'] !== '') {
                $FieldsAndValues['supplier_id'] = $form['supplier_id'];
            }
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
