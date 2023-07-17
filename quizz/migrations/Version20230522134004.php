<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230522134004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, id_categorie INT NOT NULL, question VARCHAR(255) NOT NULL, INDEX IDX_B6F7494E9F34925F (id_categorie), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, id_question INT NOT NULL, reponse VARCHAR(255) NOT NULL, reponse_expected SMALLINT NOT NULL, INDEX IDX_5FB6DEC76353B48 (id_question), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E9F34925F FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        // $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC76353B48 FOREIGN KEY (id_question_id) REFERENCES question (id)');
        $this->addSql('CREATE TABLE quizz (id INT AUTO_INCREMENT NOT NULL, id_categorie_id INT NOT NULL, name VARCHAR(100) NOT NULL, INDEX IDX_7C77973D9F34925F (id_categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (1 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (2 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (3 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (4 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (5 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (6 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (7 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (8 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (9 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (10 , "default")');
        $this->addSql('INSERT INTO `quizz`(`id_categorie_id`, `name` ) VALUES (11 , "default")');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answers (id INT AUTO_INCREMENT NOT NULL, id_question INT DEFAULT NULL, reponse VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, reponse_expected SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE questions (id INT AUTO_INCREMENT NOT NULL, id_categorie INT DEFAULT NULL, question VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E9F34925F');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC76353B48');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE reponse');
    }
}
