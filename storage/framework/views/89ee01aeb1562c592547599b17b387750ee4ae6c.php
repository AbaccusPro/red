<?php $__env->startSection('title', trans('messages.requirements.title')); ?>
<?php $__env->startSection('container'); ?>

<ul class="list-group">
    <?php foreach($requirements['requirements'] as $extention => $enabled): ?>
    <li class="list-group-item">
    	<?php echo e($extention); ?>

    	<div class="pull-right">
    		<?php if($enabled): ?> 
	    		<i class="fa success fa-check-circle-o"></i>
	    	<?php else: ?> 
	    		<i class="fa error fa-times-circle-o"></i>
	    	<?php endif; ?>
    	</div>
    </li>
    <?php endforeach; ?>
</ul>

<?php if(!isset($requirements['errors'])): ?>
    <div class="btn-installer">
    	<a class="btn btn-primary" href="<?php echo e(route('LaravelInstaller::permissions')); ?>">
		    <?php echo e(trans('messages.next')); ?>

	    </a>
    </div>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('vendor.installer.layouts.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>