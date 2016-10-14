<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf_token" content="<?php echo csrf_token() ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, height=device-height, target-densitydpi=device-dpi" />
        <meta property="og:image" content="<?php echo e(url('setting/logo.jpg')); ?>" />
        <meta property="og:title" content="<?php echo e(Setting::get('site_title')); ?>" />
        <meta property="og:type" content="Social Network" />
        <meta name="keywords" content="<?php echo e(Setting::get('meta_keywords')); ?>">
        <meta name="description" content="<?php echo e(Setting::get('meta_description')); ?>">
        <link rel="icon" type="image/x-icon" href="<?php echo url('setting/'.Setting::get('favicon')); ?>">


        <title><?php echo e(Theme::get('title')); ?></title>

        <?php echo Theme::asset()->styles(); ?>


        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.min.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script type="text/javascript">
        function SP_source() {
          return "<?php echo e(url('/')); ?>/";
        }
        var base_url = "<?php echo e(url('/')); ?>/";
        var theme_url = "<?php echo Theme::asset()->url(''); ?>";
        </script>
        <?php echo Theme::asset()->scripts(); ?>

        <?php if(Setting::get('google_analytics') != NULL): ?>
            <?php echo Setting::get('google_analytics'); ?>

        <?php endif; ?>
    </head>
    <body>
        <div class="">
            <?php echo Theme::partial('guest-header'); ?>    
        </div>
        
        <?php echo Theme::content(); ?>

        
        <?php echo Theme::partial('footer'); ?>


        <?php echo Theme::asset()->container('footer')->scripts(); ?>

    </body>
</html>
