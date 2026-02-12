<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InstallmentSale extends AbstractMigration
{
   
    public function change(): void
    {
        $table = $this->table('installment_sale', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
        ->addColumn('sale_id', 'biginteger', ['null' => false])
        ->addColumn('installment_id', 'biginteger', ['null' => false])
        ->addColumn('payment_terms_id', 'biginteger', ['null' => false])
        ->addColumn('total_parcelas', 'integer', ['null' => false])
        ->addColumn('valor_total', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => false])
        ->addColumn('valor_pago_total', 'decimal', ['precision' => 12, 'scale' => 2, 'null' => true, 'default' => 0])
        ->addColumn('status', 'string', ['values' => ['aberto', 'pago', 'parcial', 'cancelado'], 'default' => 'aberto', 'null' => false])
        ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
        ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
        
        ->addForeignKey('sale_id', 'sale', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->addForeignKey('installment_id', 'installment', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->addForeignKey('payment_terms_id', 'payment_terms', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->create();

    }
}
