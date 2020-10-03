<?php

namespace frontend\modules\api\models;

use Yii;

/**
 * This is the model class for table "{{%ticket_items}}".
 *
 * @property integer $pkTicketItemID
 * @property integer $fkTicketID
 * @property string $itemName
 * @property string $itemImage
 * @property string $itemPrice
 * @property integer $itemQuantity
 * @property string $itemUrl
 *
 * @property TicketItemImages[] $ticketItemImages
 * @property Tickets $fkTicket
 */
class TicketItems extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ticket_items}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fkTicketID', 'itemName', 'itemPrice', 'itemQuantity'], 'required'],
            [['fkTicketID', 'itemQuantity'], 'integer'],
            [['itemName', 'itemImage', 'itemUrl'], 'string'],
            [['itemPrice'], 'number'],
            [['fkTicketID'], 'exist', 'skipOnError' => true, 'targetClass' => Tickets::className(), 'targetAttribute' => ['fkTicketID' => 'pkTicketID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkTicketItemID' => 'Ticket Item ID',
            'fkTicketID' => 'Ticket ID',
            'itemName' => 'Item Name',
            'itemImage' => 'Image',
            'itemPrice' => 'Price',
            'itemQuantity' => 'Quantity',
            'itemUrl' => 'URL',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicketItemImages()
    {
        return $this->hasMany(TicketItemImages::className(), ['fkTicketItemID' => 'pkTicketItemID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFkTicket()
    {
        return $this->hasOne(Tickets::className(), ['pkTicketID' => 'fkTicketID']);
    }
}
