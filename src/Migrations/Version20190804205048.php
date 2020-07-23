<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190804205048 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE pnapn_vote (id INT NOT NULL, meeting_id INT NOT NULL, project_id INT NOT NULL, role_validity_id BIGINT NOT NULL, vote_cat1 SMALLINT DEFAULT NULL, vote_cat2 SMALLINT DEFAULT NULL, vote_cat3 SMALLINT DEFAULT NULL, vote_cat4 SMALLINT DEFAULT NULL, vote_cat5 SMALLINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82A2AB9E67433D9C ON pnapn_vote (meeting_id)');
        $this->addSql('CREATE INDEX IDX_82A2AB9E166D1F9C ON pnapn_vote (project_id)');
        $this->addSql('ALTER TABLE pnapn_vote ADD CONSTRAINT FK_82A2AB9E67433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE pnapn_vote ADD CONSTRAINT FK_82A2AB9E166D1F9C FOREIGN KEY (project_id) REFERENCES pnapn_project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE pnapn_vote');
    }
}
