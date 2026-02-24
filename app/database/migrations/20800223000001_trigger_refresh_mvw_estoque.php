<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;


final class TriggerRefreshMvwEstoque extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE OR REPLACE FUNCTION refresh_mvw_estoque()
            RETURNS TRIGGER AS $$
            BEGIN
                REFRESH MATERIALIZED VIEW CONCURRENTLY mvw_estoque;
                RETURN NULL;
            END;
            $$ LANGUAGE plpgsql;
        ");

        $this->execute("
            CREATE TRIGGER trigger_refresh_mvw_estoque
            AFTER INSERT OR UPDATE OR DELETE ON stock_movement
            FOR EACH STATEMENT
            EXECUTE FUNCTION refresh_mvw_estoque();
        ");
    }

    public function down(): void
    {
        $this->execute("
            DROP TRIGGER IF EXISTS trigger_refresh_mvw_estoque ON stock_movement;
        ");

        $this->execute("
            DROP FUNCTION IF EXISTS refresh_mvw_estoque;
        ");
    }
}