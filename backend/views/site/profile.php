<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \frontend\models\ResetPasswordForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Profile';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-reset-password">

    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'userName')->textInput() ?>
                <?= $form->field($model, 'userEmail')->textInput() ?>
                <?= $form->field($model, 'imageFile')->fileInput()->hint('Only if you want to change image'); ?>

                <p><strong>Enter password only if you want to change it:</strong></p>

                <?= $form->field($model, 'userPassword')->textInput(['value' => '']) ?>
                <?= $form->field($model, 'confirm_password')->textInput() ?>

                <div class="form-group">
                    <?= Html::submitButton('Save', ['class' => 'btn btn-primary btn-flat']) ?>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
