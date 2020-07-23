<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190803211210 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE pnapn_project ADD voting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pnapn_project ADD CONSTRAINT FK_B988FDBC4254ACF8 FOREIGN KEY (voting_id) REFERENCES pnapn_voting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B988FDBC4254ACF8 ON pnapn_project (voting_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE pnapn_project DROP CONSTRAINT FK_B988FDBC4254ACF8');
        $this->addSql('DROP INDEX IDX_B988FDBC4254ACF8');
        $this->addSql('ALTER TABLE pnapn_project DROP voting_id');
    }
}
