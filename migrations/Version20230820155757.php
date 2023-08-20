<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230820155757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_user (project_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_B4021E51166D1F9C (project_id), INDEX IDX_B4021E51A76ED395 (user_id), PRIMARY KEY(project_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, creator_id_id INT NOT NULL, project_id_id INT NOT NULL, ueser_id_id INT DEFAULT NULL, category_id_id INT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, finished TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_527EDB25F05788E9 (creator_id_id), INDEX IDX_527EDB256C1197C9 (project_id_id), INDEX IDX_527EDB25BD50C3CC (ueser_id_id), INDEX IDX_527EDB259777D11E (category_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_user ADD CONSTRAINT FK_B4021E51A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25F05788E9 FOREIGN KEY (creator_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB256C1197C9 FOREIGN KEY (project_id_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB25BD50C3CC FOREIGN KEY (ueser_id_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE task ADD CONSTRAINT FK_527EDB259777D11E FOREIGN KEY (category_id_id) REFERENCES category (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51166D1F9C');
        $this->addSql('ALTER TABLE project_user DROP FOREIGN KEY FK_B4021E51A76ED395');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25F05788E9');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB256C1197C9');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB25BD50C3CC');
        $this->addSql('ALTER TABLE task DROP FOREIGN KEY FK_527EDB259777D11E');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_user');
        $this->addSql('DROP TABLE task');
    }
}
