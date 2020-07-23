<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190828202225 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE voting ADD meeting_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE voting ALTER voting_group_id DROP NOT NULL');
        $this->addSql('ALTER TABLE voting ADD CONSTRAINT FK_FC28DA5567433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FC28DA5567433D9C ON voting (meeting_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE voting DROP CONSTRAINT FK_FC28DA5567433D9C');
        $this->addSql('DROP INDEX IDX_FC28DA5567433D9C');
        $this->addSql('ALTER TABLE voting DROP meeting_id');
        $this->addSql('ALTER TABLE voting ALTER voting_group_id SET NOT NULL');
    }
}
