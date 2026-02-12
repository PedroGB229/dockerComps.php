<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Product extends AbstractMigration
{

    public function change(): void
    {
        $table = $this->table('product', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('supplier_id', 'biginteger', ['null' => true])
            ->addColumn('nome', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('codigo_barra', 'string', ['limit' => 20, 'null' => true])
            ->addColumn('descricao_curta', 'text', ['null' => true])
            ->addColumn('descricao', 'text', ['null' => true])
            ->addColumn('preco_custo', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
            ->addColumn('preco_venda', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
            ->addColumn('ativo', 'boolean', ['default' => true, 'null' => true])
            ->addColumn('excluido', 'boolean', ['default' => false, 'null' => true])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('supplier_id', 'supplier', 'id', ['delete' => 'CASCADE', 'update' => 'NO ACTION'])
            ->create();
    }
}
