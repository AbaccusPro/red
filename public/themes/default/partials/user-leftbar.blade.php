<div class="user-profile-buttons">
	<div class="row follow-links pagelike-links">
		<!-- This [if-1] is for checking current user timeline or diff user timeline -->	
		@if(Auth::user()->username != $timeline->username)
		<?php 
					//php code is for checking user's follow_privacy settings
		$user_follow ="";
		$confirm_follow ="";						
		$othersSettings = $user->getOthersSettings($timeline->username);
		if($othersSettings)
		{
						//follow_privacy checking
			if ($othersSettings->follow_privacy == "only_follow") {
				$user_follow = "only_follow";
			}elseif ($othersSettings->follow_privacy == "everyone") {
				$user_follow = "everyone";
			}

						//confirm_follow checking
			if ($othersSettings->confirm_follow == "yes") {
				$confirm_follow = "yes";
			}elseif ($othersSettings->confirm_follow == "no") {
				$confirm_follow = "no";
			}
		}

		?>
		<!-- This [if-2] is for checking usersettings follow_privacy showing follow/following || message button -->
		@if($confirm_follow == "no")

		<!-- This [if-3] is for checking usersettings follow_privacy showing follow/following || message button -->
		@if(($user->followers->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone"))

		@if(!$user->followers->contains(Auth::user()->id))

			<div class="col-md-6 col-sm-6 col-xs-6 left-col">
				<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-heart"></i> {{ trans('common.follow') }}
				</a>
			</div>

			<div class="col-md-6 col-sm-6 col-xs-6 hidden">
				<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-check"></i> {{ trans('common.following') }}
				</a>
			</div>
		@else

			<div class="col-md-6 col-sm-6 col-xs-6 hidden">
				<a href="#" class="btn btn-options btn-block follow-user btn-default follow " data-timeline-id="{{ $timeline->id }}">
					<i class="fa fa-heart"></i> {{ trans('common.follow') }}
				</a>
			</div>

			<div class="col-md-6 col-sm-6 col-xs-6 left-col">
				<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="{{ $timeline->id }}">	<i class="fa fa-check"></i> {{ trans('common.following') }}
				</a>
			</div>
		@endif
		@elseif(($user->following->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone"))

			@if(!$user->followers->contains(Auth::user()->id))

				<div class="col-md-6 col-sm-6 col-xs-6 left-col">
					<a href="#" class="btn btn-options btn-block follow-user btn-default follow" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-heart"></i> {{ trans('common.follow') }}
					</a>
				</div>

				<div class="col-md-6 col-sm-6 col-xs-6 hidden">
					<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-check"></i> {{ trans('common.following') }}
					</a>
				</div>
			@else							
				<div class="col-md-6 col-sm-6 col-xs-6 hidden">
					<a href="#" class="btn btn-options btn-block follow-user btn-default follow " data-timeline-id="{{ $timeline->id }}">
						<i class="fa fa-heart"></i> {{ trans('common.follow') }}
					</a>
				</div>

				<div class="col-md-6 col-sm-6 col-xs-6 left-col">
					<a href="#" class="btn btn-options btn-block follow-user btn-success unfollow" data-timeline-id="{{ $timeline->id }}">	<i class="fa fa-heart"></i> {{ trans('common.following') }}
					</a>
				</div>
			@endif
		@endif	<!-- End of [if-3]-->

		@elseif($confirm_follow == "yes")
		<!-- This [if-4] is for checking usersettings follow_privacy showing follow/following || message button -->
		@if(($user->followers->contains(Auth::user()->id) && $user_follow == "only_follow") || ($user_follow == "everyone"))
		@if(!$user->followers->contains(Auth::user()->id))
			<div class="col-md-6 col-sm-6 col-xs-6 left-col">
				<a href="#" class="btn btn-options btn-block btn-default follow-user-confirm follow" data-timeline-id="{{ $timeline->id }}-{{ $follow_user_status }}">
					<i class="fa fa-heart"></i> {{ trans('common.follow') }}
				</a>
			</div>

			<div class="col-md-6 col-sm-6 col-xs-6 hidden">
				<a href="#" class="btn btn-options btn-block follow-user-confirm btn-warning followrequest" data-timeline-id="{{ $timeline->id }}-{{ $follow_user_status }}">
					<i class="fa fa-check"></i> {{ trans('common.requested') }}
				</a>
			</div>
		@else
			<div class="col-md-6 col-sm-6 col-xs-6 hidden">
				<a href="#" class="btn btn-options btn-block btn-default follow-user-confirm  follow " data-timeline-id="{{ $timeline->id }}-{{ $follow_user_status }}">
					<i class="fa fa-heart"></i> {{ trans('common.follow') }}
				</a>
			</div>

		@if($follow_user_status == "pending")
		<div class="col-md-6 col-sm-6 col-xs-6 left-col">
			<a href="#" class="btn btn-options btn-block follow-user-confirm btn-warning followrequest" data-timeline-id="{{ $timeline->id }}-{{ $follow_user_status }}">
				<i class="fa fa-check"></i> {{ trans('common.requested') }}
			</a>
		</div>
		@endif
		@if($follow_user_status == "approved")
		<div class="col-md-6 col-sm-6 col-xs-6 left-col">
			<a href="#" class="btn btn-options btn-block follow-user-confirm btn-primary unfollow" data-timeline-id="{{ $timeline->id }}-{{ $follow_user_status }}">
				<i class="fa fa-check"></i> {{ trans('common.following') }}
			</a>
		</div>
		@endif
		@endif
		@endif	<!-- End of [if-4]-->
		@endif	<!-- End of [if-2]-->

		<div class="col-md-6 col-sm-6 col-xs-6 right-col">
			<a href="#" class="btn btn-options btn-block btn-default" onClick="chatBoxes.sendMessage({{ $timeline->user->id }})">
				<i class="fa fa-inbox"></i> {{ trans('common.message') }}
			</a>
		</div>
		@else
		<div class="col-md-12"><a href="{{ url('/'.Auth::user()->username.'/settings/general') }}" class="btn btn-profile"><i class="fa fa-pencil-square-o"></i>{{ trans('common.edit_profile') }}</a></div>
		@endif <!-- End of [if-1]-->

	</div>
</div>

<div class="user-bio-block">
	<div class="bio-header">{{ trans('common.bio') }}</div>
	<div class="bio-description">
		{{ ($timeline['about'] != NULL) ? $timeline['about'] : trans('messages.no_description') }}
	</div>
	<ul class="bio-list list-unstyled">
		@if($user->country != NULL)
		<li>
			<i class="fa fa-map-marker" aria-hidden="true"></i><span>{{ trans('common.lives_in').' '.$user->country }}</span>
		</li>
		@endif

		@if($user->city != NULL)
		<li><i class="fa fa-building-o"></i><span>{{ trans('common.from').' '.$user->city }}</span></li>
		@endif

		@if($user->birthday != '0000-00-00')
		<li><i class="fa fa-calendar"></i><span>

			{{ trans('common.born_on').' '.date('F d', strtotime($user->birthday)) }}

		</span></li>
		@endif
	</ul>
	<ul class="list-inline list-unstyled social-links-list">
		@if($user->facebook_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->facebook_link }}" class="btn btn-facebook"><i class="fa fa-facebook"></i></a>
			</li>
		@endif
		@if($user->twitter_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->twitter_link }}" class="btn btn-twitter"><i class="fa fa-twitter"></i></a>
			</li>
		@endif
		@if($user->dribbble_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->dribbble_link }}" class="btn btn-dribbble"><i class="fa fa-dribbble"></i></a>
			</li>
		@endif
		@if($user->youtube_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->youtube_link }}" class="btn btn-youtube"><i class="fa fa-youtube"></i></a>
			</li>
		@endif
		@if($user->instagram_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->instagram_link }}" class="btn btn-instagram"><i class="fa fa-instagram"></i></a>
			</li>
		@endif
		@if($user->linkedin_link != NULL)
			<li>
				<a target="_blank" href="{{ $user->linkedin_link }}" class="btn btn-linkedin"><i class="fa fa-linkedin"></i></a>
			</li>
		@endif
	</ul>
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

	<!-- my-pages -->
	<div class="widget-pictures widget-best-pictures">
		<div class="picture pull-left">
			{{ trans('common.pages') }}
		</div>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures">
			<div class="row">
				@if(count($own_pages) > 0)
				@foreach($own_pages as $own_page)
				<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
					<a href="{{ url($own_page->username) }}" class="image-hover" title="{{ $own_page->name }}" data-toggle="tooltip" data-placement="top">
						<img src="@if($own_page->avatar != NULL) {{ url('page/avatar/'.$own_page->avatar) }} @else {{ url('page/avatar/default-page-avatar.png')}} @endif" alt="{{ $own_page->name }}" title="{{ $own_page->name }}">
					</a>
				</div>
				@endforeach
				@else
				<div class="alert alert-warning">{{ trans('messages.no_pages') }}</div>
				@endif
			</div><!-- /row -->
		</div>
	</div>
	<!-- /my pages -->

	<!-- my-groups -->
	<div class="widget-pictures widget-best-pictures">

		<div class="picture pull-left">
			{{ trans('common.groups') }}
		</div>
		<div class="clearfix"></div>
		<div class="best-pictures my-best-pictures">
			<div class="row">
				@if(count($own_groups) > 0)

				@foreach($own_groups as $own_group)
				<div class="col-md-2 col-sm-2 col-xs-2 best-pics">
					<a href="{{ url($own_group->username) }}" class="image-hover" title="{{ $own_group->name }}" data-toggle="tooltip" data-placement="top">
						<img src=" @if($own_group->avatar != NULL) {{ url('group/avatar/'.$own_group->avatar) }} @else {{ url('group/avatar/default-group-avatar.png') }} @endif " alt="{{ $own_group->name }}" title="{{ $own_group->name }}" >
					</a>
				</div>
				@endforeach
				@else
				<div class="alert alert-warning">{{ trans('messages.no_groups') }}</div>

			@endif

		</div><!-- /row -->

		</div>
	</div>
	<!-- /my pages -->
	@if(Setting::get('timeline_ad') != NULL)
	<div id="link_other" class="post-filters">
		{!! htmlspecialchars_decode(Setting::get('timeline_ad')) !!} 
	</div>	
	@endif
















