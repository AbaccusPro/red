<?php $__env->startSection('title', trans('messages.permissions.title')); ?>
<?php $__env->startSection('container'); ?>

<ul class="list-group">
    <?php foreach($permissions['permissions'] as $permission): ?>
    	<li class="list-group-item">
        	<?php echo e($permission['folder']); ?><span>
        	&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        	<?php echo e($permission['permission']); ?></span>
        	<div class="pull-right">
        		<?php if($permission['isSet']): ?>
		    		<i class="fa success fa-check-circle-o"></i>
	        	<?php else: ?>
		    		<i class="fa error fa-times-circle-o"></i>
	        	<?php endif; ?>
        	</div>
        </li>
    <?php endforeach; ?>
</ul>

<div class="text-center">
	<i class="fa fa-spin fa-spinner hidden"></i>
</div>


<?php if(!isset($permissions['errors'])): ?>
	<div class="btn-installer">
		<a class="btn btn-primary" href="<?php echo e(route('LaravelInstaller::database')); ?>">
	    <?php echo e(trans('messages.install')); ?>

	</a>
	</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>


<script type="text/javascript">
	$('.btn-primary').on('click',function(e){
		e.preventDefault();
		$('.fa-spin').removeClass('hidden');
	});
</script>
<?php echo $__env->make('vendor.installer.layouts.master', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>