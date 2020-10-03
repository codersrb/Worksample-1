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
 * Site controller
 */
class SiteController extends Controller
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'profile'],
                        'allow' => true,
                        'roles' =>
                        [
                            User::ROLE_ADMIN
                        ],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
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

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }



    /**
     * Profile Page
     */
    public function actionProfile()
    {
        $model = Profile::findOne(Yii::$app->user->identity->pkUserID);

        if(Yii::$app->request->isPost)
        {
            $model->userName = Yii::$app->request->post('Profile')['userName'];
            $model->userEmail = Yii::$app->request->post('Profile')['userEmail'];

            if(Yii::$app->request->post('Profile')['userPassword'])
            {
                $model->userPassword = Yii::$app->security->generatePasswordHash(Yii::$app->request->post('Profile')['userPassword']);
            }

            $model->imageFile = UploadedFile::getInstance($model, 'imageFile');

            if($model->upload())
            {
                Yii::$app->session->addFlash('success', 'Profile updated successfully');
            }
            else
            {
                Yii::$app->session->addFlash('success', 'Error while updating profile. Please try again');
            }
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }
}
