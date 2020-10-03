<?php

namespace frontend\modules\api\models;

use Yii;
use yii\base\Model;

/**
 * Strings model
 * Model contains all the WebService response strings
 * Changes here will do changes throughtout the WebService
 */
class Strings extends Model
{
    public function getString($key, $args = null)
    {
    	$arrStringTemplates = [

            // 0 : Fan Signup
    		sprintf('Welcome to %s', Yii::$app->name),
            // 1 : Fan Login
    		sprintf('Incorrect email or password'),
            // 2 : Fan Login
            sprintf('Your account is inactive, Please contact admin'),
            // 3 : Forget Password
            sprintf('Password reset instruction sent on email'),
            // 4 : Fan Profile Update
    		sprintf('Profile Updated Successfully'),
            // 5 : Player Details
            sprintf('User account doesn\'t exists'),
            // 6 : Player Details, Comment List
            sprintf('Success'),
            // 7 : Add Comment
            sprintf('Comment added successfully'),
            // 8 : Follow - Success
            sprintf('You are following %s', $args[0]),
            // 9 : Unfollow
            sprintf('Removed from follow'),
            // 10 : Likes - Success
            sprintf('You liked %s', $args[0]),
            // 11 : Dislike
            sprintf('Removed from like'),
            // 12 : SignUp Player
            sprintf('Error while creating player. Please try again'),
            // 13 : Fan liked list
            sprintf('No records found'),
            // 14 : Following list
            sprintf('No records found'),
            // 15 : Update Proile - Player
            sprintf('Error while updating profile information'),
            // 16 : Update Profile - Career stats
            sprintf('Error while updating career stats'),
            // 17 : Reset Password
            sprintf('User account can\'t be found'),
            // 18 : Reset Password - Success
            sprintf('Password changed successfully'),
            // 19 : Reset Password - Error
            sprintf('Error while resetting password'),
            // 20 : Already following
            sprintf('You are already following this player'),
            // 21 : Already liked
            sprintf('You have already liked this player'),
            // 22 : Player Video Success
            sprintf('Video Uploaded Successfully'),

    	];


        /**
         * If key exists, Do the magic
         */
    	if(array_key_exists($key, $arrStringTemplates))
    	{
    		return $arrStringTemplates[$key];
    	}
    	else
    	{
    		return 'Response string not found';
    	}
    }
}