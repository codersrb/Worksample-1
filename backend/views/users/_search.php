<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\UsersSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="users-search">
    <div class="box-group" id="accordion">
        <div class="box box-info box-solid">
            <div class="box-header with-border">
                <h4 class="box-title">
                    <a data-toggle="" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="">
                        Search Form
                    </a>
                    &nbsp;
                    
                </h4>
            </div>
            <div id="collapseOne" class="panel-collapse" aria-expanded="false">
                <div class="box-body">

                    <?php $form = ActiveForm::begin([
                        'action' => ['index'],
                        'method' => 'get',
                    ]); ?>
                    <div class="row">

                        <div class="col-md-2">    <?= $form->field($model, 'pkUserID') ?></div>

<div class="col-md-2">    <?= $form->field($model, 'userEmail') ?></div>

<div class="col-md-2">    <?= $form->field($model, 'userAuthKey') ?></div>

<div class="col-md-2">    <?= $form->field($model, 'userPassword') ?></div>

<div class="col-md-2">    <?= $form->field($model, 'userResetToken') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userName') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userNumber') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userPicture') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userAdded') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userModified') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userRole') ?></div>

<div class="col-md-2">    <?php // echo $form->field($model, 'userStatus') ?></div>

                    </div>
                    <div class="form-group">
                        <?= Html::submitButton('Search', ['class' => 'btn btn-primary btn-flat']) ?>
                        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-default btn-flat']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
