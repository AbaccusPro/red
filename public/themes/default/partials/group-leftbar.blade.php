
<div class="right-side-section">
	<div class="user-profile-buttons">
		<div class="row join-links pagelike-links">
			<!-- Start Open Group -->
			@if($group->is_admin(Auth::user()->id) && $group->type == "open")
				@if(!$group->users->contains(Auth::user()->id))
				<div class="col-md-6 col-xs-6 col-sm-6 left-col">
					<a href="#" class="btn btn-options btn-block join-user btn-default join" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-plus"></i> {{ trans('common.join') }}
					</a>
				</div>

				<div class="col-md-6 col-xs-6 col-sm-6 left-col hidden">
					<a href="#" class="btn btn-options btn-block btn-success join-user joined" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i>  {{ trans('common.joined') }}
					</a>
				</div>
				@else
				<div class="col-md-6 col-xs-6 col-sm-6 left-col hidden">
					<a href="#" class="btn btn-options btn-block join-user btn-default join " data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-plus"></i>  {{ trans('common.join') }}
					</a>
				</div>
				
				<div class="col-md-6 col-xs-6 col-sm-6 left-col">
					<a href="#" class="btn btn-options btn-block btn-success join-user joined @if(count($group->admins()) == 1 && $group->is_admin(Auth::user()->id)) disabled @endif ">
						<i class="fa fa-check"></i>  {{ trans('common.joined') }}
					</a>
				</div>
				@endif	
				<div class="col-md-6 col-xs-6 col-sm-6 right-col">

					<a href="{{ url('/'.$timeline->username.'/group-settings/general') }}" class="btn btn-options btn-block btn-default">

						<i class="fa fa-gear"></i>  {{ trans('common.settings') }}
					</a>
				</div>
			@else
			@if(!$group->is_admin(Auth::user()->id) && $group->type == "open")
			@if(!$group->users->contains(Auth::user()->id))
				<div class="col-md-12 page">
					<a href="#" class="btn btn-options btn-block join-user btn-default join" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-plus"></i> {{ trans('common.join') }}
					</a>
				</div>
				<div class="col-md-12 col-xs-12 col-sm-12 page  hidden">
					<a href="#" class="btn btn-options btn-block btn-success join-user joined" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i> {{ trans('common.joined') }}
					</a>
				</div>
			@else
				<div class="col-md-12 col-xs-12 col-sm-12 page hidden">
					<a href="#" class="btn btn-options btn-block join-user btn-default join" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-plus"></i> {{ trans('common.join') }}
					</a>
				</div>
				<div class="col-md-12 col-xs-12 col-sm-12 page">
					<a href="#" class="btn btn-options btn-block btn-success join-user joined @if(count($group->admins()) == 1 && $group->is_admin(Auth::user()->id)) disabled @endif " data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i> {{ trans('common.joined') }}
					</a>
				</div>
			@endif			
			@endif					
			@endif
			<!-- End open group -->

			<!-- Start closed group -->
			@if($group->is_admin(Auth::user()->id) && $group->type == "closed")
			@if(!$group->users->contains(Auth::user()->id))


			<div class="col-md-12 col-xs-12 col-sm-12 ">
				<a href="#" class="btn btn-options btn-block join-close-group btn-default join" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-plus"></i> {{ trans('common.join') }}
				</a>
			</div>

			<div class="col-md-12 col-xs-12 col-sm-12 hidden">
				<a href="#" class="btn btn-options btn-block btn-warning join-close-group joinrequest" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i> {{ trans('common.join_requested') }}
				</a>
			</div>

			@else
			<div class="col-md-12 col-xs-12 col-sm-12 hidden">
				<a href="#" class="btn btn-options btn-block join-close-group btn-default join" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-plus"></i> {{ trans('common.join') }}
				</a>
			</div>

			@if(Auth::user()->get_group($group->id)->pivot->status == "pending")
			<div class="col-md-12 col-xs-12 col-sm-12">
				<a href="#" class="btn btn-options btn-block btn-warning join-close-group joinrequest" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i> {{ trans('common.join_requested') }}
				</a>
			</div>							
			@endif
			
			@if(Auth::user()->get_group($group->id)->pivot->status == "approved")
			<div class="col-md-6 col-xs-6 col-sm-6 left-col">
				<a href="#" class="btn btn-options btn-block btn-success join-close-group joined @if(count($group->admins()) == 1 && $group->is_admin(Auth::user()->id)) disabled @endif " data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i> {{ trans('common.joined') }}
				</a>
			</div>
			@endif

			@endif

			<div class="col-md-6 col-xs-6 col-sm-6 right-col">

				<a href="{{ url('/'.$timeline->username.'/group-settings/closegroup') }}" class="btn btn-options btn-block btn-default">

					<i class="fa fa-gear"></i> {{ trans('common.settings') }}
				</a>
			</div>

			@else
			@if(!$group->is_admin(Auth::user()->id) && $group->type == "closed")

			@if(!$group->users->contains(Auth::user()->id))
			<div class="col-md-12 col-xs-12 col-sm-12 page">
				<a href="#" class="btn btn-options btn-block join-close-group btn-default join" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-plus"></i>   {{ trans('common.join') }}
				</a>
			</div>

			<div class="col-md-12 col-xs-12 col-sm-12 page hidden">
				<a href="#" class="btn btn-options btn-block btn-success join-close-group joinrequest" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i>   {{ trans('common.join_requested') }}
				</a>
			</div>

			@else
			<div class="col-md-12 col-xs-12 col-sm-12 page hidden">
				<a href="#" class="btn btn-options btn-block join-close-group btn-default join" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-plus"></i>  {{ trans('common.join') }}
				</a>
			</div>
			@if(Auth::user()->get_group($group->id)->pivot->status == "pending")
			<div class="col-md-12 col-xs-12 col-sm-12 page">
				<a href="#" class="btn btn-options btn-block btn-success join-close-group joinrequest" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i> {{ trans('common.join_requested') }}
				</a>
			</div>
			@endif

			@if(Auth::user()->get_group($group->id)->pivot->status == "approved")								
				<div class="col-md-12 col-xs-12 col-sm-12 page">
					<a href="#" class="btn btn-options btn-block btn-success join-close-group joined @if(count($group->admins()) == 1 && $group->is_admin(Auth::user()->id)) disabled @endif " data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i> {{ trans('common.joined') }}
					</a>
				</div>
			@endif
		@endif			
	@endif	
