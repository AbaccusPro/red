
<div class="timeline-cover-section">
	<div class="timeline-cover">
		<img src=" @if($timeline->cover_id) {{ url('user/cover/'.$timeline->cover->source) }} @else {{ url('user/cover/default-cover-user.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
		@if($timeline->id == Auth::user()->timeline_id)
			<a href="#" class="btn btn-camera-cover change-cover"><i class="fa fa-camera" aria-hidden="true"></i><span class="change-cover-text">{{ trans('common.change_cover') }}</span></a>
		@endif
		<div class="user-cover-progress hidden">

		</div>
			<!-- <div class="cover-bottom">
		</div> -->
		<div class="user-timeline-name">
			<a href="{{ url($timeline->username) }}">{{ $timeline->name }}</a>
			@if($timeline->user->verified)
				<span class="verified-badge bg-success">
					<i class="fa fa-check"></i>
				</span>
			@endif
		</div>
		</div>
	<div class="timeline-list">
		<ul class="list-inline pagelike-links">							
			@if($user_post == true)
				<li class="{{ Request::segment(2) == 'posts' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/posts') }}" ><span class="top-list">{{ count($timeline->posts) }} {{ trans('common.posts') }}</span></a></li>
			@else
				<li class="{{ Request::segment(2) == 'posts' ? 'active' : '' }}"><a href="#"><span class="top-list">{{ count($timeline->posts) }} {{ trans('common.posts') }}</span></a></li>
			@endif

			<li class="{{ Request::segment(2) == 'following' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/following') }}" ><span class="top-list">{{ $following_count }} {{ trans('common.following') }}</span></a></li>
			<li class="{{ Request::segment(2) == 'followers' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/followers') }}" ><span class="top-list">{{ $followers_count }}  {{ trans('common.followers') }}</span></a></li>
			<li class="{{ Request::segment(2) == 'liked-pages' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/liked-pages') }}" ><span class="top-list">{{ count($user->pageLikes) }} {{ trans('common.liked_pages') }}</span></a></li>
			<li class="{{ Request::segment(2) == 'joined-groups' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/joined-groups') }}" ><span class="top-list">{{ $joined_groups_count }}  {{ trans('common.joined_groups') }}</span></a></li>

			@if($follow_confirm == "yes" && $timeline->id == Auth::user()->timeline_id)
				<li class="{{ Request::segment(2) == 'follow-requests' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/follow-requests') }}" ><span class="top-list">{{count($followRequests)}} {{ trans('common.follow_requests') }}</span></a></li>
			@endif

			@if(Auth::user()->username != $timeline->username)
				<li class="dropdown largescreen-report"><a href="#" class=" dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="top-list"> <i class="fa fa-ellipsis-h"></i></span></a>

					<ul class="dropdown-menu  report-dropdown">
						@if(!$timeline->reports->contains(Auth::user()->id))
						<li><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>{{ trans('common.report') }}</a></li>
						<li class="hidden"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>{{ trans('common.reported') }}</a></li>
						@else
						<li class="hidden"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>{{ trans('common.report') }}</a></li>
						<li><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}"> <i class="fa fa-flag" aria-hidden="true"></i>{{ trans('common.reported') }}</a></li>
						@endif
					</ul>
				</li>
				@if(!$timeline->reports->contains(Auth::user()->id))
					<li class="smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>
					<li class="hidden smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>
				@else
					<li class="hidden smallscreen-report"><a href="#" class="page-report report" data-timeline-id="{{ $timeline->id }}">{{ trans('common.report') }}</a></li>
					<li class="smallscreen-report"><a href="#" class="page-report reported" data-timeline-id="{{ $timeline->id }}">{{ trans('common.reported') }}</a></li>
				@endif
				@endif
			

			</ul>
			<div class="status-button">
					<a href="#" class="btn btn-status">{{ trans('common.status') }}</a>
			</div>
			<div class="timeline-user-avtar">

				<img src="{{ $timeline->user->avatar }}" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
				@if($timeline->id == Auth::user()->timeline_id)
					<div class="chang-user-avatar">
						<a href="#" class="btn btn-camera change-avatar"><i class="fa fa-camera" aria-hidden="true"></i><span class="avatar-text">{{ trans('common.update_profile') }}<span>{{ trans('common.picture') }}</span></span></a>
					</div>
				@endif			
				<div class="user-avatar-progress hidden">
				</div>
			</div><!-- /timeline-user-avatar -->

		</div><!-- /timeline-list -->
	</div><!-- timeline-cover-section -->


