<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230523103357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE resultat_quizz (id INT AUTO_INCREMENT NOT NULL, id_quizz_id INT NOT NULL, id_user_id INT NOT NULL, result SMALLINT NOT NULL, INDEX IDX_30B832EF9F34925F (id_quizz_id), INDEX IDX_30B832EF79F37AE5 (id_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE resultat_quizz ADD CONSTRAINT FK_30B832EF9F34925F FOREIGN KEY (id_quizz_id) REFERENCES quizz (id)');
        $this->addSql('ALTER TABLE resultat_quizz ADD CONSTRAINT FK_30B832EF79F37AE5 FOREIGN KEY (id_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE resultat_quizz DROP FOREIGN KEY FK_30B832EF9F34925F');
        $this->addSql('ALTER TABLE resultat_quizz DROP FOREIGN KEY FK_30B832EF79F37AE5');
        $this->addSql('DROP TABLE resultat_quizz');
    }
}
