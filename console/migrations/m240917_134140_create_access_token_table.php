<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%access_token}}`.
 */
class m240917_134140_create_access_token_table extends Migration
{
/**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%access_token}}', [
            'id' => $this->primaryKey(),
	    'userId' => $this->integer()->notNull(),
	    'accessToken' => $this->integer()->notNull(),
	    'createdAt' => $this->integer()->notNull(),
	    'expiresAt' => $this->integer()->notNull(),
        ]);

	$this->createIndex(
	    'idx-access_token-userId',
	    '{{%access_token}}',
	    'userId',
	);

	$this->addForeignKey(
	    'fk-access_token-userId',
	    '{{%access_token}}',
	    'userId',
	    '{{%user}}',
	    'id',
	    'CASCADE',
	);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
	$this->dropForeignKey(
	    'fk-access_token-userId',
	    '{{%access_token}}',
	);

	$this->dropIndex(
	    'idx-access_token-userId',
	    '{{%access_token}}',
	);

        $this->dropTable('{{%access_token}}');
    }
}
