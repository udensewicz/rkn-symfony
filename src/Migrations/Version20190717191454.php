<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190717191454 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE voting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE voting (id INT NOT NULL, voting_group_id INT NOT NULL, subject TEXT NOT NULL, started TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, ended TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FC28DA55E18E5C53 ON voting (voting_group_id)');
        $this->addSql('ALTER TABLE voting ADD CONSTRAINT FK_FC28DA55E18E5C53 FOREIGN KEY (voting_group_id) REFERENCES voting_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE voting_group ADD meeting_id INT NOT NULL');
        $this->addSql('ALTER TABLE voting_group ADD CONSTRAINT FK_A1A65B8067433D9C FOREIGN KEY (meeting_id) REFERENCES meeting (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A1A65B8067433D9C ON voting_group (meeting_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE voting_id_seq CASCADE');
        $this->addSql('DROP TABLE voting');
        $this->addSql('ALTER TABLE voting_group DROP CONSTRAINT FK_A1A65B8067433D9C');
        $this->addSql('DROP INDEX IDX_A1A65B8067433D9C');
        $this->addSql('ALTER TABLE voting_group DROP meeting_id');
    }
}
