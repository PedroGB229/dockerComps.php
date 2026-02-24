<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Index extends AbstractMigration
{
   
    public function change(): void
    {
   $this->execute("
        -- Extensão para busca textual com LIKE/ILIKE eficiente
        CREATE EXTENSION IF NOT EXISTS pg_trgm;

        ---------------------------------------
        -- CUSTOMER
        ---------------------------------------
        CREATE INDEX idx_customer_id ON customer (id);
        CREATE INDEX idx_customer_nome_fantasia 
            ON customer USING gin (nome_fantasia gin_trgm_ops);
        CREATE INDEX idx_customer_sobrenome_razao 
            ON customer USING gin (sobrenome_razao gin_trgm_ops);
        CREATE INDEX idx_customer_cpf_cnpj ON customer (cpf_cnpj);
        CREATE INDEX idx_customer_ativo ON customer (ativo);

        ---------------------------------------
        -- USERS
        ---------------------------------------
        CREATE INDEX idx_users_id ON users (id);
        CREATE INDEX idx_users_nome 
            ON users USING gin (nome gin_trgm_ops);
        CREATE INDEX idx_users_sobrenome 
            ON users USING gin (sobrenome gin_trgm_ops);
        CREATE INDEX idx_users_cpf ON users (cpf);
        CREATE INDEX idx_users_rg ON users (rg);
        CREATE INDEX idx_users_ativo ON users (ativo);

        ---------------------------------------
        -- SUPPLIER
        ---------------------------------------
        CREATE INDEX idx_supplier_id ON supplier (id);
        CREATE INDEX idx_supplier_nome_fantasia 
            ON supplier USING gin (nome_fantasia gin_trgm_ops);
        CREATE INDEX idx_supplier_sobrenome_razao 
            ON supplier USING gin (sobrenome_razao gin_trgm_ops);
        CREATE INDEX idx_supplier_cpf_cnpj ON supplier (cpf_cnpj);
        CREATE INDEX idx_supplier_ativo ON supplier (ativo);

        ---------------------------------------
        -- COMPANY
        ---------------------------------------
        CREATE INDEX idx_company_id ON company (id);
        CREATE INDEX idx_company_nome_fantasia 
            ON company USING gin (nome_fantasia gin_trgm_ops);
        CREATE INDEX idx_company_sobrenome_razao 
            ON company USING gin (sobrenome_razao gin_trgm_ops);
        CREATE INDEX idx_company_cpf_cnpj ON company (cpf_cnpj);
        CREATE INDEX idx_company_ativo ON company (ativo);
        ");
    }
}
