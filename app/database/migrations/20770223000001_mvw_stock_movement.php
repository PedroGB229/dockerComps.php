<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MvwStockMovement extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
           
        CREATE MATERIALIZED VIEW IF NOT EXISTS mvw_stock_movement AS
        select 
        product.id,
        product.nome,
        coalesce(sum(stock_movement.quantidade_entrada),0) as quantidade_entrada,
        coalesce(sum(stock_movement.quantidade_saida),0) as quantidade_saida,
        coalesce(sum(quantidade_entrada),0) - coalesce(sum(quantidade_saida),0) as estoque_atual
        from stock_movement
        left join product on product.id = stock_movement.id_product
        
        group by product.id, product.nome;
        ");

        $this->execute("
            CREATE INDEX product_id_hash ON product USING HASH (id);
            CREATE INDEX product_nome_hash ON product USING HASH (nome);
            CREATE INDEX stock_movement_idprd_hash ON stock_movement USING HASH (id_product);
        ");
    }

    public function down(): void
    {
        $this->execute("DROP MATERIALIZED VIEW IF EXISTS mvw_stock_movement CASCADE;");
    }
}