<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;

use frontend\modules\api\models\App;
use frontend\modules\api\models\Players;
use frontend\modules\api\models\Strings;


/**
 * Request Handler Controller Class
 * for WebService
 * @author Saurabh Sharma
 */
class DefaultController extends Controller
{
    public $request;
    public $debug = false;
    public $modelClass = 'frontend\modules\api\models\Users';


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


    public function actionSignin()
    {

        print_r( $this->request );


        $data['name'] = 'OK';
        $data['last'] = 'TESTED';

        return $data;
    }
}
