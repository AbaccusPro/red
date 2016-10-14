<!-- main-section -->

	<div class="container">
		<div class="row">				 
			<div class="col-md-10">
				<?php if($timeline->type == "user"): ?>
				<?php echo Theme::partial('user-header',compact('user','timeline','liked_pages','joined_groups','followRequests','following_count','followers_count',
						'follow_confirm','user_post','joined_groups_count')); ?>

				<?php elseif($timeline->type == "page"): ?>
				<?php echo Theme::partial('page-header',compact('page','timeline')); ?>

				<?php else: ?>
				<?php echo Theme::partial('group-header',compact('timeline','group')); ?>

				<?php endif; ?>

				<div class="row">
					<div class="timeline">
						<div class="col-md-4">
							<?php if($timeline->type == "user"): ?>
							<?php echo Theme::partial('user-leftbar',compact('timeline','user','follow_user_status','own_pages','own_groups')); ?>

							<?php elseif($timeline->type == "page"): ?>
							<?php echo Theme::partial('page-leftbar',compact('timeline','page','page_members')); ?>

							<?php else: ?>
							<?php echo Theme::partial('group-leftbar',compact('timeline','group','group_members')); ?>

							<?php endif; ?>
						</div>

						<!-- Post box on timeline,page,group -->
						<div class="col-md-8">

							<?php if($timeline->type == "user" && $timeline_post == true): ?>
								<?php echo Theme::partial('create-post',compact('timeline')); ?>


							<?php elseif($timeline->type == "page"): ?>
								<?php if(($page->timeline_post_privacy == "only_admins" && $page->is_admin(Auth::user()->id)) || ($page->timeline_post_privacy == "everyone")): ?>
									<?php echo Theme::partial('create-post',compact('timeline')); ?>

								<?php elseif($page->timeline_post_privacy == "everyone"): ?>	
									<?php echo Theme::partial('create-post',compact('timeline')); ?>

								<?php endif; ?>
								
							<?php elseif($timeline->type == "group"): ?>						
								<?php if(($group->post_privacy == "only_admins" && $group->is_admin(Auth::user()->id))): ?>
								<?php echo Theme::partial('create-post',compact('timeline')); ?>

								<?php endif; ?>
							<?php endif; ?>					

							<div class="timeline-posts">
								<?php if(count($posts) > 0): ?>
									<?php if($user_post == true || $user_post == "page" || $user_post == "group"): ?>
	 									<?php foreach($posts as $post): ?>									
	 										<?php echo Theme::partial('post',compact('post','timeline','next_page_url','user')); ?>					
	 									<?php endforeach; ?>
	 								<?php endif; ?>
								<?php else: ?>
								<div class="no-posts alert alert-warning"><?php echo e(trans('messages.no_posts')); ?></div>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div><!-- /row -->
			</div><!-- /col-md-10 -->

			<div class="col-md-2">
				<?php echo Theme::partial('timeline-rightbar'); ?>

			</div>

		</div><!-- /row -->
	</div>
