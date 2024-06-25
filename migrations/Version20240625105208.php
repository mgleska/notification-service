<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240625105208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE inbox_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE outbox_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE inbox (id INT NOT NULL, user_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR(255) DEFAULT NULL, phone_number VARCHAR(50) DEFAULT NULL, push_token VARCHAR(255) DEFAULT NULL, message VARCHAR(1000) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN inbox.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE outbox (id INT NOT NULL, inbox_id INT NOT NULL, channel VARCHAR(100) NOT NULL, delivery_status VARCHAR(255) NOT NULL, try_number SMALLINT NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN outbox.delivered_at IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE inbox_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE outbox_id_seq CASCADE');
        $this->addSql('DROP TABLE inbox');
        $this->addSql('DROP TABLE outbox');
    }
}
