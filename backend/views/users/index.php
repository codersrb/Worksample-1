<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Users', ['create'], ['class' => 'btn btn-success btn-flat']) ?>
    </p>

	<div class="box">
        <div class="box-body table-responsive no-padding">
	    <?= GridView::widget([
	        'dataProvider' => $dataProvider,
	        // 'filterModel' => $searchModel,
            'columns' => [
	            ['class' => 'yii\grid\SerialColumn'],

    		    'pkUserID',
                'userPhone',
                'userEmail:email',
                'userAuthKey',
                'userPassword',
                'userResetToken',
                // 'userName',
                // 'userPicture',
                // 'userAdded',
                // 'userModified',
                // 'userRole',
                // 'userStatus',

		            ['class' => 'yii\grid\ActionColumn'],
		        ],
		    ]); ?>
		</div>
	</div>
</div>
