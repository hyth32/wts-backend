<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%posts}}`.
 */
class m240918_083335_create_posts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%posts}}', [
            'id' => $this->primaryKey(),
	    'userId' => $this->integer()->notNull(),
	    'text' => $this->text()->notNull(),
	    'createdAt' => $this->integer()->notNull(),
        ]);

	$this->addForeignKey(
	    'fk-posts-userId',
	    '{{%posts}}',
	    'userId',
	    '{{%user}}',
	    'id',
	    'CASCADE',
	    'CASCADE',
	);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	$this->dropForeignKey('fk-posts-userId', '{{%posts}}');
        $this->dropTable('{{%posts}}');
    }
}
