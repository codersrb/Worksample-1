<?php
namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $userEmail;


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
                'targetClass' => '\common\models\User',
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
        
        if (!User::isPasswordResetTokenValid($user->userResetToken)) {
            $user->generatePasswordResetToken();
        }
        
        if (!$user->save()) {
            return false;
        }

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'passwordResetToken-html', 'text' => 'passwordResetToken-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->userEmail)
            ->setSubject('ThatSalesApp || Password Recovery')
            ->send();
    }
}