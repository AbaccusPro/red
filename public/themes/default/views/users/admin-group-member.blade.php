<!-- main-section -->

<div class="container">
	<div class="row">
		<div class="col-md-10">

			{!! Theme::partial('group-header',compact('timeline','group')) !!}

			<div class="row">
				<div class="timeline">
					<div class="col-md-4">
						
						{!! Theme::partial('group-leftbar',compact('timeline','group','group_members')) !!}
					</div>

					<div class="col-md-8">						

						<div class="panel panel-default">
							<div class="panel-heading no-bg panel-settings">
								<h3 class="panel-title">
									{{ trans('common.admins') }}
								</h3>
							</div>
							<div class="panel-body">

							@if(count($group_admins) > 0)
								<ul class="list-group page-likes">
									@foreach($group_admins as $group_admin)

									<li class="list-group-item holder">
										<div class="connect-list">
											<div class="connect-link pull-left">
												<a href="{{ url(url($group_admin->username)) }}">
													<img src="{{ $group_admin->avatar }}" alt="{{ $group_admin->name }}" class="img-icon img-30" title="{{ $group_admin->name }}">
													{{ $group_admin->name }}
												</a>
											</div>										
											
												@if($group->is_admin($group_admin->pivot->user_id) && $group->is_admin(Auth::user()->id))
												<div class="pull-right follow-links">
												
												<form class="margin-right" method="POST" action="{{ url('/member/update-role/') }}">
													{{ csrf_field() }}
													@if(count($group_admins) > 1)
														{{ Form::hidden('user_id', $group_admin->id) }}
														{{ Form::hidden('group_id', $group_id) }}

														<div class="col-md-5 col-sm-5 col-xs-5 padding-5">
															{{ Form::select('member_role', $member_role_options, $group_admin->pivot->role_id , array('class' => 'form-control')) }}
														</div>
														<div class="col-md-3 col-sm-3 col-xs-3 padding-5">
															{{ Form::submit(trans('common.assign'), array('class' => 'btn btn-to-follow btn-default')) }}
														</div>
														<div class="left-col col-md-4 col-sm-4 col-xs-4 padding-5">
															<a href="#" class="btn btn-to-follow btn-default remove-member remove" data-user-id="{{ $group_admin->id }} - {{ $group_id }}">
																<i class="fa fa-trash"></i> {{ trans('common.remove') }} 
															</a>
														</div>
													@endif
												</form>											
													
												</div>
												
											@endif
											<div class="clearfix"></div>
										</div>
									</li>
									@endforeach
								</ul>
							@else
								<div class="alert alert-warning">{{ trans('messages.no_admins') }}</div>
							@endif
							</div><!-- /panel-body -->
						</div>

					</div><!-- /col-md-8 -->
				</div><!-- /main-content -->
			</div><!-- /row -->
		</div><!-- /col-md-10 -->

		<div class="col-md-2">
			{!! Theme::partial('timeline-rightbar') !!}
		</div>
	</div>
</div><!-- /container -->
