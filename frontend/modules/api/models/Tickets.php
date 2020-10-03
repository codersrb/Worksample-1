<?php

namespace frontend\modules\api\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%tickets}}".
 *
 * @property integer $pkTicketID
 * @property string $ticketType
 * @property integer $ticketFkUserID
 * @property string $ticketAddressFrom
 * @property string $ticketAddressTo
 * @property string $ticketLatitudeFrom
 * @property string $ticketLongitudeFrom
 * @property string $ticketLatitudeTo
 * @property string $ticketLongitudeTo
 * @property integer $ticketStatus
 * @property string $ticketExpiresIn
 * @property string $ticketReward
 * @property integer $ticketMeasurementsSystem
 * @property string $ticketComment
 * @property string $ticketDeliveryRequestType
 * @property string $ticketDeliveryDateTime
 * @property string $ticketAddressVenue
 * @property string $ticketLatitudeVenue
 * @property string $ticketLongitudeVenue
 * @property double $ticketWeight
 * @property double $ticketLength
 * @property double $ticketWidth
 * @property double $ticketHeight
 * @property string $ticketItemPrice
 * @property string $ticketTotalPrice
 * @property string $ticketTransport
 * @property string $ticketItemName
 * @property string $ticketDeliveryDate
 * @property string $ticketDeliveryTime
 * @property integer $ticketRating
 * @property string $ticketDeleted
 * @property string $ticketAdded
 * @property string $ticketModified
 *
 * @property TicketItems[] $ticketItems
 * @property Users $ticketFkUser
 */
class Tickets extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tickets}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'ticketAdded',
                'updatedAtAttribute' => 'ticketModified',
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
            [['ticketType'], 'required'],
            [['ticketFkUserID', 'ticketStatus', 'ticketMeasurementsSystem', 'ticketRating'], 'integer'],
            [['ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketReward', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketItemPrice', 'ticketTotalPrice'], 'number'],
            [['ticketExpiresIn', 'ticketDeliveryDateTime', 'ticketDeliveryDate', 'ticketDeliveryTime', 'ticketDeleted', 'ticketAdded', 'ticketModified'], 'safe'],
            [['ticketComment', 'ticketTransport'], 'safe'],
            [['ticketType', 'ticketAddressFrom', 'ticketAddressTo', 'ticketDeliveryRequestType', 'ticketAddressVenue', 'ticketItemName'], 'string', 'max' => 255],
            [['ticketFkUserID'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['ticketFkUserID' => 'pkUserID']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'pkTicketID' => 'Ticket ID',
            'ticketType' => 'Type',
            'ticketFkUserID' => 'User ID',
            'ticketAddressFrom' => 'Address From',
            'ticketAddressTo' => 'Address To',
            'ticketLatitudeFrom' => 'Latitude From',
            'ticketLongitudeFrom' => 'Longitude From',
            'ticketLatitudeTo' => 'Latitude To',
            'ticketLongitudeTo' => 'Longitude To',
            'ticketStatus' => 'Status',
            'ticketExpiresIn' => 'Expires In',
            'ticketReward' => 'Reward',
            'ticketMeasurementsSystem' => 'Measurements System',
            'ticketComment' => 'Comment',
            'ticketDeliveryRequestType' => 'Delivery Request Type',
            'ticketDeliveryDateTime' => 'Delivery Date Time',
            'ticketAddressVenue' => 'Address Venue',
            'ticketLatitudeVenue' => 'Latitude Venue',
            'ticketLongitudeVenue' => 'Longitude Venue',
            'ticketWeight' => 'Weight',
            'ticketLength' => 'Length',
            'ticketWidth' => 'Width',
            'ticketHeight' => 'Height',
            'ticketItemPrice' => 'Item Price',
            'ticketTotalPrice' => 'Total Price',
            'ticketTransport' => 'Transport',
            'ticketItemName' => 'Item Name',
            'ticketDeliveryDate' => 'Delivery Date',
            'ticketDeliveryTime' => 'Delivery Time',
            'ticketRating' => 'Rating',
            'ticketDeleted' => 'Deleted At',
            'ticketAdded' => 'Added At',
            'ticketModified' => 'Modified At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicketItems()
    {
        return $this->hasMany(TicketItems::className(), ['fkTicketID' => 'pkTicketID']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTicketFkUser()
    {
        return $this->hasOne(Users::className(), ['pkUserID' => 'ticketFkUserID']);
    }
}
