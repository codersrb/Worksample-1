<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%messages}}".
 *
 * @property integer $pkMessageID
 * @property integer $fkChatID
 * @property integer $fromUserID
 * @property integer $toUserID
 * @property string $messageText
 * @property integer $messageDeliveryStatus
 * @property string $messageAdded
 */
class Messages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%messages}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'messageAdded',
                'updatedAtAttribute' => false,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkChatID', 'fromUserID', 'toUserID', 'messageText'], 'required'],
            [['fkChatID', 'fromUserID', 'toUserID', 'messageDeliveryStatus'], 'integer'],
            [['messageText'], 'string'],
            [['messageAdded'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkMessageID' => 'Message ID',
            'fkChatID' => 'Chat ID',
            'fromUserID' => 'From',
            'toUserID' => 'To',
            'messageText' => 'Text',
            'messageDeliveryStatus' => 'Delivery Status',
            'messageAdded' => 'Added At',
        ];
    }
}
