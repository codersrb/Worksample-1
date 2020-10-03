<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Users */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'userEmail')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userAuthKey')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userPassword')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userResetToken')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userName')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userNumber')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userPicture')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'userAdded')->textInput() ?>

    <?= $form->field($model, 'userModified')->textInput() ?>

    <?= $form->field($model, 'userRole')->dropDownList([ 'User' => 'User', 'Admin' => 'Admin', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'userStatus')->dropDownList([ 'Pending' => 'Pending', 'Active' => 'Active', 'Inactive' => 'Inactive', ], ['prompt' => '']) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success btn-flat' : 'btn btn-primary btn-flat']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
