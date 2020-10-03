<?php
namespace frontend\modules\api\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $userEmail;
    public $otp = false;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['userEmail', 'filter', 'filter' => 'trim'],
            ['userEmail', 'required'],
            ['userEmail', 'email'],
            ['userEmail', 'exist',
                'filter' => ['userStatus' => User::STATUS_ACTIVE],
                'message' => 'There is no user with such email.'
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return boolean whether the email was send
     */
    public function sendEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'userStatus' => User::STATUS_ACTIVE,
            'userEmail' => $this->userEmail,
        ]);

        if (!$user) {
            return false;
        }
        
        if(!User::isPasswordResetTokenValid($user->userResetToken))
        {
            $otp = rand(100000, 999999);
            $user->generatePasswordResetToken();
            $user->userOTP = $otp;

        }
        
        if (!$user->save()) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user, 'otp' => $this->otp]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->userEmail)
            ->setSubject('Password reset for ' . Yii::$app->name)
            ->send();
    }
}
