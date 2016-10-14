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
			
							
			<?php if(Auth::guest()): ?>
			<ul class="nav navbar-nav navbar-right">
				<li class="logout">
					<a href="<?php echo e(url('/register')); ?>"><i class="fa fa-sign-in" aria-hidden="true"></i> <?php echo e(trans('common.join')); ?></a>
				</li>
			</ul>
			<?php else: ?>
			<ul class="nav navbar-nav navbar-right" id="navbar-right">
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
	