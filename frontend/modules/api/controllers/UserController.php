<?php

namespace frontend\modules\api\controllers;

use Yii;
use yii\web\Controller;
use yii\rest\ActiveController;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\components\ApiHelper;
use common\components\ApiInputValidator;

use frontend\modules\api\models\Users;
use frontend\modules\api\models\Pages;
use frontend\modules\api\models\Strings;


/**
 * Request Handler Controller Class
 * for WebService
 * @author Saurabh Sharma
 */
class UserController extends ActiveController
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


    public function actionSignupToken()
    {
        $data = [];

        if(ApiInputValidator::validateInputs(['locale', 'userPhone'], ['resendOTP'], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['userPhone' => $userPhone]);

            if($user)
            {
                if(isset($resendOTP) && $resendOTP === TRUE)
                {
                    $user->userOTP = rand(1000,9999);

                    if($user->save())
                    {
                        $data['status'] = 200;
                        $data['message'] = 'OTP Sent to Mobile';
                        $data['data'] = [
                            'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                            'userOTP' => ArrayHelper::getValue($user, ['userOTP']),
                        ];
                    }
                    else
                    {
                        $data['status'] = 403;
                        $data['message'] = 'Error while generating new token';
                    }
                }
                else
                {
                    $data['status'] = 403;
                    $data['message'] = 'Account Already exists';
                }

            }
            else
            {
                $otp = rand(1000,9999);
                $user = new Users;
                $user->userPhone = $userPhone;
                $user->userOTP = $otp;
                $user->userStatus = 'Pending';

                if($user->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'OTP Sent to Mobile';
                    $data['data'] = [
                        'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                        'userOTP' => ArrayHelper::getValue($user, ['userOTP']),
                    ];
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = current($user->getFirstErrors());
                }
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




    public function actionSignup()
    {
        $data = [];

        if(ApiInputValidator::validateInputs(['pkUserID', 'userOTP'], [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID, 'userOTP' => $userOTP]);

            if($user)
            {
                $user->userStatus = 'Active';
                if($user->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Signup successfull';
                    $data['data'] = [
                        'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                        'userToken' => Yii::$app->getSecurity()->generatePasswordHash($user->pkUserID.','.time()),
                    ];
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving order';
                }

            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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

    public function actionTermsAndFaqs()
    {
        $faqs = Pages::findOne(['pageSlug' => 'faqs']);
        $terms = Pages::findOne(['pageSlug' => 'terms-and-conditions']);

        $data['status'] = 200;
        $data['message'] = 'OK';
        $data['data'] = [
            'terms' => ArrayHelper::getValue($terms, 'pageContent'),
            'faqs' => [
                    ['question' => 'Question1', 'answer' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s'],
                    ['question' => 'Question 2 Question Heading 3', 'answer' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s'],
                    ['question' => 'Question 3 Question Heading 4', 'answer' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s'],
                ],
        ];

        return $data;
    }


    public function actionProfilePersonalInfo()
    {
        $data = [];


        $required = ['pkUserID', 'userName', 'userAge', 'userGender'];
        if(ApiInputValidator::validateInputs($required, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID]);

            if($user)
            {
                foreach($required as $value)
                {
                    $user->$value = $this->request->$value;
                }

                if($user->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Profile Updated successfull';
                    $data['data'] = [
                        'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                        'userName' => ArrayHelper::getValue($user, ['userName']),
                        'userAge' => ArrayHelper::getValue($user, ['userAge']),
                        'userGender' => ArrayHelper::getValue($user, ['userGender']),
                    ];
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving order';
                }

            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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


    public function actionProfileContactInfo()
    {
        $data = [];


        $required = ['pkUserID', 'userEmail', 'userPhone'];
        if(ApiInputValidator::validateInputs($required, ['userPassword'], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID]);

            if($user)
            {
                foreach($required as $value)
                {
                    $user->$value = $this->request->$value;
                }

                if(isset($userPassword))
                {
                    $user->userPassword = Yii::$app->security->generatePasswordHash($userPassword);
                }

                if($user->save())
                {
                    $data['status'] = 200;
                    $data['message'] = 'Contact Information Updated successfull';
                    $data['data'] = [
                        'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                        'userEmail' => ArrayHelper::getValue($user, ['userEmail']),
                        'userPhone' => ArrayHelper::getValue($user, ['userPhone']),
                    ];
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving order';
                }

            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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


    public function actionProfileImage()
    {
        $data = [];


        $required = ['pkUserID', 'userProfilePicture'];
        if(ApiInputValidator::validateInputs($required, [], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID]);

            if($user)
            {
                $boolSaveImage = ApiHelper::uploadImageFromB64($userProfilePicture, Yii::$app->security->generateRandomString(), 'profileImagePath', '.png');

                if(isset($boolSaveImage['name']))
                {
                    $user->userProfilePicture = $boolSaveImage['name'];

                    if($user->save())
                    {
                        $data['status'] = 200;
                        $data['message'] = 'Contact Information Updated successfull';
                        $data['data'] = [
                            'pkUserID' => ArrayHelper::getValue($user, ['pkUserID']),
                            'userProfilePicture' => Url::home(true).Yii::$app->params['profileImagePath'].$user->userProfilePicture
                        ];
                    }
                    else
                    {
                        $data['status'] = 500;
                        $data['message'] = 'Error while saving order';
                    }

                }

            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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


    public function actionGetProfileInfo()
    {
        $data = [];


        $required = ['pkUserID'];
        if(ApiInputValidator::validateInputs($required, [''], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID]);

            if($user)
            {
                $userData = [];

                $userData['userName'] = $user->userName;
                $userData['userPhone'] = $user->userPhone;
                $userData['userEmail'] = $user->userEmail;
                $userData['userAge'] = $user->userAge;
                $userData['userGender'] = $user->userGender;
                $userData['userProfilePicture'] = Url::home(true).Yii::$app->params['profileImagePath'].$user->userProfilePicture;

                $data['status'] = 200;
                $data['message'] = 'Profile data';
                $data['data'] = $userData;
            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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



    public function actionVerifyAccount()
    {
        $data = [];


        $required = ['pkUserID', 'userName', 'userEmail', 'userAge', 'userGender', 'userIdentityCard', 'userIdentityType'];
        if(ApiInputValidator::validateInputs($required, [''], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID]);

            if($user)
            {

                foreach($required as $value)
                {
                    $user->$value = $this->request->$value;
                }

                $boolSaveImage = ApiHelper::uploadImageFromB64($userIdentityCard, Yii::$app->security->generateRandomString(), 'identityImagePath', '.png');

                if(isset($boolSaveImage['name']))
                {
                    $user->userIdentityCard = $boolSaveImage['name'];
                }

                $user->userStatus = 'Verified';


                if($user->save())
                {
                    $userData = [];

                    // $userData['userName'] = $user->userName;
                    // $userData['userPhone'] = $user->userPhone;
                    // $userData['userEmail'] = $user->userEmail;
                    // $userData['userAge'] = $user->userAge;
                    // $userData['userGender'] = $user->userGender;
                    // $userData['userProfilePicture'] = $user->userProfilePicture;

                    $data['status'] = 200;
                    $data['message'] = 'Account verified successfully';
                    $data['userIdentityCard'] = Url::home(true).Yii::$app->params['identityImagePath'].$user->userIdentityCard;
                    // $data['data'] = $userData;
                }
                else
                {
                    $data['status'] = 500;
                    $data['message'] = 'Error while saving user account';
                }



            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'User not found';
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



    public function actionCheckAccount()
    {
        $data = [];


        $required = ['pkUserID'];
        if(ApiInputValidator::validateInputs($required, [''], $this->request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $this->request);

            $user = Users::findOne(['pkUserID' => $pkUserID, 'userStatus' => 'Verified']);

            if($user)
            {
                    $data['status'] = 200;
                    $data['message'] = 'Account is verified';
                    $data['isProfileCompleted'] = true;
            }
            else
            {
                $data['status'] = 500;
                $data['message'] = 'Account not verified';
                $data['isProfileCompleted'] = true;
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





}
