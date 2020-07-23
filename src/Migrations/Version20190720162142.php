<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190720162142 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE article DROP CONSTRAINT fk_23a0e66d079f553');
        $this->addSql('DROP INDEX idx_23a0e66d079f553');
        $this->addSql('ALTER TABLE article DROP creator');
        $this->addSql('ALTER TABLE article DROP modified_by');
        $this->addSql('ALTER TABLE article RENAME COLUMN modifier_id TO modified_by_id');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6699049ECE FOREIGN KEY (modified_by_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_23A0E6699049ECE ON article (modified_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E6699049ECE');
        $this->addSql('DROP INDEX IDX_23A0E6699049ECE');
        $this->addSql('ALTER TABLE article ADD creator INT NOT NULL');
        $this->addSql('ALTER TABLE article ADD modified_by INT NOT NULL');
        $this->addSql('ALTER TABLE article RENAME COLUMN modified_by_id TO modifier_id');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT fk_23a0e66d079f553 FOREIGN KEY (modifier_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_23a0e66d079f553 ON article (modifier_id)');
    }
}
