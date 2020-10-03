<?php

/* @var $this \yii\web\View */
/* @var $content string */

use backend\assets\AppAsset;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use common\widgets\Alert;
use yii\widgets\Menu;
use backend\models\SalesRequests;
use yii\helpers\Url;
use yii\web\View;

AppAsset::register($this);

$session = Yii::$app->session;
$session->open();
$bodyClass = '';
if($session->get('sidebar'))
{
  if($session->get('sidebar') == 'collapse')
  {
    $bodyClass = ' sidebar-collapse';
  }
}

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body class="hold-transition skin-yellow sidebar-mini <?php echo $bodyClass; ?>">
<?php $this->beginBody() ?>

<!-- Site wrapper -->
<div class="wrapper">

  <header class="main-header">
    <!-- Logo -->
    <a href="<?php echo Yii::$app->homeUrl; ?>" class="logo">
      <!-- mini logo for sidebar mini 50x50 pixels -->
      <span class="logo-mini"><b><?php echo Yii::$app->params['appShortName']; ?></b></span>
      <!-- logo for regular state and mobile devices -->
      <span class="logo-lg"><b><?php echo Yii::$app->params['appName']; ?></b></span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->

    <nav class="navbar navbar-static-top">
      <!-- Sidebar toggle button-->
      <a href="#" id="admin-sidebar-toggle" class="sidebar-toggle" data-toggle="offcanvas" role="button">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </a>

      <div class="navbar-custom-menu">

        <ul class="nav navbar-nav">

        <?php

        if(!Yii::$app->user->isGuest) : ?>
          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <?php

            if(isset(Yii::$app->user->identity->userProfilePicture))
            {
              ?>
                <img src="<?php echo Url::to('@fronturl').'/'.Yii::$app->params['profileImagePath'].Yii::$app->user->identity->userProfilePicture; ?>" class="user-image" alt="Profile Image">
              <?php
            }
            ?>
              <span class="hidden-xs"><?php echo (isset(Yii::$app->user->identity->userName)) ? Yii::$app->user->identity->userName : ''; ?></span>
            </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header">
                <?php
                if(isset(Yii::$app->user->identity->userProfilePicture))
                {
                  ?>
                    <img src="<?php echo Url::to('@fronturl').'/'.Yii::$app->params['profileImagePath'].Yii::$app->user->identity->userProfilePicture; ?>" class="img-circle" alt="Profile Image">
                  <?php
                }
                ?>
                <p>
                  <?php echo (isset(Yii::$app->user->identity->userName)) ? Yii::$app->user->identity->userName : ''; ?>
                  <small>Member since <?php echo Yii::$app->formatter->asDate(Yii::$app->user->identity->userAdded); ?></small>
                </p>
              </li>
              <!-- Menu Footer-->
              <li class="user-footer">
                <div class="pull-left">
                  <?php echo Html::a('Profile', ['site/profile'], ['class' => 'btn btn-default btn-flat']); ?>
                </div>
                <div class="pull-right">
                   <?php
                    echo Html::beginForm(['/site/logout'], 'post');
                    echo Html::submitButton('Logout', ['class' => 'btn btn-default btn-flat']);
                    echo Html::endForm();
                  ?>
                </div>
              </li>
            </ul>
          </li>

          <?php endif; ?>
        </ul>

      </div>
    </nav>
  </header>

  <!-- =============================================== -->

  <!-- Left side column. contains the sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
      </div>
      <!-- sidebar menu: : style can be found in sidebar.less -->

      <?php

        $menuItems = [];
        $menuItems[] = ['label' => 'MAIN NAVIGATION', 'options' => ['class' => 'header'], 'link' => false];
        if(Yii::$app->user->isGuest)
        {
            $menuItems[] = ['label' => '<i class="fa fa-sign-in" aria-hidden="true"></i> <span>Login</span>', 'url' => ['/site/login']];
        }
        else
        {
            if(Yii::$app->user->identity->userRole == 'Admin')
            {
                $menuItems[] = ['label' => '<i class="fa fa-dashboard"></i> <span>Dashboard</span>', 'url' => ['/site/index']];
                $menuItems[] = ['label' => '<i class="fa fa-users"></i> <span>Users</span>', 'url' => ['/users/index']];
            }

        }

        echo Menu::widget([
        'options' => ['class' => 'sidebar-menu treeview'],
        'items' => $menuItems,
        'submenuTemplate' => "\n<ul class='treeview-menu'>\n{items}\n</ul>\n",
        'encodeLabels' => false, //allows you to use html in labels
        'activateParents' => true,
        ]);

        ?>
    </section>
    <!-- /.sidebar -->
  </aside>

  <!-- =============================================== -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?php echo $this->title; ?>
      </h1>
      <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
    </section>

    <!-- Main content -->
    <section class="content">
        <?= Alert::widget() ?>
        <?= $content ?>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <div class="pull-right hidden-xs">
    </div>
    <strong>Copyright &copy; <?= date('Y') ?> <?php echo Yii::$app->params['appName']; ?>.</strong> All rights
    reserved.
  </footer>
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<?php
$js="
jQuery('#admin-sidebar-toggle').click(function(){
  if(jQuery('body').hasClass('sidebar-collapse'))
  {
    // Collapsed
    jQuery.ajax({
      url : '".Url::toRoute('ajax/sidebar', true)."',
      method: 'GET',
      data: {action: 'collapse'},
      error: function(){
        alert('Error while setting session');
      }
    });
  }
  else
  {
    // Expanded

    jQuery.ajax({
      url : '".Url::toRoute('ajax/sidebar', true)."',
      method: 'GET',
      data: {action: 'dont'},
      error: function(){
        alert('Error while setting session');
      }
    });

  }
});
";
$this->registerJs($js, View::POS_READY);
?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
