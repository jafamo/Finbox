<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla import_profile (id, bank_id, name, source_format, date_format, decimal_separator, parser_config, encoding, is_active)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE import_profile (id UUID NOT NULL, bank_id UUID NOT NULL, name VARCHAR(255) NOT NULL, source_format VARCHAR(20) NOT NULL, date_format VARCHAR(50) DEFAULT NULL, decimal_separator VARCHAR(1) DEFAULT NULL, parser_config JSON DEFAULT NULL, encoding VARCHAR(50) DEFAULT NULL, is_active BOOLEAN NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_IMPORT_PROFILE_BANK_ID ON import_profile (bank_id)');
        $this->addSql('ALTER TABLE import_profile ADD CONSTRAINT FK_IMPORT_PROFILE_BANK_ID FOREIGN KEY (bank_id) REFERENCES bank (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE import_profile DROP CONSTRAINT FK_IMPORT_PROFILE_BANK_ID');
        $this->addSql('DROP TABLE import_profile');
    }
}
