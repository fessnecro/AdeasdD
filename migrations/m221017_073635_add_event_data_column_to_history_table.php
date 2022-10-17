<?php

use app\widgets\HistoryList\helpers\HistoryListHelper;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%history}}`.
 */
class m221017_073635_add_event_data_column_to_history_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%history}}', 'event_data', $this->text()->null());

        $query = \app\models\History::find();

        $query->addSelect('history.*');
        $query->with([
            'customer',
            'user',
            'sms',
            'task',
            'call',
            'fax',
        ]);

        $models = $query->all();

        foreach ($models as $model) {
            $model->event_data = json_encode(\app\widgets\HistoryList\helpers\HistoryListHelper::getEventData($model));
            $model->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%history}}', 'event_data');
    }
}
