<?php

namespace frontend\modules\api\models;

use Yii;

/**
 * This is the model class for table "{{%ticket_item_images}}".
 *
 * @property integer $pkTicketItemImageID
 * @property integer $fkTicketItemID
 * @property string $ticketItemImage
 *
 * @property TicketItems $fkTicketItem
 */
class TicketItemImages extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_item_images}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkTicketItemID'], 'required'],
            [['fkTicketItemID'], 'integer'],
            [['ticketItemImage'], 'string'],
            [['fkTicketItemID'], 'exist', 'skipOnError' => true, 'targetClass' => TicketItems::className(), 'targetAttribute' => ['fkTicketItemID' => 'pkTicketItemID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkTicketItemImageID' => 'Ticket Item Image ID',
            'fkTicketItemID' => 'Ticket Item ID',
            'ticketItemImage' => 'Item Image',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkTicketItem()
    {
        return $this->hasOne(TicketItems::className(), ['pkTicketItemID' => 'fkTicketItemID']);
    }
}
