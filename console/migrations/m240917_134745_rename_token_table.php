<?php

use yii\db\Migration;

/**
 * Class m240917_134745_rename_token_table
 */
class m240917_134745_rename_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	$this->renameTable('{{%access_token}}', '{{%accessTokens}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240917_134745_rename_token_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240917_134745_rename_token_table cannot be reverted.\n";

        return false;
    }
    */
}
