<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190712192839 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE meeting_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE meeting (id INT NOT NULL, created_by_id INT NOT NULL, title TEXT NOT NULL, published BOOLEAN NOT NULL, plan TEXT DEFAULT NULL, date_start TIMESTAMP(0) WITH TIME ZONE NOT NULL, time_will_take DOUBLE PRECISION NOT NULL, date_end TIMESTAMP(0) WITH TIME ZONE NOT NULL, have_pnapn BOOLEAN NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F515E139B03A8386 ON meeting (created_by_id)');
        $this->addSql('ALTER TABLE meeting ADD CONSTRAINT FK_F515E139B03A8386 FOREIGN KEY (created_by_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE meeting_id_seq CASCADE');
        $this->addSql('DROP TABLE meeting');
    }
}
