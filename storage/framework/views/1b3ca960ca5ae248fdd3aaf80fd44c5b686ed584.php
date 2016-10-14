<nav class="navbar socialite navbar-default no-bg">
	<div class="container-fluid">
		<!-- Brand and toggle get grouped for better mobile display -->
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-4" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<a class="navbar-brand socialite" href="<?php echo e(url('/')); ?>">
				<img class="socialite-logo" src="<?php echo url('setting/'.Setting::get('logo')); ?>" alt="<?php echo e(Setting::get('site_name')); ?>" title="<?php echo e(Setting::get('site_name')); ?>">
			</a>
		</div>
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-4">
			<form class="navbar-form navbar-left form-left" role="search">
				<div class="input-group no-margin">
					<span class="input-group-btn">
						<button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
					</span>
					<input type="text" id="navbar-search" data-url="<?php echo e(URL::to('api/v1/timelines')); ?>" class="form-control" placeholder="<?php echo e(trans('messages.search_placeholder')); ?>">
				</div><!-- /input-group -->
			</form>
			<!-- Collect the nav links, forms, and other content for toggling -->
			
			<?php if(Auth::guest()): ?>
			<ul class="nav navbar-nav navbar-right">
				<li class="logout">
					<a href="<?php echo e(url('/login')); ?>"><i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo e(trans('common.join')); ?></a>
				</li>
			</ul>
			<?php else: ?>
			<ul class="nav navbar-nav navbar-right" id="navbar-right" v-cloak>
				<li>
					<ul class="list-inline notification-list">
						<li class="dropdown message notification">
							<a href="#" data-toggle="dropdown" @click.prevent="showNotifications" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
								<i class="fa fa-bell" aria-hidden="true">
									<?php if(Auth::user()->notifications()->where('seen',0)->count() > 0): ?>
									<span class="count hidden"><?php echo e(Auth::user()->notifications()->where('seen',0)->count()); ?></span>
									<span class="count" v-if="unreadNotifications > 0" >{{ unreadNotifications }}</span>
									<?php endif; ?>
								</i>
								<span class="small-screen"><?php echo e(trans('common.notifications')); ?></span>
							</a>
							<div class="dropdown-menu">
								<div class="dropdown-menu-header">
									<span class="pull-left"><?php echo e(trans('common.notifications')); ?></span>
									<a v-if="unreadNotifications > 0" class="pull-right" href="#" @click.prevent="markNotificationsRead" ><?php echo e(trans('messages.mark_all_read')); ?></a>
									<div class="clearfix"></div>
								</div>
								<?php if(Auth::user()->notifications()->count() > 0): ?>
								<ul class="list-unstyled dropdown-messages-list scrollable" data-type="notifications">
									<li class="inbox-message"  v-bind:class="[ !notification.seen ? 'active' : '' ]" v-for="notification in notifications.data">
										<a href="<?php echo e(url(Auth::user()->username.'/notification/')); ?>/{{ notification.id }}">
											<div class="media">
												<div class="media-left">
													<img class="media-object img-icon" v-bind:src="notification.notified_from.avatar" alt="images">
												</div>
												<div class="media-body">
													<h4 class="media-heading">
														<span class="notification-text"> {{ notification.description }} </span>
														<span class="message-time">
															<span class="notification-type"><i class="fa fa-user" aria-hidden="true"></i></span>
															<time class="timeago" datetime="{{ notification.created_at }}" title="{{ notification.created_at }}">
																{{ notification.created_at }}
															</time>
														</span>
													</h4>
												</div>
											</div>
										</a>
									</li>
									<li v-if="notificationsLoading" class="dropdown-loading">
										<i class="fa fa-spin fa-spinner"></i>
									</li>
								</ul>
								<?php else: ?>
								<div class="no-messages">
									<i class="fa fa-bell-slash-o" aria-hidden="true"></i>
									<p><?php echo e(trans('messages.no_notifications')); ?></p>
								</div>
								<?php endif; ?>
								<div class="dropdown-menu-footer"><br>
									
								</div>
							</div>
						</li>
						<li class="dropdown message largescreen-message">
							<a href="#" data-toggle="dropdown" @click="showConversations" class="dropdown-toggle" role="button" aria-haspopup="true" aria-expanded="false">
								<i class="fa fa-comments" aria-hidden="true">
									<span class="count" v-if="unreadConversations" >{{ unreadConversations }}</span>
								</i>
								<span class="small-screen"><?php echo e(trans('common.messages')); ?></span>
							</a>
							<div class="dropdown-menu">
								<div class="dropdown-menu-header">
									<span class="pull-left"><?php echo e(trans('common.messages')); ?></span>
									<div class="clearfix"></div>
								</div>
								<div class="no-messages hidden">
									<i class="fa fa-commenting-o" aria-hidden="true"></i>
									<p><?php echo e(trans('messages.no_messages')); ?></p>
								</div>
								<ul class="list-unstyled dropdown-messages-list scrollable" data-type="messages">
									<li class="inbox-message" v-for="conversation in conversations.data">
										<a href="#" onclick="chatBoxes.sendMessage({{ conversation.user.id }})">
											<div class="media">
												<div class="media-left">
													<img class="media-object img-icon" v-bind:src="conversation.user.avatar" alt="images">
												</div>
												<div class="media-body">
													<h4 class="media-heading">
														<span class="message-heading">{{ conversation.user.name }}</span> 
														<span class="online-status hidden"></span>
														<time class="timeago message-time" datetime="{{ conversation.lastMessage.created_at }}" title="{{ conversation.lastMessage.created_at }}">
															{{ conversation.lastMessage.created_at }}
														</h4>
														<p class="message-text">
															{{ conversation.lastMessage.body }}
														</p>
													</div>
												</div>
											</a>
										</li>
										<li v-if="conversationsLoading" class="dropdown-loading">
											<i class="fa fa-spin fa-spinner"></i>
										</li>
									</ul>
									<div class="dropdown-menu-footer">
										<a href="<?php echo e(url('messages')); ?>"><?php echo e(trans('common.see_all')); ?></a>
									</div>
								</div>
							</li>
							<li class="smallscreen-message">
								<a href="<?php echo e(url('messages')); ?>">
									<i class="fa fa-comments" aria-hidden="true">
										<span class="count" v-if="unreadConversations" >{{ unreadConversations }}</span>
									</i>
									<span class="small-screen"><?php echo e(trans('common.messages')); ?></span>
								</a>
							</li>
							<li class="chat-list-toggle">
								<a href="#"><i class="fa fa-users" aria-hidden="true"></i><span class="small-screen">chat-list</span></a>
							</li>
						</ul>
					</li>
					<li class="dropdown user-image socialite">
						<a href="<?php echo e(url(Auth::user()->username)); ?>" class="dropdown-toggle no-padding" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
							<img src="<?php echo e(Auth::user()->avatar); ?>" alt="<?php echo e(Auth::user()->name); ?>" class="img-radius img-30" title="<?php echo e(Auth::user()->name); ?>">

							<span class="user-name"><?php echo e(Auth::user()->name); ?></span><i class="fa fa-angle-down" aria-hidden="true"></i></a>
							<ul class="dropdown-menu">
								<?php if(Auth::user()->hasRole('admin')): ?>
								<li class="<?php echo e(Request::segment(1) == 'admin' ? 'active' : ''); ?>"><a href="<?php echo e(url('admin')); ?>"><i class="fa fa-user-secret" aria-hidden="true"></i><?php echo e(trans('common.admin')); ?></a></li>
								<?php endif; ?>
								<li class="<?php echo e((Request::segment(1) == Auth::user()->username && Request::segment(2) == '') ? 'active' : ''); ?>"><a href="<?php echo e(url(Auth::user()->username)); ?>"><i class="fa fa-user" aria-hidden="true"></i><?php echo e(trans('common.my_profile')); ?></a></li>

								<li class="<?php echo e(Request::segment(2) == 'pages-groups' ? 'active' : ''); ?>"><a href="<?php echo e(url(Auth::user()->username.'/pages-groups')); ?>"><i class="fa fa-bars" aria-hidden="true"></i><?php echo e(trans('common.my_pages_groups')); ?></a></li>

								<li class="<?php echo e(Request::segment(3) == 'general' ? 'active' : ''); ?>"><a href="<?php echo e(url('/'.Auth::user()->username.'/settings/general')); ?>"><i class="fa fa-cog" aria-hidden="true"></i><?php echo e(trans('common.settings')); ?></a></li>

								<li><a href="<?php echo e(url('/logout')); ?>"><i class="fa fa-unlock" aria-hidden="true"></i><?php echo e(trans('common.logout')); ?></a></li>
							</ul>
						</li>
	               <!--  <li class="logout">
	                    <a href="<?php echo e(url('/logout')); ?>"><i class="fa fa-sign-out" aria-hidden="true"></i></a>
	                </li> -->
	            </ul>
	            <?php endif; ?>
	        </div><!-- /.navbar-collapse -->
	    </div><!-- /.container-fluid -->
	</nav>	


	<?php echo Theme::asset()->container('footer')->usePath()->add('notifications', 'js/notifications.js'); ?>


