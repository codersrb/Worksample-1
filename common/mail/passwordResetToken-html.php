<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */

$resetLink = Yii::$app->urlManager->createAbsoluteUrl(['site/reset-password', 'token' => $user->userResetToken]);
?>
<div class="password-reset">
<br/>
Dear <?php echo $user->userName; ?>,<br/>
<br/>
We have received your request for ThatSalesApp password to be reset.<br/>
In the event that another person sent this request to us, or if you remember your password and do not wish to change it, you can simply ignore this message and continue to use your old password.<br/>
<br/>
If you would like to reset your password, please click on this<br/>
<br/>
<?= Html::a(Html::encode($resetLink), $resetLink) ?><br/>
<br/>
If you're unable to click on the link you can also copy the URL and paste it into your browser manually.<br/>
<br/>
Keep shopping!!!<br/>
<br/>
With Regards<br/>
Team ThatSalesApp <br/>
</div>