@endif
			<!-- End closed group -->

	<!-- Start secret Group -->
	@if($group->is_admin(Auth::user()->id) && $group->type == "secret")			
		<div class="col-md-12 col-xs-12 col-sm-12">

			<a href="{{ url('/'.$timeline->username.'/group-settings/general') }}" class="btn btn-options btn-block btn-default">

				<i class="fa fa-gear"></i>  {{ trans('common.settings') }}
			</a>
		</div>					
	@endif
	<!-- End secret group -->
		

		</div>
	</div>

	<!-- Change avatar form -->
	<form class="change-avatar-form hidden" action="{{ url('ajax/change-avatar') }}" method="post" enctype="multipart/form-data">
		<input name="timeline_id" value="{{ $timeline->id }}" type="hidden">
		<input name="timeline_type" value="{{ $timeline->type }}" type="hidden">
		<input class="change-avatar-input hidden" accept="image/jpeg,image/png" type="file" name="change_avatar" >
	</form>

	<!-- Change cover form -->
	<form class="change-cover-form hidden" action="{{ url('ajax/change-cover') }}" method="post" enctype="multipart/form-data">
		<input name="timeline_id" value="{{ $timeline->id }}" type="hidden">
		<input name="timeline_type" value="{{ $timeline->type }}" type="hidden">
		<input class="change-cover-input hidden" accept="image/jpeg,image/png" type="file" name="change_cover" >
	</form>

	<div class="user-bio-block">
		<div class="bio-header">{{ trans('common.about') }}</div>
		<div class="bio-description">
			{{ ($timeline['about'] != NULL) ? $timeline['about'] : trans('messages.no_description') }}
		</div>
		<ul class="bio-list list-unstyled">
			<li>
				@if($group->type == 'open')
				<i class="fa fa-unlock" aria-hidden="true"></i>
				@else
				<i class="fa fa-lock" aria-hidden="true"></i>
				@endif
				<span>{{ $group->type.' '.trans('common.group') }}</span>
			</li>
		</ul>
	</div>
	<div class="widget-pictures widget-best-pictures"><!-- /pages-liked -->
	<div class="picture pull-left">
		{{ trans('common.members') }}
	</div>
	@if(count($group_members) > 0)
		<div class="pull-right show-all">
			<a href="{{ url($timeline->username.'/members/'.$group->id) }}">{{ trans('common.show_all') }}</a>
		</div>
	@endif
	<div class="clearfix"></div>
	<div class="best-pictures my-best-pictures">
		<div class="row">
			@if(count($group_members) > 0)
				@foreach($group_members->take(12) as $group_member)
				<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
					<a href="{{ url($group_member->username) }}" class="image-hover" data-toggle="tooltip" data-placement="top" title="{{ $group_member->name }}" >
						<img src="{{ $group_member->avatar }}" alt="{{ $group_member->name }}" title="{{ $group_member->name }}">
					</a>
				</div>
				@endforeach
			@else
				<div class="alert alert-warning">{{ trans('messages.no_members') }}</div>
			@endif
		</div>
	</div>
</div> <!-- /pages-liked -->
</div>

