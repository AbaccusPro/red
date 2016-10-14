<div class="timeline-cover-section">
	<div class="timeline-cover">
		<img src=" @if($timeline->cover_id) {{ url('page/cover/'.$timeline->cover->source) }} @else {{ url('page/cover/default-cover-page.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}">
		@if($timeline->page->is_admin(Auth::user()->id) == true)
			<a href="#" class="btn btn-camera-cover change-cover"><i class="fa fa-camera" aria-hidden="true"></i><span class="change-cover-text">{{ trans('common.change_cover') }}</span></a>
		@endif
		<div class="user-cover-progress hidden">
			
		</div>
		<div class="user-timeline-name">		
			<a href="{{ url($timeline->username) }}">{{ $timeline->name }}</a>
			@if($timeline->page->verified)
				<span class="verified-badge bg-success">
					<i class="fa fa-check"></i>
				</span>
			@endif
		</div>
		
	</div>
	<div class="timeline-list">
		<ul class="list-inline pagelike-links">	

			@if(Auth::user()->get_page($page->id) != NULL)
			@if(($page->member_privacy == "only_admins" && $page->is_admin(Auth::user()->id)) || ($page->member_privacy == "members" && Auth::user()->get_page($page->id)->pivot->active == 1))		
			<li><a href="{{ url($timeline->username.'/add-pagemembers')}}" ><span class="top-list"> {{ trans('common.addmembers') }}</span></a></li>	
			@endif

			@endif

			<li class="{{ Request::segment(2) == 'pagemembers' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/pagemembers/')}}">
				<span class="top-list">
					{{ $page->members() != false ? count($page->members()) : 0 }} {{ trans('common.members') }}
				</span>
			</a>
		</li>
		
		<li class="{{ Request::segment(2) == 'pageadmin' ? 'active' : '' }}">
			<a href="{{ url($timeline->username.'/pageadmin/') }}">
				<span class="top-list">
					{{ $page->admins() != false ? count($page->admins()) : 0 }} {{ trans('common.admins') }}
				</span>
			</a>
		</li>	

		<li class="{{ Request::segment(2) == 'page-likes' ? 'active' : '' }}">
			<a href="{{ url($timeline->username.'/page-likes') }}">
				<span class="top-list">
					{{ $page->likes()->count() }} {{ trans('common.people_like_this') }}
				</span>
			</a>
		</li>
		
		<li class="{{ Request::segment(2) == 'page-posts' ? 'active' : '' }}"><a href="{{ url($timeline->username.'/page-posts') }}"><span class="top-list">{{ count($timeline->posts) }} {{ trans('common.posts') }}</span></a></li>
		@if(!$page->is_admin(Auth::user()->id))
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

		<img src=" @if($timeline->avatar_id) {{ url('page/avatar/'.$timeline->avatar->source) }} @else {{ url('page/avatar/default-page-avatar.png') }} @endif" alt="{{ $timeline->name }}" title="{{ $timeline->name }}" alt="{{ $timeline->name }}">			
		@if($timeline->page->is_admin(Auth::user()->id) == true)
			<div class="chang-user-avatar">
				<a href="#" class="btn btn-camera change-avatar"><i class="fa fa-camera" aria-hidden="true"></i><span class="avatar-text">{{ trans('common.update_profile') }}<span>{{ trans('common.picture') }}</span></span></a>
			</div>	
		@endif	
		
		<div class="user-avatar-progress hidden"></div>
	</div>
</div>
</div>
