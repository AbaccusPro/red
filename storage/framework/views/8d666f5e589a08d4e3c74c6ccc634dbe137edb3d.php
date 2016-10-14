<!-- main-section -->
	<!-- <div class="main-content"> -->
		<div class="container">
			<div class="row">
				<div class="visible-lg col-lg-2">
					<?php echo Theme::partial('home-leftbar',compact('trending_tags')); ?>

				</div>
              
                <div class="col-md-7 col-lg-6">
			   		<?php if(Session::has('message')): ?>
				        <div class="alert alert-<?php echo e(Session::get('status')); ?>" role="alert">
				            <?php echo Session::get('message'); ?>

				        </div>
				    <?php endif; ?>


					<?php if(isset($active_announcement)): ?>
						<div class="announcement alert alert-info">
							<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
							<h3><?php echo e($active_announcement->title); ?></h3>
							<p><?php echo e($active_announcement->description); ?></p>
						</div>
					<?php endif; ?>
					<?php echo Theme::partial('create-post',compact('timeline')); ?>


					<div class="timeline-posts">
						<?php if($posts->count() > 0): ?>
							<?php foreach($posts as $post): ?>
								<?php echo Theme::partial('post',compact('post','timeline','next_page_url')); ?>

							<?php endforeach; ?>
						<?php else: ?>
							<div class="no-posts alert alert-warning"><?php echo e(trans('common.no_posts')); ?></div>
						<?php endif; ?>
					</div>
				</div><!-- /col-md-6 -->

				<div class="col-md-5 col-lg-4">
					<?php echo Theme::partial('home-rightbar',compact('suggested_users', 'suggested_groups', 'suggested_pages')); ?>

				</div>
			</div>
		</div>
	<!-- </div> -->
<!-- /main-section -->