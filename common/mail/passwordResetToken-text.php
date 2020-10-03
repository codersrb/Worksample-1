<?php

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->userResetToken]);
?>
Dear <?php echo $user->userName; ?>,

We have received your request for ThatSalesApp password to be reset.
In the event that another person sent this request to us, or if you remember your password and do not wish to change it, you can simply ignore this message and continue to use your old password.

If you would like to reset your password, please click on this

<?php echo $resetLink; ?>

If you're unable to click on the link you can also copy the URL and paste it into your browser manually.

Keep shopping!!!

With Regards
Team ThatSalesApp