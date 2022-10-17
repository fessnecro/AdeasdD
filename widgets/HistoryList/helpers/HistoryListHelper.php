<?php

namespace app\widgets\HistoryList\helpers;

use app\models\Call;
use app\models\Customer;
use app\models\History;
use app\models\Sms;
use Yii;

class HistoryListHelper
{
    public static function getBodyByModel(History $model)
    {
        switch ($model->event) {
            case History::EVENT_CREATED_TASK:
            case History::EVENT_COMPLETED_TASK:
            case History::EVENT_UPDATED_TASK:
                $task = $model->task;
                return "$model->eventText: " . ($task->title ?? '');
            case History::EVENT_INCOMING_SMS:
            case History::EVENT_OUTGOING_SMS:
                return $model->sms->message ? $model->sms->message : '';
            case History::EVENT_OUTGOING_FAX:
            case History::EVENT_INCOMING_FAX:
                return $model->eventText;
            case History::EVENT_CUSTOMER_CHANGE_TYPE:
                return "$model->eventText " .
                    (Customer::getTypeTextByType($model->getDetailOldValue('type')) ?? "not set") . ' to ' .
                    (Customer::getTypeTextByType($model->getDetailNewValue('type')) ?? "not set");
            case History::EVENT_CUSTOMER_CHANGE_QUALITY:
                return "$model->eventText " .
                    (Customer::getQualityTextByQuality($model->getDetailOldValue('quality')) ?? "not set") . ' to ' .
                    (Customer::getQualityTextByQuality($model->getDetailNewValue('quality')) ?? "not set");
            case History::EVENT_INCOMING_CALL:
            case History::EVENT_OUTGOING_CALL:
                /** @var Call $call */
                $call = $model->call;
                return ($call ? $call->totalStatusText . ($call->getTotalDisposition(false) ? " <span class='text-grey'>" . $call->getTotalDisposition(false) . "</span>" : "") : '<i>Deleted</i> ');
            default:
                return $model->eventText;
        }
    }

    /**
     * @param $model
     * @return array
     */
    public static function getEventData(History $model): array
    {
        switch ($model->event) {
            case History::EVENT_CREATED_TASK:
            case History::EVENT_COMPLETED_TASK:
            case History::EVENT_UPDATED_TASK:
                $task = $model->task;

                $data = [
                    'view' => '_item_common',
                    'body' => self::getBodyByModel($model),
                    'iconClass' => 'fa-check-square bg-yellow',
                    'footer' => isset($task->customerCreditor->name) ? "Creditor: " . $task->customerCreditor->name : ''
                ];
                break;
            case History::EVENT_INCOMING_SMS:
            case History::EVENT_OUTGOING_SMS:
                $data = [
                    'view' => '_item_common',
                    'body' => self::getBodyByModel($model),
                    'footer' => $model->sms->direction == Sms::DIRECTION_INCOMING ?
                        Yii::t('app', 'Incoming message from {number}', [
                            'number' => $model->sms->phone_from ?? ''
                        ]) : Yii::t('app', 'Sent message to {number}', [
                            'number' => $model->sms->phone_to ?? ''
                        ]),
                    'iconIncome' => $model->sms->direction == Sms::DIRECTION_INCOMING,
                    'iconClass' => 'icon-sms bg-dark-blue'
                ];
                break;
            case History::EVENT_OUTGOING_FAX:
            case History::EVENT_INCOMING_FAX:
                $fax = $model->fax;
                $data = [
                    'view' => '_item_common',
                    'body' => self::getBodyByModel($model) .
                        ' - ' .
                        (isset($fax->document) ? Html::a(
                            Yii::t('app', 'view document'),
                            $fax->document->getViewUrl(),
                            [
                                'target' => '_blank',
                                'data-pjax' => 0
                            ]
                        ) : ''),
                    'footer' => Yii::t('app', '{type} was sent to {group}', [
                        'type' => $fax ? $fax->getTypeText() : 'Fax',
                        'group' => isset($fax->creditorGroup) ? Html::a($fax->creditorGroup->name, ['creditors/groups'], ['data-pjax' => 0]) : ''
                    ]),
                    'iconClass' => 'fa-fax bg-green'
                ];
                break;
            case History::EVENT_CUSTOMER_CHANGE_TYPE:
                $data = [
                    'view' => '_item_statuses_change',
                    'oldValue' => Customer::getTypeTextByType($model->getDetailOldValue('type')),
                    'newValue' => Customer::getTypeTextByType($model->getDetailNewValue('type'))
                ];
                break;
            case History::EVENT_CUSTOMER_CHANGE_QUALITY:
                $data = [
                    'view' => '_item_statuses_change',
                    'oldValue' => Customer::getQualityTextByQuality($model->getDetailOldValue('quality')),
                    'newValue' => Customer::getQualityTextByQuality($model->getDetailNewValue('quality')),
                ];
                break;
            case History::EVENT_INCOMING_CALL:
            case History::EVENT_OUTGOING_CALL:
                /** @var Call $call */
                $call = $model->call;
                $answered = $call && $call->status == Call::STATUS_ANSWERED;

                $data = [
                    'view' => '_item_common',
                    'content' => $call->comment ?? '',
                    'body' => self::getBodyByModel($model),
                    'footer' => isset($call->applicant) ? "Called <span>{$call->applicant->name}</span>" : null,
                    'iconClass' => $answered ? 'md-phone bg-green' : 'md-phone-missed bg-red',
                    'iconIncome' => $answered && $call->direction == Call::DIRECTION_INCOMING
                ];
                break;
            default:
                $data = [
                    'view' => '_item_common',
                    'body' => self::getBodyByModel($model),
                    'iconClass' => 'fa-gear bg-purple-light'
                ];
                break;
        }

        return $data;
    }
}