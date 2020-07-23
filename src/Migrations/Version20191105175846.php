<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191105175846 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE voting_group ALTER deleted SET NOT NULL');
        $this->addSql('ALTER TABLE file ADD access_type VARCHAR(255)');
        $this->addSql('ALTER TABLE file ADD extension VARCHAR(255)');
        $this->addSql('ALTER TABLE pnapn_project ALTER id DROP DEFAULT');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE voting_group ALTER deleted DROP NOT NULL');
        $this->addSql('CREATE SEQUENCE pnapn_project_id_seq');
        $this->addSql('SELECT setval(\'pnapn_project_id_seq\', (SELECT MAX(id) FROM pnapn_project))');
        $this->addSql('ALTER TABLE pnapn_project ALTER id SET DEFAULT nextval(\'pnapn_project_id_seq\')');
        $this->addSql('ALTER TABLE file DROP access_type');
    }
}
