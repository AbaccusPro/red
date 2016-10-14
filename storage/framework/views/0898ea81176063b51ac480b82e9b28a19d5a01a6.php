
<div class="timeline-cover-section">
	<div class="timeline-cover">
		<img src=" <?php if($timeline->cover_id): ?> <?php echo e(url('user/cover/'.$timeline->cover->source)); ?> <?php else: ?> <?php echo e(url('user/cover/default-cover-user.png')); ?> <?php endif; ?>" alt="<?php echo e($timeline->name); ?>" title="<?php echo e($timeline->name); ?>">
		<?php if($timeline->id == Auth::user()->timeline_id): ?>
			<a href="#" class="btn btn-camera-cover change-cover"><i class="fa fa-camera" aria-hidden="true"></i><span class="change-cover-text"><?php echo e(trans('common.change_cover')); ?></span></a>
		<?php endif; ?>
		<div class="user-cover-progress hidden">

		</div>
			<!-- <div class="cover-bottom">
		</div> -->
		<div class="user-timeline-name">
			<a href="<?php echo e(url($timeline->username)); ?>"><?php echo e($timeline->name); ?></a>
			<?php if($timeline->user->verified): ?>
				<span class="verified-badge bg-success">
					<i class="fa fa-check"></i>
				</span>
			<?php endif; ?>
		</div>
		</div>
	<div class="timeline-list">
		<ul class="list-inline pagelike-links">							
			<?php if($user_post == true): ?>
				<li class="<?php echo e(Request::segment(2) == 'posts' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/posts')); ?>" ><span class="top-list"><?php echo e(count($timeline->posts)); ?> <?php echo e(trans('common.posts')); ?></span></a></li>
			<?php else: ?>
				<li class="<?php echo e(Request::segment(2) == 'posts' ? 'active' : ''); ?>"><a href="#"><span class="top-list"><?php echo e(count($timeline->posts)); ?> <?php echo e(trans('common.posts')); ?></span></a></li>
			<?php endif; ?>

			<li class="<?php echo e(Request::segment(2) == 'following' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/following')); ?>" ><span class="top-list"><?php echo e($following_count); ?> <?php echo e(trans('common.following')); ?></span></a></li>
			<li class="<?php echo e(Request::segment(2) == 'followers' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/followers')); ?>" ><span class="top-list"><?php echo e($followers_count); ?>  <?php echo e(trans('common.followers')); ?></span></a></li>
			<li class="<?php echo e(Request::segment(2) == 'liked-pages' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/liked-pages')); ?>" ><span class="top-list"><?php echo e(count($user->pageLikes)); ?> <?php echo e(trans('common.liked_pages')); ?></span></a></li>
			<li class="<?php echo e(Request::segment(2) == 'joined-groups' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/joined-groups')); ?>" ><span class="top-list"><?php echo e($joined_groups_count); ?>  <?php echo e(trans('common.joined_groups')); ?></span></a></li>

			<?php if($follow_confirm == "yes" && $timeline->id == Auth::user()->timeline_id): ?>
				<li class="<?php echo e(Request::segment(2) == 'follow-requests' ? 'active' : ''); ?>"><a href="<?php echo e(url($timeline->username.'/follow-requests')); ?>" ><span class="top-list"><?php echo e(count($followRequests)); ?> <?php echo e(trans('common.follow_requests')); ?></span></a></li>
			<?php endif; ?>

			<?php if(Auth::user()->username != $timeline->username): ?>
				<li class="dropdown largescreen-report"><a href="#" class=" dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="top-list"> <i class="fa fa-ellipsis-h"></i></span></a>

					<ul class="dropdown-menu  report-dropdown">
						<?php if(!$timeline->reports->contains(Auth::user()->id)): ?>
						<li><a href="#" class="page-report report" data-timeline-id="<?php echo e($timeline->id); ?>"> <i class="fa fa-flag" aria-hidden="true"></i><?php echo e(trans('common.report')); ?></a></li>
						<li class="hidden"><a href="#" class="page-report reported" data-timeline-id="<?php echo e($timeline->id); ?>"> <i class="fa fa-flag" aria-hidden="true"></i><?php echo e(trans('common.reported')); ?></a></li>
						<?php else: ?>
						<li class="hidden"><a href="#" class="page-report report" data-timeline-id="<?php echo e($timeline->id); ?>"> <i class="fa fa-flag" aria-hidden="true"></i><?php echo e(trans('common.report')); ?></a></li>
						<li><a href="#" class="page-report reported" data-timeline-id="<?php echo e($timeline->id); ?>"> <i class="fa fa-flag" aria-hidden="true"></i><?php echo e(trans('common.reported')); ?></a></li>
						<?php endif; ?>
					</ul>
				</li>
				<?php if(!$timeline->reports->contains(Auth::user()->id)): ?>
					<li class="smallscreen-report"><a href="#" class="page-report report" data-timeline-id="<?php echo e($timeline->id); ?>"><?php echo e(trans('common.report')); ?></a></li>
					<li class="hidden smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="<?php echo e($timeline->id); ?>"><?php echo e(trans('common.reported')); ?></a></li>
				<?php else: ?>
					<li class="hidden smallscreen-report"><a href="#" class="page-report report" data-timeline-id="<?php echo e($timeline->id); ?>"><?php echo e(trans('common.report')); ?></a></li>
					<li class="smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="<?php echo e($timeline->id); ?>"><?php echo e(trans('common.reported')); ?></a></li>
				<?php endif; ?>
				<?php endif; ?>
			

			</ul>
			<div class="status-button">
					<a href="#" class="btn btn-status"><?php echo e(trans('common.status')); ?></a>
			</div>
			<div class="timeline-user-avtar">

				<img src="<?php echo e($timeline->user->avatar); ?>" alt="<?php echo e($timeline->name); ?>" title="<?php echo e($timeline->name); ?>">
				<?php if($timeline->id == Auth::user()->timeline_id): ?>
					<div class="chang-user-avatar">
						<a href="#" class="btn btn-camera change-avatar"><i class="fa fa-camera" aria-hidden="true"></i><span class="avatar-text"><?php echo e(trans('common.update_profile')); ?><span><?php echo e(trans('common.picture')); ?></span></span></a>
					</div>
				<?php endif; ?>			
				<div class="user-avatar-progress hidden">
				</div>
			</div><!-- /timeline-user-avatar -->

		</div><!-- /timeline-list -->
	</div><!-- timeline-cover-section -->


