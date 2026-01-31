<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class PaymentInstallments extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('payment_installments', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('payment_term_id', 'biginteger', ['null' => false])
            ->addColumn('numero_parcelas', 'integer', ['null' => false])
            ->addColumn('intervalo_dias', 'integer', ['null' => false])
            ->addColumn('alterar_vencimento_dias', 'integer', ['null' => true])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('payment_term_id', 'payment_terms', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();
    }
}
