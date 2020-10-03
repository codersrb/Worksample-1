<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\components\AccessRule;
use common\models\LoginForm;
use common\models\User;
use backend\models\Profile;

/**
 * Ajax controller
 */
class AjaxController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' =>
                        [
                            User::ROLE_ADMIN
                        ],
                    ],

                    [
                        'actions' => ['sidebar'],
                        'allow' => true,
                        'roles' => ['?']
                        ,
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }


    /**
     * @inheritdoc
     */
    public function beforeAction($action = null)
    {
        Yii::$app->response->format = 'json';
        return parent::beforeAction($action);
    }


    public function actionSidebar($action = 'collapse')
    {
        $data = array();

        $session = Yii::$app->session;
        $session->open();
        
        if($action == 'collapse')
        {
            $session->set('sidebar', 'collapse');
            $data['status'] = true;
        }
        else
        {
            $session->set('sidebar', 'expande');
            $data['status'] = true;
        }

        return $data;
    }
}
