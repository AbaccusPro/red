<?php $__env->startSection('title', trans('messages.welcome.title')); ?>
<?php $__env->startSection('container'); ?>
    <p class="paragraph"><?php echo e(trans('messages.welcome.message')); ?></p>
    <div class="btn-installer"><a href="<?php echo e(route('LaravelInstaller::environment')); ?>" class="btn btn-primary"><?php echo e(trans('messages.next')); ?></a></div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('vendor.installer.layouts.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>