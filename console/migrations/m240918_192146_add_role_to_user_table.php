<?php

use yii\db\Migration;

/**
 * Class m240918_192146_add_role_to_user_table
 */
class m240918_192146_add_role_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'role', $this->string()->defaultValue('user')->notNull());
        $this->update('{{%user}}', ['role' => 'user']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'role');
        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m240918_192146_add_role_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
