<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823075253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla bank (id, name, internal_code, notes)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bank (id UUID NOT NULL, name VARCHAR(255) NOT NULL, internal_code VARCHAR(255) DEFAULT NULL, notes TEXT DEFAULT NULL, PRIMARY KEY (id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE bank');
    }
}
