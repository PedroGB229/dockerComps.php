<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Product extends AbstractMigration
{

    public function change(): void
    {
        $table = $this->table('product', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('supplier_id', 'biginteger', ['null' => false])
            ->addColumn('company_id', 'biginteger', ['null' => false])
            ->addColumn('nome', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('descricao', 'text', ['null' => true])
            ->addColumn('descricao_curta', 'text', ['null' => true])
            ->addColumn('codigo_barras', 'string', ['limit' => 13, 'null' => true])
            ->addColumn('preco_custo', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
            ->addColumn('preco_venda', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
            ->addColumn('estoque', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('ativo', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('excluido', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])

            ->addForeignKey('supplier_id', 'supplier', 'id', ['delete' => 'RESTRICT'])
            ->addForeignKey('company_id', 'company', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
