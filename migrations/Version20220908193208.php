<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220908193208 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51D60322AC');
        $this->addSql('ALTER TABLE item_sprint DROP FOREIGN KEY FK_697920788C24077B');
        $this->addSql('ALTER TABLE item_sprint DROP FOREIGN KEY FK_69792078126F525E');
        $this->addSql('DROP TABLE item_sprint');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP INDEX IDX_B4021E51D60322AC ON project_user');
        $this->addSql('ALTER TABLE project_user ADD role VARCHAR(255) NOT NULL, DROP role_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE item_sprint (item_id INT NOT NULL, sprint_id INT NOT NULL, INDEX IDX_69792078126F525E (item_id), INDEX IDX_697920788C24077B (sprint_id), PRIMARY KEY(item_id, sprint_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE item_sprint ADD CONSTRAINT FK_697920788C24077B FOREIGN KEY (sprint_id) REFERENCES sprint (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item_sprint ADD CONSTRAINT FK_69792078126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_user ADD role_id INT NOT NULL, DROP role');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51D60322AC FOREIGN KEY (role_id) REFERENCES role (id)');
        $this->addSql('CREATE INDEX IDX_B4021E51D60322AC ON project_user (role_id)');
    }
}
