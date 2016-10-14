<?php $__env->startSection('title', trans('messages.environment.title')); ?>
<?php $__env->startSection('container'); ?>
    <?php if(session('message')): ?>
    <div class="alert alert-success">
        <?php echo e(session('message')); ?>

    </div>
    <?php endif; ?>
    <form method="post" class="installer-form" action="<?php echo e(route('LaravelInstaller::environmentSave')); ?>">
        <textarea class="form-control" rows="12" name="envConfig"><?php echo e($envConfig); ?></textarea>
        <?php echo csrf_field(); ?>

             <button class="btn btn-success" type="submit"><?php echo e(trans('messages.environment.save')); ?></button>
    </form>
    <?php if(!isset($environment['errors'])): ?>
    <div class="btn-installer">
        <a class="btn btn-primary" href="<?php echo e(route('LaravelInstaller::requirements')); ?>">
        <?php echo e(trans('messages.next')); ?>

        </a>
    </div>
    <?php endif; ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('vendor.installer.layouts.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>