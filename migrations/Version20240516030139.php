<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240516030139 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, colour VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chapter (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_F981B52E591CC992 (course_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, instructor_id INT NOT NULL, level_id INT NOT NULL, category_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, introduction VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, price DOUBLE PRECISION NOT NULL, stripe_product_id VARCHAR(255) NOT NULL, stripe_price_id VARCHAR(255) NOT NULL, active TINYINT(1) DEFAULT NULL, INDEX IDX_169E6FB98C4FC193 (instructor_id), INDEX IDX_169E6FB95FB14BA7 (level_id), INDEX IDX_169E6FB912469DE2 (category_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_user (role_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_332CA4DDD60322AC (role_id), INDEX IDX_332CA4DDA76ED395 (user_id), PRIMARY KEY(role_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, picture VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, introduction VARCHAR(255) NOT NULL, verified TINYINT(1) DEFAULT NULL, warnings SMALLINT DEFAULT NULL, banned TINYINT(1) DEFAULT NULL, creator_id VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_course (user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_73CC7484A76ED395 (user_id), INDEX IDX_73CC7484591CC992 (course_id), PRIMARY KEY(user_id, course_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chapter ADD CONSTRAINT FK_F981B52E591CC992 FOREIGN KEY (course_id) REFERENCES course (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB98C4FC193 FOREIGN KEY (instructor_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB95FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('ALTER TABLE course ADD CONSTRAINT FK_169E6FB912469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_course ADD CONSTRAINT FK_73CC7484591CC992 FOREIGN KEY (course_id) REFERENCES course (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapter DROP FOREIGN KEY FK_F981B52E591CC992');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB98C4FC193');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB95FB14BA7');
        $this->addSql('ALTER TABLE course DROP FOREIGN KEY FK_169E6FB912469DE2');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDD60322AC');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484A76ED395');
        $this->addSql('ALTER TABLE user_course DROP FOREIGN KEY FK_73CC7484591CC992');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE chapter');
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE role');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE user_course');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
