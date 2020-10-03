<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\rest\ActiveController;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\components\ApiInputValidator;
use common\components\ApiHelper;

use frontend\modules\api\models\Users;
use frontend\modules\api\models\Tickets;
use frontend\modules\api\models\TicketItems;
use frontend\modules\api\models\TicketItemImages;
use frontend\modules\api\models\Pages;
use frontend\modules\api\models\Strings;
use frontend\modules\api\models\Deals;
use frontend\modules\api\models\Chats;
use frontend\modules\api\models\Messages;


/**
 * Request Handler Controller Class
 * for WebService
 * @author Saurabh Sharma
 */
class TicketsController extends ActiveController
{
    public $request;
    public $debug = false;
    public $modelClass = 'frontend\modules\api\models\Tickets';


    /**
     * @inheritdoc
     * @todo Accept only Post Requests
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    // 'signin' => ['post'],
                ],
            ],
        ];
    }


    /**
     * @todo Remove default actions
     */
    public function actions()
    {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['index'], $actions['view'], $actions['create'], $actions['update'], $actions['delete'], $actions['options']);

        return $actions;
    }


    /**
     * Perform this action before every request
     * Date modified : Jul 13 2016
     */
    public function beforeAction($event)
    {
        /**
         * Set default request parser to json
         * Parse every request as Json
         * Set the parsed request to $request property
         */
        $this->request = Json::decode(Yii::$app->request->rawBody, false);


        /**
         * Send all requests in Json format
         * Set Content Type as Json
         */
        Yii::$app->response->format = 'json';


        /**
         * Default Csrf protection on API Module
         */
        return parent::beforeAction($event);
    }


    public function actionCreateDeliveryRequest()
    {
        $data = [];

        $requiredFields = ['ticketFkUserID','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketReward', 'ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketComment', 'ticketExpiresIn', 'ticketStatus'];
        if(ApiInputValidator::validateInputs($requiredFields, ['ownPackage', 'fromShop', 'ticketSubscribe', 'ticketAdditionalComment', 'ticketStatus'], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);


            if(isset($pkTicketID))
            {
                $ticket = Tickets::findOne($pkTicketID);

                if(!$ticket)
                {
                    $ticket = new Tickets;
                }
            }
            else
            {
                $ticket = new Tickets;
            }

            $ticket->ticketType = 'DeliveryRequest';

            foreach($requiredFields as $field)
            {
                $ticket->$field = $$field;
            }

            if($ticket->save())
            {

                if(isset($ownPackage) && count($ownPackage) > 0)
                {
                    $ticketItem = new TicketItems;
                    $ticketItem->fkTicketID = $ticket->pkTicketID;
                    $ticketItem->itemName = $ownPackage->name;
                    $ticketItem->itemQuantity = $ownPackage->quantity;
                    $ticketItem->itemPrice = $ownPackage->price;
                    $ticketItem->save();

                    if($ownPackage->photos && count($ownPackage->photos) > 0)
                    {
                    	foreach($ownPackage->photos as $image)
                    	{
                    		$boolSaveImage = ApiHelper::uploadImageFromB64($image, Yii::$app->security->generateRandomString(), 'itemImagePath', '.png');

                    		if(isset($boolSaveImage['name']))
                    		{
                    			$itemImage = new TicketItemImages;
                    			$itemImage->fkTicketItemID = $ticketItem->pkTicketItemID;
                    			$itemImage->ticketItemImage = $boolSaveImage['name'];
                    			$itemImage->save();

                    		}
                    	}
                    }


                }
                elseif(isset($fromShop) && count($fromShop) > 0)
                {
                    if(count($fromShop->items) > 0)
                    {
                        foreach($fromShop->items as $item)
                        {
                            $ticketItem = new TicketItems;
                            $ticketItem->fkTicketID = $ticket->pkTicketID;
                            $ticketItem->itemName = $item->name;
                            $ticketItem->itemPrice = $item->price;
                            $ticketItem->itemQuantity = $item->quantity;
                            $ticketItem->itemUrl = $item->url;
                            $ticketItem->save();

                            $boolSaveImage = ApiHelper::uploadImageFromB64($item->image, Yii::$app->security->generateRandomString(), 'itemImagePath', '.png');

                            if(isset($boolSaveImage['name']))
                            {
                    			$itemImage = new TicketItemImages;
                    			$itemImage->fkTicketItemID = $ticketItem->pkTicketItemID;
                    			$itemImage->ticketItemImage = $boolSaveImage['name'];
                    			$itemImage->save();

                    		}
                        }
                    }
                }


                $data['status'] = 200;
                $data['message'] = 'Delivery request generated';
                $data['data'] = [
                    'pkTicketID' => ArrayHelper::getValue($ticket, ['pkTicketID']),
                ];
            }
            else
            {
                $data['status'] = 500;
                $data['message'] = current($ticket->getFirstErrors());
            }


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }


    public function actionCreateDeliveryOffer()
    {
        $data = [];

        $requiredFields = ['ticketFkUserID','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketReward', 'ticketWeight','ticketComment', 'ticketExpiresIn', 'ticketTransport', 'ticketItemPrice', 'ticketStatus'];
        if(ApiInputValidator::validateInputs($requiredFields, ['ticketSubscribe', 'ticketAdditionalComment', 'ticketStatus'], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            if(isset($pkTicketID))
            {
                $ticket = Tickets::findOne($pkTicketID);

                if(!$ticket)
                {
                    $ticket = new Tickets;
                }
            }
            else
            {
                $ticket = new Tickets;
            }

            $ticket->ticketType = 'DeliveryOffer';

            foreach($requiredFields as $field)
            {
                $ticket->$field = $$field;
            }

            if($ticket->save())
            {
                $data['status'] = 200;
                $data['message'] = 'Offer request created';
                $data['data'] = [
                    'pkTicketID' => ArrayHelper::getValue($ticket, ['pkTicketID']),
                ];
            }
            else
            {
                $data['status'] = 500;
                $data['message'] = current($ticket->getFirstErrors());
            }


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }


    public function actionTickets()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'startLimit', 'endLimit'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            $tickets = Tickets::find()
            ->joinWith('ticketItems')
            ->joinWith('ticketItems.ticketItemImages')
            ->orderBy('pkTicketID DESC')
            ->where(['ticketFkUserID' => $pkUserID])
            ->andWhere(['or', 'ticketType = \'DeliveryRequest\'', 'ticketType = \'DeliveryOffer\''])
			->andWhere(['or', 'ticketStatus = 0', 'ticketStatus = 1'])
            ->limit($limit)
            ->offset($offset)
            ->all();

            // print_r($tickets);

            $allTickets = [];

            $requiredKeys = ['pkTicketID','ticketType','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketStatus', 'ticketExpiresIn', 'ticketReward', 'ticketComment', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketTransport','ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketItemPrice', 'ticketTotalPrice', 'ticketDeliveryDate', 'ticketDeliveryTime', 'ticketRating', 'ticketAdded', 'ticketStatus'];

            if($tickets)
            {
                foreach($tickets as $ticket)
                {
                    $tmpTicket = [];

                    foreach($requiredKeys as $key)
                    {
                        $tmpTicket[$key] = $ticket->$key;
                    }

                    $tmpTicket['items'] = [];



                    if(count($ticket->ticketItems) > 0)
                    {
                        foreach($ticket->ticketItems as $item)
                        {
                        	$tmpTicketItem = [];
                        	$tmpTicketItem = $item->toArray();

                        	$tmpTicketItemImages = [];
                        	if(count($item->ticketItemImages) > 0)
                        	{
                        		foreach($item->ticketItemImages as $eachTicketItemImage)
                        		{
                        			$tmpTicketItemImages[] = Url::home(true).Yii::$app->params['itemImagePath'].$eachTicketItemImage->ticketItemImage;
                        		}
                        	}
                        	$tmpTicketItem['images'] = $tmpTicketItemImages;



                            $tmpTicket['items'][] = $tmpTicketItem;
                        }
                    }

                    $tmpTicket['ticketSubscribe'] = true;
					$tmpTicket['ticketAdditionalComment'] = true;

                    $allTickets[] = $tmpTicket;

                }
            }


            $data['status'] = 200;
            $data['message'] = 'Tickets listing successful';
            $data['data'] = $allTickets;


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }


    public function actionCheckTickets()
    {
        $data = [];

        $requiredFields = ['pkUserID'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $tickets = Tickets::find()
            ->orderBy('pkTicketID DESC')
            ->where(['ticketFkUserID' => $pkUserID])
            ->andWhere(['or', 'ticketType = \'DeliveryRequest\'', 'ticketType = \'DeliveryOffer\''])
            ->count();

            $data['status'] = 200;
            $data['message'] = 'Ticket counts';
            $data['count'] = $tickets;
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionPublishRequest()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'pkTicketID'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $ticket = Tickets::findOne($pkTicketID);

            if($ticket)
            {
                $ticket->ticketStatus = 1;

                if($ticket->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Ticket published successfully';
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving ticket';
                }
            }
            else
            {
                $data['status'] = 404;
                $data['message'] = 'Ticket not found';
            }
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }


    public function actionPublishOffer()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'pkTicketID'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $ticket = Tickets::findOne($pkTicketID);

            if($ticket)
            {
                $ticket->ticketStatus = 1;

                if($ticket->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Ticket published successfully';
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving ticket';
                }
            }
            else
            {
                $data['status'] = 404;
                $data['message'] = 'Ticket not found';
            }
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionBrowseRequests()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'startLimit', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            $tickets = Tickets::find()
            ->joinWith('ticketItems')
            ->joinWith('ticketItems.ticketItemImages')
            ->orderBy('pkTicketID DESC')
            ->where(['ticketType' => 'DeliveryOffer', 'ticketStatus' => 1])
            ->limit($limit)
            ->offset($offset)
            ->all();

            // print_r($tickets);

            $allTickets = [];

            $requiredKeys = ['pkTicketID','ticketType','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketStatus', 'ticketExpiresIn', 'ticketReward', 'ticketComment', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketTransport','ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketItemPrice', 'ticketTotalPrice', 'ticketDeliveryDate', 'ticketDeliveryTime', 'ticketRating', 'ticketAdded', 'ticketStatus'];

            if($tickets)
            {
                foreach($tickets as $ticket)
                {
                    $tmpTicket = [];

                    foreach($requiredKeys as $key)
                    {
                        $tmpTicket[$key] = $ticket->$key;
                    }

                    $tmpTicket['userName'] = $ticket->ticketFkUser->userName;
                    $tmpTicket['userProfilePicture'] = Url::to(Yii::$app->params['profileImagePath'].$ticket->ticketFkUser->userProfilePicture, true);
                    $tmpTicket['items'] = [];



                    if(count($ticket->ticketItems) > 0)
                    {
                        foreach($ticket->ticketItems as $item)
                        {
                        	$tmpTicketItem = [];
                        	$tmpTicketItem = $item->toArray();

                        	$tmpTicketItemImages = [];
                        	if(count($item->ticketItemImages) > 0)
                        	{
                        		foreach($item->ticketItemImages as $eachTicketItemImage)
                        		{
                        			$tmpTicketItemImages[] = Url::home(true).Yii::$app->params['itemImagePath'].$eachTicketItemImage->ticketItemImage;
                        		}
                        	}
                        	$tmpTicketItem['images'] = $tmpTicketItemImages;



                            $tmpTicket['items'][] = $tmpTicketItem;
                        }
                    }

                    $tmpTicket['ticketSubscribe'] = true;
					$tmpTicket['ticketAdditionalComment'] = true;

                    $allTickets[] = $tmpTicket;

                }
            }


            $data['status'] = 200;
            $data['message'] = 'Tickets listing successful';
            $data['data'] = $allTickets;


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }


    public function actionBrowseOffers()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'startLimit', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            $tickets = Tickets::find()
            ->joinWith('ticketItems')
            ->joinWith('ticketItems.ticketItemImages')
            ->orderBy('pkTicketID DESC')
            ->where([   'ticketType' => 'DeliveryRequest', 'ticketStatus' => 1])
            ->limit($limit)
            ->offset($offset)
            ->all();

            // print_r($tickets);

            $allTickets = [];

            $requiredKeys = ['pkTicketID','ticketType','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketStatus', 'ticketExpiresIn', 'ticketReward', 'ticketComment', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketTransport','ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketItemPrice', 'ticketTotalPrice', 'ticketDeliveryDate', 'ticketDeliveryTime', 'ticketRating', 'ticketAdded', 'ticketStatus'];

            if($tickets)
            {
                foreach($tickets as $ticket)
                {
                    $tmpTicket = [];

                    foreach($requiredKeys as $key)
                    {
                        $tmpTicket[$key] = $ticket->$key;
                    }

                    $tmpTicket['userName'] = $ticket->ticketFkUser->userName;
                    $tmpTicket['userProfilePicture'] = Url::to(Yii::$app->params['profileImagePath'].$ticket->ticketFkUser->userProfilePicture, true);
                    $tmpTicket['items'] = [];



                    if(count($ticket->ticketItems) > 0)
                    {
                        foreach($ticket->ticketItems as $item)
                        {
                        	$tmpTicketItem = [];
                        	$tmpTicketItem = $item->toArray();

                        	$tmpTicketItemImages = [];
                        	if(count($item->ticketItemImages) > 0)
                        	{
                        		foreach($item->ticketItemImages as $eachTicketItemImage)
                        		{
                        			$tmpTicketItemImages[] = Url::home(true).Yii::$app->params['itemImagePath'].$eachTicketItemImage->ticketItemImage;
                        		}
                        	}
                        	$tmpTicketItem['images'] = $tmpTicketItemImages;



                            $tmpTicket['items'][] = $tmpTicketItem;
                        }
                    }

                    $tmpTicket['ticketSubscribe'] = true;
					$tmpTicket['ticketAdditionalComment'] = true;

                    $allTickets[] = $tmpTicket;

                }
            }


            $data['status'] = 200;
            $data['message'] = 'Tickets listing successful';
            $data['data'] = $allTickets;


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionAddToArchive()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'ticketIDs'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);


            foreach($ticketIDs as $ticket)
            {
                $ticketData = Tickets::findOne($ticket);


                if($ticketData)
                {
                    $ticketData->ticketStatus = 2;
                    $ticketData->save();
                }
                else
                {
                    $data['notFoundTickets'][] = $ticket;
                }

            }
            $data['status'] = 200;
            $data['message'] = 'Tickets archived successfully';
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionArchivedTickets()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'startLimit', 'endLimit'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            $tickets = Tickets::find()
            ->joinWith('ticketItems')
            ->joinWith('ticketItems.ticketItemImages')
            ->orderBy('pkTicketID DESC')
            ->where(['ticketFkUserID' => $pkUserID, 'ticketStatus' => 2])
            ->andWhere(['or', 'ticketType = \'DeliveryRequest\'', 'ticketType = \'DeliveryOffer\''])
            ->limit($limit)
            ->offset($offset)
            ->all();

            // print_r($tickets);

            $allTickets = [];

            $requiredKeys = ['pkTicketID','ticketType','ticketAddressFrom', 'ticketAddressTo', 'ticketLatitudeFrom', 'ticketLongitudeFrom', 'ticketLatitudeTo', 'ticketLongitudeTo', 'ticketStatus', 'ticketExpiresIn', 'ticketReward', 'ticketComment', 'ticketDeliveryDateTime', 'ticketAddressVenue', 'ticketLatitudeVenue', 'ticketLongitudeVenue', 'ticketTransport','ticketWeight', 'ticketLength', 'ticketWidth', 'ticketHeight', 'ticketItemPrice', 'ticketTotalPrice', 'ticketDeliveryDate', 'ticketDeliveryTime', 'ticketRating', 'ticketAdded', 'ticketStatus'];

            if($tickets)
            {
                foreach($tickets as $ticket)
                {
                    $tmpTicket = [];

                    foreach($requiredKeys as $key)
                    {
                        $tmpTicket[$key] = $ticket->$key;
                    }

                    $tmpTicket['items'] = [];



                    if(count($ticket->ticketItems) > 0)
                    {
                        foreach($ticket->ticketItems as $item)
                        {
                        	$tmpTicketItem = [];
                        	$tmpTicketItem = $item->toArray();

                        	$tmpTicketItemImages = [];
                        	if(count($item->ticketItemImages) > 0)
                        	{
                        		foreach($item->ticketItemImages as $eachTicketItemImage)
                        		{
                        			$tmpTicketItemImages[] = Url::home(true).Yii::$app->params['itemImagePath'].$eachTicketItemImage->ticketItemImage;
                        		}
                        	}
                        	$tmpTicketItem['images'] = $tmpTicketItemImages;



                            $tmpTicket['items'][] = $tmpTicketItem;
                        }
                    }

                    $tmpTicket['ticketSubscribe'] = true;

                    $allTickets[] = $tmpTicket;

                }
            }


            $data['status'] = 200;
            $data['message'] = 'Tickets listing successful';
            $data['data'] = $allTickets;


        }
        else
        {
            $data['status'] = 200;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionDeleteArchivedTicket()
    {
        $data = [];

        $requiredFields = ['pkUserID', 'pkTicketID'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $ticket = Tickets::find()->where(['pkTicketID' => $pkTicketID, 'ticketStatus' => 2])->one();

            if($ticket)
            {

                if($ticket->delete())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Ticket deleted successfully';
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving ticket';
                }
            }
            else
            {
                $data['status'] = 404;
                $data['message'] = 'Ticket not found';
            }
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }




    public function actionDeleteAllArchive()
    {
        $data = [];

        $requiredFields = ['pkUserID'];
        if(ApiInputValidator::validateInputs($requiredFields, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $tickets = Tickets::find()->where(['ticketFkUserID' => $pkUserID, 'ticketStatus' => 2])->all();

            foreach($tickets as $ticket)
            {

                if($ticket)
                {
                    $ticket->delete();
                }
                else
                {
                    $data['notFoundTickets'][] = $ticket->pkTicketID;
                }

            }
            $data['status'] = 200;
            $data['message'] = 'Tickets deleted successfully';
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }



    public function actionSendDealOffer()
    {
        $data = [];

        $requiredFields = ['fkTicketID', 'fkUserID', 'fkExecutorID', 'messageText'];
        if(ApiInputValidator::validateInputs($requiredFields, ['ticketDeliveryDateTime', 'ticketReward'], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $ticket = Tickets::find()->where(['pkTicketID' => $fkTicketID])->one();

            if($ticket)
            {
                if(isset($ticketDeliveryDateTime))
                {
                    $ticket->ticketDeliveryDateTime = $ticketDeliveryDateTime;
                }

                if(isset($ticketReward))
                {
                    $ticket->ticketReward = $ticketReward;
                }

                $ticket->save();



                $deal = Deals::find()->where(['fkUserID' => $fkUserID, 'fkExecutorID' => $fkExecutorID, 'fkTicketID' => $fkTicketID])->one();

                if($deal)
                {
                    $data['status'] = 422;
                    $data['message'] = 'Deal already exists';
                    $data['data'] = $deal;
                }
                else
                {
                    $deal = new Deals;
                    $deal->fkTicketID = $fkTicketID;
                    $deal->fkUserID = $fkUserID;
                    $deal->fkExecutorID = $fkExecutorID;
                    $deal->dealStatus = 0;

                    if($deal->save())
                    {
                        $chats = new Chats;
                        $message = new Messages;

                        $chats->fkDealID = $deal->pkDealID;
                        $chats->save();

                        $message->fkChatID = $chats->pkChatID;
                        $message->fromUserID = $fkUserID;
                        $message->toUserID = $fkExecutorID;
                        $message->messageText = $messageText;
                        $message->messageDeliveryStatus = 0;
                        $message->save();


                        $data['status'] = 200;
                        $data['message'] = 'Deal added successfully';
                        $data['data'] = $deal;
                    }
                    else
                    {
                        $data['status'] = 500;
                        $data['message'] = current($deal->getFirstErrors());;
                    }
                }
            }
            else
            {
                $data['status'] = 404;
                $data['message'] = 'Tickets not found';
            }
        }
        else
        {
            $data['status'] = 500;
            $data['message'] = ApiInputValidator::$validationErrorMessage;
            $data['errorKey'] = ApiInputValidator::$validationErrorKey;
        }

        return $data;
    }
}
