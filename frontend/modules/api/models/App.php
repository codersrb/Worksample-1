<?php

namespace frontend\modules\api\models;

use Yii;
use Exception;
use yii\base\Model;
use yii\db\ActiveRecord;
use yii\db\Expression;

use common\models\Users AS BaseUser;

use yii\helpers\VarDumper;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;



class App extends Model
{

    public static $errorMessage;
    public static $errorCode;
    public static $validationErrorKey;
    public static $validationErrorMessage;
    public static $userModel;


    /**
     * Signup WebService for Fan
     * @param fullName
     * @param displayName
     * @param email
     * @param password
     */
    public static function signupFan($request)
    {
        $data = [];

        if(self::validateInputs(['fullName', 'displayName', 'email', 'password'], ['profileImage'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {
                $model = new Users;
                $model->userName = $displayName;
                $model->userFullName = $fullName;
                $model->userEmail = $email;
                $model->userPassword = Yii::$app->security->generatePasswordHash($password);
                $model->userAuthToken = Yii::$app->security->generateRandomString(64);
                $model->userStatus = 'Active';

                if(!empty($profileImage))
                {
                    $imageName = Yii::$app->security->generateRandomString();
                    $boolUploadImage = self::uploadImageAndroid($profileImage, $imageName, 'profilePath', '.png', $model->userProfileImage);

                    if($boolUploadImage && is_array($boolUploadImage))
                    {
                        $model->userProfileImage = $boolUploadImage['name'];
                        $data['profileImage'] =  $boolUploadImage['url'];
                    }
                }

                if($model->save())
                {
                    $boolSendMail = Yii::$app
                        ->mailer
                        ->compose(
                            ['html' => 'webservice/signupFan/html', 'text' => 'webservice/signupFan/text'],
                            ['user' => $model]
                        )
                        ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                        ->setTo($model->userEmail)
                        ->setSubject('Welcome to '.Yii::$app->name)
                        ->send();

                    if($boolSendMail)
                    {
                        $data['code'] = 200;
                        $data['message'] = Strings::getString(0);
                        $data['fullName'] = $model->userFullName;
                        $data['displayName'] = $model->userName;
                        $data['email'] = $model->userEmail;
                        $data['status'] = $model->userStatus;
                        $data['authToken'] = $model->userAuthToken;
                    }
                    else
                    {
                        throw new Exception('Error while sending mail. Please try again');
                    }
                    
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = current($model->getFirstErrors());
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    /**
     * Login WebService for Fan & Player
     * @param email
     * @param password
     */
    public static function login($request)
    {
        $data = [];

        if(self::validateInputs(['email', 'password'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {
                $model = Users::findOne(['userEmail' => $email]);

                if($model)
                {
                    if(Yii::$app->security->validatePassword($password, $model->userPassword))
                    {
                        if($model->userStatus == 'Active')
                        {
                            $authToken = Yii::$app->security->generateRandomString(64);
                            $data['code'] = 200;
                            $data['userID'] = $model->pkUserID;
                            $data['fullName'] = $model->userFullName;
                            $data['displayName'] = $model->userName;
                            $data['email'] = $model->userEmail;
                            $data['profileImage'] = self::generateImagePath($model->userProfileImage, 'profilePath');
                            $data['status'] = $model->userStatus;
                            $data['authToken'] = $authToken;
                            $data['userRole'] = $model->userRole;

                            if($model->userRole == 'Player')
                            {
                                $player = $model->players;

                                $playerData = [];
                                $playerData['gender'] = $player->playerGender;
                                $playerData['height'] = $player->playerHeight;
                                $playerData['weight'] = $player->playerWeight;
                                $playerData['pastInjuriesRecord'] = $player->playerPastInguriesRecord;
                                $playerData['bloodType'] = $player->playerBloodType;
                                $playerData['placeOfBirth'] = $player->playerBirthPlace;
                                $playerData['origin'] = $player->playerOrigin;
                                $playerData['nationalty'] = $player->fkCountry->countryName;
                                $playerData['position'] = $player->playerPosition;
                                $playerData['professionalDetails'] = $player->playerProfessionalDetails;
                                $playerData['careerStatics'] = $model->getPlayerStats()->select('playerStatValue')->indexBy('playerStatName')->column();
                                $playerData['skills'] = $player->playerShortVideo;
                                $playerData['biography'] = $player->playerBiography;
                                $playerData['socialUrl']['facebook'] = $player->playerFacebookLink;
                                $playerData['socialUrl']['google'] = $player->playerGooleLink;
                                $playerData['socialUrl']['twitter'] = $player->playerTwitterLink;

                                $data['player'] = $playerData;
                            }

                            $model->userAuthToken = $authToken;
                            $model->save(false);
                        }
                        else
                        {
                            throw new Exception(Strings::getString(2));
                        }
                    }
                    else
                    {
                        throw new Exception(Strings::getString(1));
                    }
                }
                else
                {
                    throw new Exception(Strings::getString(1));
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine().$ex->getFile();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }

    /**
     * Forget Password WebService
     * @param email
     */
    public static function forgetPassword($request)
    {
        $data = [];

        if(self::validateInputs(['email'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {
                $model = new PasswordResetRequestForm;
                $model->userEmail = $email;
                $model->otp = true;

                if($model->sendEmail())
                {
                    $data['code'] = 200;
                    $data['message'] = Strings::getString(3);
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = 'This email address is not registered with us';
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine().$ex->getFile();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    public static function country($request)    {        $data = [];        $return = [];        
    //$data = Country::find()->select('countryName')->indexBy('pkCountryID')->column();       
     $countries = Country::find()->all();        foreach ($countries as $country ) {            $return[] = [                'id' => $country->pkCountryID,                'name' => $country->countryName            ];        }        return [            'data' => $return,        ];    }


    /**
     * Reset Password WebService
     * @param email
     * @param password
     * @param otp
     */
    public static function resetPassword($request)
    {
        $data = [];

        if(self::validateInputs(['email', 'password', 'otp'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $user = Users::findOne(['userEmail' => $email, 'userOTP' => $otp]);
            
            if($user)
            {
                $user->userPassword = Yii::$app->security->generatePasswordHash($password);
                $user->userOTP = new Expression('NULL');
                
                if($user->save())
                {
                    $data['code'] = 200;
                    $data['message'] = Strings::getString(18);
                }
                else
                {
                    $data['code'] = 200;
                    $data['message'] = Strings::getString(19);
                }
            }
            else
            {
                $data['code'] = 500;
                $data['message'] = Strings::getString(17);
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    /**
     * Update fan profile
     * @return mixed all profile information
     */

    public static function updateFanProfile($request)
    {
        $data = [];

        if(self::validateInputs(['fullName', 'displayName', 'email', 'password'], ['profileImage'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {
                $model = self::$userModel;
                $model->userName = $displayName;
                $model->userFullName = $fullName;
                $model->userEmail = $email;
                $model->userPassword = Yii::$app->security->generatePasswordHash($password);

                if(!empty($profileImage))
                {
                    $imageName = Yii::$app->security->generateRandomString();
                    $boolUploadImage = self::uploadImageAndroid($profileImage, $imageName, 'profilePath', '.png', $model->userProfileImage);

                    if($boolUploadImage && is_array($boolUploadImage))
                    {
                        $model->userProfileImage = $boolUploadImage['name'];
                        $data['profileImage'] =  $boolUploadImage['url'];
                    }
                }

                if($model->save())
                {
                    $data['code'] = 200;
                    $data['message'] = Strings::getString(4);
                    $data['fullName'] = $model->userFullName;
                    $data['displayName'] = $model->userName;
                    $data['email'] = $model->userEmail;
                    $data['status'] = $model->userStatus;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = current($model->getFirstErrors());
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Player list, 
     * @param $startLimit start From
     * @param $endLimit end From
     * @return mixed all profile information
     */
    public static function playerList($request)
    {
        $data = [];

        if(self::validateInputs(['startLimit', 'endLimit'], ['keyword', 'filter'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);

            if(!isset($filter))
            {
                $filter = 'asc';
            }


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;


            try
            {
                $users = Users::find()
                    ->joinWith('players')
                    ->where(['userStatus' => 'Active', 'userRole' => 'Player'])
                    ->andFilterWhere(['like', 'userName', @$keyword])
                    ->orderBy('userName '.$filter)
                    ->limit($limit)
                    ->offset($offset)
                    ->all();


                $allPlayers = [];
                if($users)
                {
                    foreach($users as $player)
                    {
                        $tmpPlayer = [];

                        $tmpPlayer['userID'] = $player->pkUserID;
                        $tmpPlayer['playerFullName'] = $player->userFullName;
                        $tmpPlayer['playerUserName'] = $player->userName;
                        $tmpPlayer['playerProfessionalDetails'] = (isset($player->players)) ? $player->players->playerProfessionalDetails : NULL;
                        $tmpPlayer['playerLikes'] = $player->getLikedBy()->count();
                        $tmpPlayer['playerComments'] = $player->getPlayerComments()->count();
                        $tmpPlayer['playerFollows'] = $player->getPlayerFollowers()->count();
                        $tmpPlayer['playerProfileImage'] = self::generateImagePath($player->userProfileImage, 'profilePath');
                        

                        $isLiked = Likes::find()->where(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $player->pkUserID])->one();
                        $isFollowed = Followers::find()->where(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $player->pkUserID])->one();


                        $tmpPlayer['isLiked'] = 0;
                        $tmpPlayer['isFollowed'] = 0;

                        if($isLiked)
                        {
                            $tmpPlayer['isLiked'] = 1;
                        }

                        if($isFollowed)
                        {
                            $tmpPlayer['isFollowed'] = 1;
                        }

                        $allPlayers[] = $tmpPlayer;
                    }

                    $data['code'] = 200;
                    $data['message'] = 'Success';
                    $data['playerList'] = $allPlayers;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = 'No results found';
                }

            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Player details, 
     * @param $playerID 
     * @return mixed all data of a player
     */
    public static function playerDetails($request)
    {
        $data = [];

        if(self::validateInputs(['playerID'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            try
            {
                $model = Users::find()
                    ->joinWith('players')
                    ->where(['pkUserID' => $playerID,'userStatus' => 'Active', 'userRole' => 'Player'])
                    ->one();


                if($model)
                {
                    $data['code'] = 200;
                    /*$data['userID'] = $model->pkUserID;
                    $data['fullName'] = $model->userFullName;
                    $data['displayName'] = $model->userName;
                    $data['email'] = $model->userEmail;
                    $data['profileImage'] = self::generateImagePath($model->userProfileImage, 'profilePath');
                    $data['status'] = $model->userStatus;
                    $data['authToken'] = Yii::$app->security->generateRandomString(64);
                    $data['role'] = $model->userRole;*/
                    $data['message'] = Strings::getString(6);

                    if($model->userRole == 'Player')
                    {
                        $player = $model->players;

                        $playerData = [];
                        $playerData['gender'] = $player->playerGender;
                        $playerData['height'] = $player->playerHeight;
                        $playerData['weight'] = $player->playerWeight;
                        $playerData['pastInjuriesRecord'] = $player->playerPastInguriesRecord;
                        $playerData['bloodType'] = $player->playerBloodType;
                        $playerData['placeOfBirth'] = $player->playerBirthPlace;
                        $playerData['origin'] = $player->playerOrigin;
                        $playerData['nationalty'] = $player->fkCountry->countryName;
                        $playerData['position'] = $player->playerPosition;
                        $playerData['professionalDetails'] = $player->playerProfessionalDetails;
                        $playerData['careerStatics'] = $model->getPlayerStats()->select('playerStatValue')->indexBy('playerStatName')->column();
                        $playerData['skills'] = $player->playerShortVideo;
                        $playerData['biography'] = $player->playerBiography;
                        $playerData['socialUrl']['facebook'] = $player->playerFacebookLink;
                        $playerData['socialUrl']['google'] = $player->playerGooleLink;
                        $playerData['socialUrl']['twitter'] = $player->playerTwitterLink;

                        $data['player'] = $playerData;
                    }
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = Strings::getString(5);
                }

            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    /**
     * commentList
     * @return mixed List of comments
     */
    public static function commentList($request)
    {
        $data = [];

        if(self::validateInputs(['playerID', 'startLimit', 'endLimit'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            try
            {
                $comments = Comments::find()
                    ->joinWith('fkFan')
                    ->where(['fkPlayerID' => $playerID])
                    ->limit($limit)
                    ->offset($offset)
                    ->all();

                if($comments)
                {
                    $allComments = [];

                    foreach($comments as $comment)
                    {
                        $tmpComments = [];

                        $tmpComments['name'] = $comment->fkFan->userName;
                        $tmpComments['image'] = self::generateImagePath( $comment->fkFan->userProfileImage, 'profilePath');
                        $tmpComments['comment'] = $comment->commentContent;

                        $allComments[] = $tmpComments;
                    }

                    $data['code'] = 200;
                    $data['message'] = Strings::getString(6);
                    $data['comments'] = $allComments;
                }
                else
                {

                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    /**
     * Add Comment
     * @param playerID
     * @param comment
     * @return mixed Success/Failure
     */
    public static function addComment($request)
    {
        $data = [];

        if(self::validateInputs(['playerID', 'comment'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            try
            {
                $model = new Comments;

                $model->fkFanID = self::$userModel->pkUserID;
                $model->fkPlayerID = $playerID;
                $model->commentContent = $comment;
                $model->commentAdded = new Expression('NOW()');

                if($model->save())
                {
                    $data['code'] = 200;
                    $data['message'] = Strings::getString(7);
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = current($model->getFirstErrors());
                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Set Follow
     * @param playerID
     * @return mixed Success/Failure
     */
    public static function setFollow($request)
    {
        $data = [];

        if(self::validateInputs(['playerID', 'follow'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            try
            {
                // Follow Player
                if($follow == 1)
                {
                    /**
                     * Check if it is already following or not
                     */

                    $checkFollowing = Followers::find()->where(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $playerID])->one();

                    if($checkFollowing)
                    {
                        $data['code'] = 200;
                        $data['message'] = Strings::getString(20);
                    }
                    else
                    {
                        $model = new Followers;
                        $model->fkFanID = self::$userModel->pkUserID;
                        $model->fkPlayerID = $playerID;
                        $model->followerAdded = new Expression('NOW()');

                        if($model->save())
                        {
                            $data['code'] = 200;
                            $data['message'] = Strings::getString(8,  [$model->fkPlayer->userName]);
                        }
                        else
                        {
                            $data['code'] = 500;
                            $data['message'] = current($model->getFirstErrors());
                        }
                    }
                }
                else
                {
                    $model = Followers::findOne(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $playerID]);

                    if($model->delete())
                    {
                        $data['code'] = 200;
                        $data['message'] = Strings::getString(9);
                    }
                    else
                    {
                        $data['code'] = 500;
                        $data['message'] = current($model->getFirstErrors());
                    }
                }
                
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Set Likes
     * @param playerID
     * @return mixed Success/Failure
     */
    public static function setLike($request)
    {
        $data = [];

        if(self::validateInputs(['playerID', 'like'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            try
            {
                // Follow Player
                if($like == 1)
                {
                    /**
                     * Check if it is already following or not
                     */

                    $checkFollowing = Likes::find()->where(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $playerID])->one();

                    if($checkFollowing)
                    {
                        $data['code'] = 200;
                        $data['message'] = Strings::getString(21);
                    }
                    else
                    {
                        $model = new Likes;
                        $model->fkFanID = self::$userModel->pkUserID;
                        $model->fkPlayerID = $playerID;
                        $model->likeAdded = new Expression('NOW()');

                        if($model->save())
                        {
                            $data['code'] = 200;
                            $data['message'] = Strings::getString(10,  [$model->fkPlayer->userName]);
                        }
                        else
                        {
                            $data['code'] = 500;
                            $data['message'] = current($model->getFirstErrors());
                        }
                    }

                }
                else
                {
                    $model = Likes::findOne(['fkFanID' => self::$userModel->pkUserID, 'fkPlayerID' => $playerID]);

                    if($model->delete())
                    {
                        $data['code'] = 200;
                        $data['message'] = Strings::getString(11);
                    }
                    else
                    {
                        $data['code'] = 500;
                        $data['message'] = current($model->getFirstErrors());
                    }
                }
                
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Fan - List of all player fan is following
     * @param playerID
     * @return mixed Success/Failure
     */
    public static function followingList($request)
    {
        $data = [];

        if(self::validateInputs(['startLimit', 'endLimit'], ['keyword', 'filter'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);

            if(!isset($filter))
            {
                $filter = 'asc';
            }


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            try
            {
                $followings = Followers::find()
                    ->joinWith('fkPlayer')
                    ->where(['fkFanID' => self::$userModel->pkUserID])
                    ->andFilterWhere(['like', 'userName', @$keyword])
                    ->orderBy('userName '.$filter)
                    ->limit($limit)
                    ->offset($offset)
                    ->all();

                if($followings)
                {
                    $allComments = [];

                    foreach($followings as $following)
                    {
                        $tmpPlayer = [];

                        $tmpPlayer['playerID'] = $following->fkPlayerID;
                        $tmpPlayer['playerUserName'] = $following->fkPlayer->userName;
                        $tmpPlayer['playerProfessionalDetails'] = $following->fkPlayer->players->playerProfessionalDetails;
                        $tmpPlayer['playerLikes'] = $following->fkPlayer->getLikedBy()->count();
                        $tmpPlayer['playerComments'] = $following->fkPlayer->getPlayerComments()->count();
                        $tmpPlayer['playerProfileImage'] = self::generateImagePath( $following->fkPlayer->userProfileImage, 'profilePath');

                        $allComments[] = $tmpPlayer;
                    }

                    $data['code'] = 200;
                    $data['message'] = Strings::getString(6);
                    $data['comments'] = $allComments;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = Strings::getString(14);
                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Signup WebService for Player
     * @param fullName
     * @param displayName
     * @param email
     * @param password
     */
    public static function signupPlayer($request)
    {
        $data = [];

        if(self::validateInputs(['fullName', 'displayName', 'email', 'password', 'gender', 'height', 'weight', 'pastInjuriesRecord', 'bloodType', 'placeOfBirth', 'countryID', 'origin', 'position'], ['profileImage'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {
                $model = new Users;
                $model->userName = $displayName;
                $model->userFullName = $fullName;
                $model->userEmail = $email;
                $model->userPassword = Yii::$app->security->generatePasswordHash($password);
                $model->userAuthToken = Yii::$app->security->generateRandomString(64);
                $model->userStatus = 'Inactive';

                if(!empty($profileImage))
                {
                    $imageName = Yii::$app->security->generateRandomString();
                    $boolUploadImage = self::uploadImageAndroid($profileImage, $imageName, 'profilePath', '.png', $model->userProfileImage);

                    if($boolUploadImage && is_array($boolUploadImage))
                    {
                        $model->userProfileImage = $boolUploadImage['name'];
                        $data['profileImage'] =  $boolUploadImage['url'];
                    }
                }

                if($model->save())
                {
                    $player = new Players;
                    $player->fkUserID = $model->pkUserID;
                    $player->playerGender = $gender;
                    $player->playerHeight = $height;
                    $player->playerWeight = $weight;
                    $player->playerBloodType = $bloodType;
                    $player->playerBirthPlace = $placeOfBirth;
                    $player->fkCountryID = $countryID;
                    $player->playerOrigin = $origin;
                    $player->playerPosition = $position;
                    $player->playerPastInguriesRecord = $pastInjuriesRecord;

                    if($player->save())
                    {
                        $boolSendMail = Yii::$app
                            ->mailer
                            ->compose(
                                ['html' => 'webservice/signupFan/html', 'text' => 'webservice/signupFan/text'],
                                ['user' => $model]
                            )
                            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
                            ->setTo($model->userEmail)
                            ->setSubject('Welcome to '.Yii::$app->name)
                            ->send();

                        if($boolSendMail)
                        {
                            $data['code'] = 200;
                            $data['message'] = Strings::getString(0);
                            $data['fullName'] = $model->userFullName;
                            $data['displayName'] = $model->userName;
                            $data['email'] = $model->userEmail;
                            $data['status'] = $model->userStatus;
                            $data['authToken'] = $model->userAuthToken;
                        }
                        else
                        {
                            throw new Exception('Error while sending mail. Please try again');
                        }
                    }
                    else
                    {
                        throw new Exception(Strings::getString(12));
                    }
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = current($model->getFirstErrors());
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }


    /**
     * Player - Get list of fan who likes that player
     * @return mixed Fans List
     */
    public static function likedFan($request)
    {
        $data = [];

        if(self::validateInputs(['startLimit', 'endLimit'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            try
            {
                $likes = Likes::find()
                    ->joinWith('fkFan')
                    ->where(['fkPlayerID' => self::$userModel->pkUserID])
                    ->limit($limit)
                    ->offset($offset)
                    ->all();

                if($likes)
                {
                    $allFans = [];

                    foreach($likes as $like)
                    {
                        $tmpFan = [];

                        $tmpFan['fanName'] = $like->fkFan->userName;
                        $tmpFan['fanProfileImage'] = self::generateImagePath( $like->fkFan->userProfileImage, 'profilePath');

                        $allFans[] = $tmpFan;
                    }

                    $data['code'] = 200;
                    $data['message'] = Strings::getString(6);
                    $data['likedFan'] = $allFans;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = Strings::getString(13);
                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Player - Get list of fan who commented on that player
     * @return mixed Fans List
     */
    public static function commentedFan($request)
    {
        $data = [];

        if(self::validateInputs(['startLimit', 'endLimit'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            try
            {
                $likes = Comments::find()
                    ->joinWith('fkFan')
                    ->where(['fkPlayerID' => self::$userModel->pkUserID])
                    ->limit($limit)
                    ->offset($offset)
                    ->all();

                if($likes)
                {
                    $allFans = [];

                    foreach($likes as $like)
                    {
                        $tmpFan = [];

                        $tmpFan['fanName'] = $like->fkFan->userName;
                        $tmpFan['fanComment'] = $like->commentContent;
                        $tmpFan['fanProfileImage'] = self::generateImagePath( $like->fkFan->userProfileImage, 'profilePath');

                        $allFans[] = $tmpFan;
                    }

                    $data['code'] = 200;
                    $data['message'] = Strings::getString(6);
                    $data['commentedFan'] = $allFans;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = Strings::getString(13);
                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Player - List of all followers
     * @return mixed Fans list
     */
    public static function followersList($request)
    {
        $data = [];

        if(self::validateInputs(['startLimit', 'endLimit'], [], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $limit = $endLimit - $startLimit;
            $offset = $startLimit;

            try
            {
                $followers = Followers::find()
                    ->joinWith('fkFan')
                    ->where(['fkPlayerID' => self::$userModel->pkUserID])
                    ->limit($limit)
                    ->offset($offset)
                    ->all();

                if($followers)
                {
                    $allFans = [];

                    foreach($followers as $follower)
                    {
                        $tmpFan = [];

                        $tmpFan['fanName'] = $follower->fkFan->userName;
                        $tmpFan['fanProfileImage'] = self::generateImagePath( $follower->fkPlayer->userProfileImage, 'profilePath');

                        $allFans[] = $tmpFan;
                    }

                    $data['code'] = 200;
                    $data['message'] = Strings::getString(6);
                    $data['fans'] = $allFans;
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = Strings::getString(14);
                }
            }
            catch(Exception $ex)
            {
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }



    /**
     * Update fan profile
     * @return mixed all profile information
     */

    public static function updatePlayerProfile($request)
    {
        $data = [];

        if(self::validateInputs(['fullName', 'displayName', 'email', 'password', 'gender', 'height', 'weight', 'pastInjuriesRecord', 'bloodType', 'placeOfBirth', 'countryID', 'origin', 'position', 'playerProfessionalDetails', 'careerStats', 'skillVideo', 'playerBiography', 'facebookUrl', 'twitterUrl', 'googleUrl'], ['profileImage'], $request))
        {
            /**
             * @todo Extract all vars in the function scope
             */
            extract((array) $request);


            $sqlTransaction = Yii::$app->db->beginTransaction();

            try
            {

                if(isset($_FILES['file']) && $_FILES['file']['size'] > 0)
                {
                    $boolImageUpload = self::uploadMedia($_FILES['file'], 'video');

                    if($boolImageUpload)
                    {
                        $mediaReturn = $boolImageUpload;
                    }
                    else
                    {
                        $data['imageError'] = 'Error while uploading media';
                    }
                }

                $model = self::$userModel;
                $model->userName = $displayName;
                $model->userFullName = $fullName;
                $model->userEmail = $email;
                $model->userPassword = Yii::$app->security->generatePasswordHash($password);


                if(!empty($profileImage))
                {
                    $imageName = Yii::$app->security->generateRandomString();
                    $boolUploadImage = self::uploadImageAndroid($profileImage, $imageName, 'profilePath', '.png', $model->userProfileImage);

                    if($boolUploadImage && is_array($boolUploadImage))
                    {
                        $model->userProfileImage = $boolUploadImage['name'];
                        
                    }
                }

                if($model->save())
                {

                    $player = Players::findOne(['fkUserID' => $model->pkUserID]);

                    if(isset($mediaReturn) && is_array($mediaReturn) && $player)
                    {
                        $player->playerShortVideo = $mediaReturn['fileName'];
                    }

                    if($careerStats && count($careerStats) > 0)
                    {
                    	$thisPlayerStats = PlayerStats::findAll(['fkPlayerID' => self::$userModel->pkUserID]);

                    	foreach($thisPlayerStats as $thisPlayerStat)
                    	{
                    		if(!$thisPlayerStat->delete())
                    		{
                    			throw new Exception(Strings::getString(16));
                    		}
                    	}

                    	
                		foreach($careerStats as $key => $value)
                		{
                			$playerStat = new PlayerStats;

                			$playerStat->fkPlayerID = $model->pkUserID;
                			$playerStat->playerStatName = $key;
                			$playerStat->playerStatValue = (string) $value;

                			if(!$playerStat->save())
                			{
                				throw new Exception(Strings::getString(16));
                			}
                		}
                    }

                    /**
                     * @todo Perform massive assignment
                     */
                    $player->playerGender = $gender;
                    $player->playerHeight = $height;
                    $player->playerWeight = $weight;
                    $player->playerBloodType = $bloodType;
                    $player->playerBirthPlace = $placeOfBirth;
                    $player->fkCountryID = $countryID;
                    $player->playerOrigin = $origin;
                    $player->playerPosition = $position;
                    $player->playerPastInguriesRecord = $pastInjuriesRecord;
                    $player->playerProfessionalDetails = $playerProfessionalDetails;
                    $player->playerBiography = $playerBiography;
                    
                    $player->playerFacebookLink = $facebookUrl;
                    $player->playerTwitterLink = $twitterUrl;
                    $player->playerGooleLink = $googleUrl;

                    if($player->save())
                    {
                    	$data['code'] = 200;
	                    $data['message'] = Strings::getString(4);
	                    $data['fullName'] = $model->userFullName;
	                    $data['displayName'] = $model->userName;
	                    $data['email'] = $model->userEmail;
                        $data['status'] = $model->userStatus;
	                    $data['skillVideo'] = stripslashes(Url::home(true)).Yii::$app->params['skillVideoPath'].$player->playerShortVideo;
                        $data['profileImage'] =  self::generateImagePath($model->userProfileImage, 'profilePath');
                    }
                    else
                    {
                    	throw new Exception(Strings::getString(15));
                    }
                }
                else
                {
                    $data['code'] = 500;
                    $data['message'] = current($model->getFirstErrors());
                }

                $sqlTransaction->commit();
            }
            catch(Exception $ex)
            {
                $sqlTransaction->rollBack();
                $data['code'] = 500;
                $data['message'] = $ex->getMessage();
                $data['debugMessage'] = $ex->getMessage(). ' at line '.$ex->getLine();
            }
        }
        else
        {
            $data['code'] = 200;
            $data['message'] = self::$validationErrorMessage;
            $data['errorKey'] = self::$validationErrorKey;
        }

        return $data;
    }






    /***************************************************
     *          Private Application Methods
    ***************************************************/

    /**
     * Validate Authentication token
     * @todo Search for Authentication token in user table
     */
    public static function validateToken($token)
    {
        $model = Users::findOne(['userAuthToken' => $token]);

        if($model)
        {
            /**
             * If found, Return true
             * else return false;
             */
            self::$userModel = $model;
            return true;
        }

        return false;
    }


    /**
     * Generate Profile Image from usermname string
     * @param $varUserName Username of user to get thier profile image
     * @param $varThumbnail Wether or not return a thumbnail (False by default)
     * @return string Complete Profile image url
     */
    private static function generateImagePath($imageName, $type)
    {
        if(is_null($imageName))
        {
            return null;
        }

        $varImageUrl = Url::base(true).Yii::$app->params[$type].$imageName;
        return $varImageUrl;
    }


    /**
     * Upload image method for Android
     * @param string $varImage (required) Base64 Encoded Image
     * @param string $varImageName (required) imageName
     * @param string $varPath (required) Path to save image
     * @return mixed array on success, false on fail
     */
    private function uploadImageAndroid($varImage, $varImageName, $varPath, $ext = '.png', $deleteImageName = false)
    {
		if($varImage)
        {
            $image_data = str_replace(' ',  '+',  $varImage);
            $image_data = base64_decode($image_data);
            
            $name =  \yii\helpers\Inflector::slug($varImageName).$ext;
            $file = Url::to('@frontend').'/web'.Yii::$app->params[$varPath].$name;
            if(file_put_contents($file, $image_data))
            {
            	if($deleteImageName)
            	{
            		@unlink(Url::to('@frontend').'/web'.Yii::$app->params[$varPath].$deleteImageName);
            	}

	            $data['name'] = $name;
	            $data['url'] = Url::base(true).Yii::$app->params[$varPath].$name;

	            return $data;
            }

            return false;
        }
    }


    /**
     * API Validation method
     * @todo Validate
     */

    public static function validateInputs($required = [], $optional = [], $request)
    {
        foreach($required as $eachRequired)
        {
            $thisKey = (isset($request->$eachRequired)) ? $request->$eachRequired : NULL;

            if(!isset($thisKey) && empty($thisKey))
            {
                self::$validationErrorKey = $eachRequired;
                self::$validationErrorMessage = sprintf('Parameter "%s" is missing', $eachRequired);
                return false;
            }
        }
        return true;
    }


    /**
     * Upload Media File to Server
     */
    public static function uploadMedia($mediaFile, $mediaType)
    {
        // Uploaded File Info Array
        $arrMediaInfo = pathinfo($mediaFile['name']);

        // Uploaded file Extension
        $extension = $arrMediaInfo['extension'];

        // Random Key to assign
        $rand = Yii::$app->security->generateRandomString();

        // Upload Dir path
        $uploadDir = Url::to('@frontend').'/web'.Yii::$app->params['skillVideoPath'];

        // Set randKey to file name
        $varFileNameWithTimeStamp = $arrMediaInfo['filename'].'-'.$rand;

        // Bind filename and extension
        $newFileName = $uploadDir.'/'.$varFileNameWithTimeStamp.'.'.$extension;

        // Thumbnail file name
        $newThumbnailFileName = $uploadDir.'/'.$varFileNameWithTimeStamp.'-thumb.'.$extension;


        if(move_uploaded_file($mediaFile['tmp_name'], $newFileName))
        {
            
            if($mediaType == 'video')
            {
                /*$return = true;
                $targetVideo = Yii::$app->getBasePath().'/web/'.$newFileName;
                $targetThumbnail = Yii::$app->getBasePath().'/web/'.$uploadDir.'/'.$varFileNameWithTimeStamp.'.jpg';
                $cmd = "ffmpeg -i '$targetVideo' -ss 00:00:01 -vframes 1 -vf crop=320:320 -y '$targetThumbnail'";
                // $cmd = "ffmpeg -i '$targetVideo' -ss 00:00:01 -vframes 1 -s 100x100 -y '$targetThumbnail'";
                exec($cmd, $output, $return);
                if(!$return)
                {
                    $data['thumbPath'] = $uploadDir.'/'.$varFileNameWithTimeStamp.'.jpg';
                    $data['thumbUrl'] = Url::base(true).'/'.$uploadDir.'/'.$varFileNameWithTimeStamp.'.jpg';
                }*/
            }
            else
            {
                $data['thumbPath'] = '';
                $data['thumbUrl'] = '';
            }

            $data['fileName'] = $varFileNameWithTimeStamp.'.'.$extension;
            $data['fileUrl'] = Url::base(true).'/'.$newFileName;
            // $data['filePath'] = $newFileName;

            return $data;
        }
        return false;
    }
}