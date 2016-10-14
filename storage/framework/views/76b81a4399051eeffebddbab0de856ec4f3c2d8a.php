<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo e(trans('messages.title')); ?></title>
    <link rel="icon" type="image/png" href="<?php echo e(asset('installer/img/favicon/favicon-16x16.png')); ?>" sizes="16x16"/>
    <link rel="icon" type="image/png" href="<?php echo e(asset('installer/img/favicon/favicon-32x32.png')); ?>" sizes="32x32"/>
    <link rel="icon" type="image/png" href="<?php echo e(asset('installer/img/favicon/favicon-96x96.png')); ?>" sizes="96x96"/>
    <link href="<?php echo e(url('/').elixir('themes/default/assets/css/style.css', '')); ?>" rel="stylesheet"/>
  </head>
  <body>


  <div class="container">
    

<div class="row">
    <div class="col-md-6 col-md-offset-3">
      <div class="installer-container">  
             <div class="settings-block">
                    Socialite Installation <a href="#" class="close-modal pull-right" data-dismiss="modal"><i class="fa fa-times"></i></a>
                </div>
                <ul class="nav nav-pills settings-list">
                    <li class="<?php echo e(isActive('LaravelInstaller::welcome')); ?>"><a href="<?php echo url('install'); ?>"  aria-expanded="true">Welcome</a></li>
                    <li class="<?php echo e(isActive('LaravelInstaller::environment')); ?>"><a href="<?php echo url('install/environment'); ?>"  aria-expanded="false">Environment Settings</a></li>
                    <li class="<?php echo e(isActive('LaravelInstaller::requirements')); ?>"><a href="<?php echo url('install/requirements'); ?>"  aria-expanded="false">Requirements</a></li>
                    <li class="<?php echo e(isActive('LaravelInstaller::permissions')); ?>"><a href="<?php echo url('install/permissions'); ?>"  aria-expanded="false">Permissions</a></li>
                    <li class="<?php echo e(isActive('LaravelInstaller::final')); ?>"><a href="<?php echo url('install/finished'); ?>"  aria-expanded="false">Finished</a></li>
                </ul>
                <div class="tab-content settings-content">
                    <?php echo $__env->yieldContent('container'); ?>
                </div>
      </div>
      </div>
</div>
  </div>



  </body>
</html>