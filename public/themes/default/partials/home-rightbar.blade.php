<div class="right-side-section">

	<div class="panel panel-default">
		<div class="panel-body nopadding">
			<div class="mini-profile socialite">
				<div class="background">
					<div class="widget-bg">
						<img src=" @if(Auth::user()->cover) {{ url('user/cover/'.Auth::user()->cover) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
					</div>
					<div class="avatar-img">
						<img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
					</div>
				</div>
				<div class="avatar-profile">
					<div class="avatar-details">
						<h2 class="avatar-name">
							<a href="{{ url(Auth::user()->username) }}">
								{{ Auth::user()->name }}
							</a>
						</h2>
						<h4 class="avatar-mail">
							<a href="{{ url(Auth::user()->username) }}">
								{{ '@'.Auth::user()->username }}
							</a>
						</h4>
					</div>      
				</div>
				<ul class="activity-list list-inline">
					<li>
						<a href="{{ url(Auth::user()->username.'/posts') }}">
							<div class="activity-name">
								{{ trans('common.posts') }}
							</div>
							<div class="activity-count">
								{{ Auth::user()->posts->count() }}
							</div>
						</a>
					</li>
					<li>
						<a href="{{ url(Auth::user()->username.'/followers') }}">
							<div class="activity-name">
								{{ trans('common.followers') }} 
							</div>
							<div class="activity-count">
								{{ Auth::user()->followers->count() }}
							</div>
						</a>
					</li>
					<li>
						<a href="{{ url(Auth::user()->username.'/following') }}">
							<div class="activity-name">
								{{ trans('common.following') }}
							</div>
							<div class="activity-count">
								{{ Auth::user()->following->count() }}
							</div>
						</a>
					</li>
				</ul>
			</div><!-- /mini-profile -->							
		</div>
	</div><!-- /panel -->
	
	<div class="panel panel-default">
		<div class="panel-heading no-bg">
			<h3 class="panel-title">
				{{ trans('common.suggested_people') }}
			</h3>
		</div>
		<div class="panel-body">
			<!-- widget holder starts here -->
			<div class="user-follow socialite">
				<!-- Each user is represented with media block -->
				@if($suggested_users != "")

				@foreach($suggested_users as $suggested_user)

				<div class="media">
					<div class="media-left badge-verification">
						<a href="{{ url($suggested_user->username) }}">
							<img src="{{ $suggested_user->avatar }}" class="img-icon" alt="{{ $suggested_user->name }}" title="{{ $suggested_user->name }}">
							@if($suggested_user->verified)
							<span class="verified-badge bg-success verified-medium">
								<i class="fa fa-check"></i>
							</span>
							@endif
						</a>
					</div>
					<div class="media-body socialte-timeline follow-links">
						<h4 class="media-heading"><a href="{{ url($suggested_user->username) }}">{{ $suggested_user->name }} </a>
							<span class="text-muted">{{ '@'.$suggested_user->username }}</span>
						</h4>
							<div class="btn-follow">
								<a href="#" class="btn btn-default follow-user follow" data-timeline-id="{{ $suggested_user->timeline->id }}"> <i class="fa fa-heart"></i> {{ trans('common.follow') }}</a>
							</div>
							<div class="btn-follow hidden">
								<a href="#" class="btn btn-success follow-user unfollow" data-timeline-id="{{ $suggested_user->timeline->id }}"><i class="fa fa-check"></i> {{ trans('common.following') }}</a>
							</div>
						</div>
					</div>
					@endforeach
					@else
					<div class="alert alert-warning">
						{{ trans('messages.no_suggested_users') }}
					</div>
					@endif

				</div>
				<!-- widget holder ends here -->
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-heading no-bg">
				<h3 class="panel-title">
					{{ trans('common.suggested_groups') }}
				</h3>
			</div>
			<div class="panel-body">
				<div class="user-follow socialite">
					<!-- Each user is represented with media block -->
					@if($suggested_groups != "")

					@foreach($suggested_groups as $suggested_group)

					<div class="media">
						<div class="media-left badge-verification">
							<a href="{{ url($suggested_group->username) }}">
								@if($suggested_group->avatar != NULL)
								<img src="{{ url('group/avatar/'.$suggested_group->avatar) }}" class="img-icon" alt="{{ $suggested_group->name }}" title="{{ $suggested_group->name }}">
								@else
								<img src="{{ url('group/avatar/default-group-avatar.png') }}" class="img-icon" alt="{{ $suggested_group->name }}" title="{{ $suggested_group->name }}">
								@endif
							</a>
						</div>
						<div class="media-body socialte-timeline join-links">
							<h4 class="media-heading"><a href="{{ url($suggested_group->username) }}">{{ $suggested_group->name }} </a>
								<span class="text-muted">{{ '@'.$suggested_group->username }}</span></h4>
								
								@if(!$suggested_group->users->contains(Auth::user()->id))
									<div class="btn-follow">
										<a href="#" class="btn btn-options btn-block join-user btn-default join" data-timeline-id="{{ $suggested_group->timeline_id }}">
											<i class="fa fa-plus"></i> {{ trans('common.join') }}
										</a>
									</div>

									<div class="btn-follow hidden">
										<a href="#" class="btn btn-options btn-block btn-success join-user joined" data-timeline-id="{{ $suggested_group->timeline_id }}">
											<i class="fa fa-check"></i>  {{ trans('common.joined') }}
										</a>
									</div>
								@else
									<div class="btn-follow hidden">
										<a href="#" class="btn btn-options btn-block join-user btn-default join " data-timeline-id="{{ $suggested_group->timeline_id }}">
											<i class="fa fa-plus"></i>  {{ trans('common.join') }}
										</a>
									</div>
									
									<div class="btn-follow">
										<a href="#" class="btn btn-options btn-block btn-success join-user joined @if(count($suggested_group->admins()) == 1 && $suggested_group->is_admin(Auth::user()->id)) disabled @endif ">
											<i class="fa fa-check"></i>  {{ trans('common.joined') }}
										</a>
									</div>
								@endif	

							</div>
						</div>
						@endforeach
						@else
						<div class="alert alert-warning">
							{{ trans('messages.no_suggested_groups') }}
						</div>
						@endif

					</div><!-- Suggested groups widget -->
				</div>
			</div><!-- /panel -->

			<div class="panel panel-default">
			<div class="panel-heading no-bg">
				<h3 class="panel-title">
					{{ trans('common.suggested_pages') }}
				</h3>
			</div>
			<div class="panel-body">
				<div class="user-follow socialite">
					<!-- Each user is represented with media block -->
					@if($suggested_pages != "")

					@foreach($suggested_pages as $suggested_page)

					<div class="media">
						<div class="media-left badge-verification">
							<a href="{{ url($suggested_page->username) }}">
								@if($suggested_page->avatar != NULL)
									<img src="{{ url('page/avatar/'.$suggested_page->avatar) }}" class="img-icon" alt="{{ $suggested_page->name }}" title="{{ $suggested_page->name }}">
									@if($suggested_user->verified)
										<span class="verified-badge bg-success verified-medium">
											<i class="fa fa-check"></i>
										</span>
									@endif
								@else
									<img src="{{ url('page/avatar/default-page-avatar.png') }}" class="img-icon" alt="{{ $suggested_page->name }}" title="{{ $suggested_page->name }}">
									@if($suggested_page->verified)
										<span class="verified-badge bg-success verified-medium">
										<i class="fa fa-check"></i>
									</span>
									@endif
								@endif
							</a>
						</div>
						<div class="media-body socialte-timeline pagelike-links">
							<h4 class="media-heading"><a href="{{ url($suggested_page->username) }}">{{ $suggested_page->name }} </a>
								<span class="text-muted">{{ '@'.$suggested_page->username }}</span></h4>
								
								@if(!$suggested_page->likes->contains(Auth::user()->id))								
									<div class="btn-follow page"><a href="#" class="btn btn-options btn-block btn-default page-like like" data-timeline-id="{{ $suggested_page->timeline_id }}"><i class="fa fa-thumbs-up"></i> {{ trans('common.like') }}</a></div>
									<div class="btn-follow page hidden"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="{{ $suggested_page->timeline_id }}"><i class="fa fa-check"></i> {{ trans('common.liked') }}</a></div>
								@else
									<div class="btn-follow page hidden"><a href="#" class="btn btn-options btn-block btn-default page-like like " data-timeline-id="{{ $suggested_page->timeline_id }}"><i class="fa fa-thumbs-up"></i> {{ trans('common.like') }}</a></div>
									<div class="btn-follow page"><a href="#" class="btn btn-options btn-block btn-success page-like liked " data-timeline-id="{{ $suggested_page->timeline_id }}"><i class="fa fa-check"></i> {{ trans('common.liked') }}</a></div>			
								@endif	

							</div>
						</div>
						@endforeach
						@else
						<div class="alert alert-warning">
							{{ trans('messages.no_suggested_pages') }}
						</div>
						@endif

					</div><!-- Suggested groups widget -->
				</div>
			</div><!-- /panel -->

			@if(Setting::get('home_ad') != NULL)
			<div id="link_other" class="post-filters">
				{!! htmlspecialchars_decode(Setting::get('home_ad')) !!}
			</div>
			@endif
		</div>