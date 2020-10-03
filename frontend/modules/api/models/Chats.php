<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%chats}}".
 *
 * @property integer $pkChatID
 * @property integer $fkDealID
 * @property string $chatAdded
 * @property string $chatModified
 */
class Chats extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%chats}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'chatAdded',
                'updatedAtAttribute' => 'chatModified',
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
            [['fkDealID'], 'required'],
            [['fkDealID'], 'integer'],
            [['chatAdded', 'chatModified'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkChatID' => 'Chat ID',
            'fkDealID' => 'Deal ID',
            'chatAdded' => 'Added At',
            'chatModified' => 'Modified At',
        ];
    }
}
