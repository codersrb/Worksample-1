<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%deals}}".
 *
 * @property string $pkDealID
 * @property string $fkTicketID
 * @property string $fkUserID
 * @property string $fkExecutorID
 * @property integer $dealStatus
 * @property string $dealAdded
 * @property string $dealModified
 *
 * @property Tickets $fkTicket
 * @property Users $fkUser
 * @property Users $fkExecutor
 */
class Deals extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%deals}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'dealAdded',
                'updatedAtAttribute' => 'dealModified',
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
            [['fkTicketID', 'fkUserID', 'fkExecutorID'], 'required'],
            [['fkTicketID', 'fkUserID', 'fkExecutorID', 'dealStatus'], 'integer'],
            [['dealAdded', 'dealModified'], 'safe'],
            [['fkTicketID'], 'exist', 'skipOnError' => true, 'targetClass' => Tickets::className(), 'targetAttribute' => ['fkTicketID' => 'pkTicketID']],
            [['fkUserID'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['fkUserID' => 'pkUserID']],
            [['fkExecutorID'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['fkExecutorID' => 'pkUserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkDealID' => 'Deal ID',
            'fkTicketID' => 'Ticket ID',
            'fkUserID' => 'User ID',
            'fkExecutorID' => 'Executor ID',
            'dealStatus' => 'Status',
            'dealAdded' => 'Added On',
            'dealModified' => 'Modified On',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkTicket()
    {
        return $this->hasOne(Tickets::className(), ['pkTicketID' => 'fkTicketID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkUser()
    {
        return $this->hasOne(Users::className(), ['pkUserID' => 'fkUserID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkExecutor()
    {
        return $this->hasOne(Users::className(), ['pkUserID' => 'fkExecutorID']);
    }
}
