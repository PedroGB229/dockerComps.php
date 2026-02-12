<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Address extends AbstractMigration
{
    
    public function change(): void
    {
        $table = $this->table('address', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'biginteger', ['identity' => true, 'null' => false])
            ->addColumn('id_customer', 'biginteger', ['null' => true])
            ->addColumn('id_users', 'biginteger', ['null' => true])
            ->addColumn('id_supplier', 'biginteger', ['null' => true])
            ->addColumn('id_company', 'biginteger', ['null' => true])
            ->addColumn('nome', 'string', ['limit' => 150])
            ->addColumn('cep', 'string', ['limit' => 20])        
            ->addColumn('numero', 'string', ['limit' => 20])
            ->addColumn('logradouro', 'string', ['limit' => 150])
            ->addColumn('bairro', 'string', ['limit' => 100])
            ->addColumn('cidade', 'string', ['limit' => 100])
            ->addColumn('estado', 'string', ['limit' => 50])
            ->addColumn('referencia', 'string', ['limit' => 50])
            ->addColumn('complemento', 'string', ['limit' => 50])
            ->addColumn('data_cadastro', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('data_atualizacao', 'datetime', ['null' => true, 'default' => 'CURRENT_TIMESTAMP'])
        
        ->addForeignKey('id_customer', 'customer', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->addForeignKey('id_users', 'users', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->addForeignKey('id_supplier', 'supplier', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->addForeignKey('id_company', 'company', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO ACTION'])
        ->create();

    }
}
