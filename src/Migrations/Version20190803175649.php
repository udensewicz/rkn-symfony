<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190803175649 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE pnapn_voting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE pnapn_voting (id INT NOT NULL, meeting_id INT NOT NULL, applying_from TIMESTAMP(0) WITH TIME ZONE NOT NULL, applying_to TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_started TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, date_ended TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEAD061F67433D9C ON pnapn_voting (meeting_id)');
        $this->addSql('ALTER TABLE pnapn_voting ADD CONSTRAINT FK_CEAD061F67433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE pnapn_voting_id_seq CASCADE');
        $this->addSql('DROP TABLE pnapn_voting');
    }
}
