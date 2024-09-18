<?php

use yii\db\Migration;

/**
 * Class m240917_141603_remove_createdat
 */
class m240917_141603_remove_createdat extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
	return $this->dropColumn('{{%accessTokens}}', 'createdAt');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m240917_141603_remove_createdat cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240917_141603_remove_createdat cannot be reverted.\n";

        return false;
    }
    */
}
