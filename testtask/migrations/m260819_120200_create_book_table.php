<?php

use yii\db\Migration;

/**
 * Каталог книг: название, год выпуска, описание, ISBN, путь к обложке.
 */
class m260819_120200_create_book_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%book}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'issue_year' => $this->smallInteger()->notNull(),
            'description' => $this->text(),
            'isbn' => $this->string(32)->notNull()->unique(),
            'cover_path' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ], 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB');

        $this->createIndex('idx-book-issue_year', '{{%book}}', 'issue_year');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%book}}');
    }
}
