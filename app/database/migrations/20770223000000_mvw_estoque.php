<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MvwEstoque extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE MATERIALIZED VIEW mvw_estoque AS
            SELECT 
                p.id AS id_product,
                p.nome,
                p.codigo_barra,
                p.preco_venda,
                COALESCE(SUM(sm.quantidade_entrada), 0) 
                - COALESCE(SUM(sm.quantidade_saida), 0) AS estoque_atual
            FROM product p
            LEFT JOIN stock_movement sm 
                ON p.id = sm.id_product
            WHERE 
                p.ativo = true
                AND p.excluido = false
            GROUP BY 
                p.id,
                p.nome,
                p.codigo_barra,
                p.preco_venda;
        ");

        $this->execute("
            CREATE UNIQUE INDEX idx_mvw_estoque_product
            ON mvw_estoque (id_product);
        ");
    }

    public function down(): void
    {
        $this->execute("DROP MATERIALIZED VIEW IF EXISTS mvw_estoque CASCADE;");
    }
}
