<?php
use app\models\search\HistorySearch;
use app\widgets\HistoryList\helpers\HistoryListHelper;

/** @var $model HistorySearch */

$data = json_decode($model->event_data);

?>

<?php if (!empty($data)): ?>
    <?= $this->render($data->view, [
        'model' => $model,
        'user' => $model->user,
        'body' => $data->body ?? null,
        'iconClass' => $data->iconClass ?? null,
        'footer' => $data->footer ?? null,
        'footerDatetime' => $model->ins_ts,
    ]); ?>
<?php endif; ?>
