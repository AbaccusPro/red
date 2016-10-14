<?php $__env->startSection('title', trans('messages.final.title')); ?>
<?php $__env->startSection('container'); ?>
    <p class="paragraph"><?php echo e(session('message')['message']); ?></p>
    <div class="btn-installer">
    	<a href="<?php echo url('/'); ?>" class="btn btn-primary"><?php echo e(trans('messages.final.exit')); ?></a>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('vendor.installer.layouts.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>