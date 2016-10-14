<!-- main-section -->
<!-- <div class="main-content"> -->
	<div class="container">
		<div class="row">
			<div class="col-md-4">
				<div class="post-filters">
					{!! Theme::partial('usermenu-settings') !!}
				</div>
			</div>
			<div class="col-md-8">
				<div class="panel panel-default">
				
					<div class="panel-heading no-bg panel-settings">
					@include('flash::message')
						<h3 class="panel-title">
							{{ trans('common.general_settings') }}
						</h3>
					</div>
					<div class="panel-body nopadding">
						<div class="socialite-form">							
							<form method="POST" action="{{ url('/'.$username.'/settings/general/') }}">
								{{ csrf_field() }}
								<div class="row">
									<div class="col-md-6">

										<fieldset class="form-group required {{ $errors->has('username') ? ' has-error' : '' }}">
											{{ Form::label('username', trans('common.username')) }}
											{{ Form::text('new_username', Auth::user()->username, ['class' => 'form-control', 'placeholder' => trans('common.username')]) }}
											@if ($errors->has('username'))
											<span class="help-block">
												<strong>{{ $errors->first('username') }}</strong>
											</span>
											@endif
										</fieldset>
										
									</div>
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
											{{ Form::label('name', trans('common.fullname')) }}
											{{ Form::text('name', Auth::user()->name, ['class' => 'form-control', 'placeholder' => trans('common.fullname')]) }}
											@if ($errors->has('name'))
											<span class="help-block">
												<strong>{{ $errors->first('name') }}</strong>
											</span>
											@endif
										</fieldset>
									</div>
								</div>
								<fieldset class="form-group">
									{{ Form::label('about', trans('common.about')) }}
									{{ Form::textarea('about', Auth::user()->about, ['class' => 'form-control', 'placeholder' => trans('messages.about_user_placeholder')]) }}
								</fieldset>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required {{ $errors->has('email') ? ' has-error' : '' }}">
											{{ Form::label('email', trans('auth.email_address')) }}
											{{ Form::email('email', Auth::user()->email, ['class' => 'form-control', 'placeholder' => trans('auth.email_address')]) }}
											@if ($errors->has('email'))
											<span class="help-block">
												<strong>{{ $errors->first('email') }}</strong>
											</span>
											@endif
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											{{ Form::label('birthday', trans('common.birthday')) }}
											

											<div class="input-group date datepicker">

												<span class="input-group-addon addon-left calendar-addon">
													<span class="fa fa-calendar"></span>
												</span>
												{{ Form::text('birthday', Auth::user()->birthday, ['class' => 'form-control', 'id' => 'datepicker1']) }}
												<span class="input-group-addon addon-right angle-addon">
													<span class="fa fa-angle-down"></span>
												</span>
											</div>
										</fieldset>
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group required">
											{{ Form::label('gender', trans('common.gender')) }}
											{{ Form::select('gender', array('male' => trans('common.male'), 'female' => trans('common.female'), 'other' => trans('common.none')), Auth::user()->gender, array('class' => 'form-control')) }}										
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											{{ Form::label('city', trans('common.current_city')) }}
											{{ Form::text('city', Auth::user()->city, ['class' => 'form-control', 'placeholder' => trans('common.current_city')]) }}
										</fieldset>
									</div>
								</div>

								<div class="row">
									<div class="col-md-6">
										<fieldset class="form-group">
											{{ Form::label('country', trans('common.country')) }}
											{{ Form::text('country', Auth::user()->country, array('class' => 'form-control', 'placeholder' => trans('common.country'))) }}
										</fieldset>
									</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											{{ Form::label('timezone', trans('common.timezone')) }}
											{{ Form::select('timezone',
												array('Pacific/Midway' => '(GMT-11:00) Midway Island, Samoa'),

												Auth::user()->timezone, array('class' => 'form-control', 'placeholder' => trans('common.timezone'))) }}
											</fieldset>
										</div>
									<div class="col-md-6">
										<fieldset class="form-group">
											{{ Form::label('language', trans('common.language')) }}
											{{ Form::select('language', Config::get('app.locales'),

												Auth::user()->language, array('class' => 'form-control')) }}
										</fieldset>
									</div>
									</div>

									<h3>
										{{ trans('common.be_social') }}
									</h3>
									<hr>
									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('facebook_link', trans('admin.facebook_link')) }}
												<div class="input-group facebook-input-group">
													<div class="input-group-addon fb-btn"><i class="fa fa-facebook"></i></div>
													{{ Form::text('facebook_link', Auth::user()->facebook_link, array('class' => 'form-control account-form', 'placeholder' => trans('admin.facebook_link'))) }}
												</div>

											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('youtube_link', trans('admin.youtube_link')) }}
												<div class="input-group youtube-input-group">
													<div class="input-group-addon youtube-btn"><i class="fa fa-youtube"></i></div>
													{{ Form::text('youtube_link', Auth::user()->youtube_link, array('class' => 'form-control', 'placeholder' => trans('admin.youtube_link'))) }}
												</div>

											</fieldset>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('twitter_link', trans('admin.twitter_link')) }}
												<div class="input-group twitter-input-group">
													<div class="input-group-addon twitter-btn"><i class="fa fa-twitter"></i></div>
													{{ Form::text('twitter_link', Auth::user()->twitter_link, array('class' => 'form-control', 'placeholder' => trans('admin.twitter_link'))) }}
												</div>

											</fieldset>
										</div>			
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('instagram_link', trans('admin.instagram_link')) }}
												<div class="input-group instagram-input-group">
													<div class="input-group-addon instagram-btn"><i class="fa fa-instagram"></i></div>
													{{ Form::text('instagram_link', Auth::user()->instagram_link, array('class' => 'form-control', 'placeholder' => trans('admin.instagram_link'))) }}
												</div>

											</fieldset>
										</div>
									</div>

									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('dribbble_link', trans('admin.dribbble_link')) }}
												<div class="input-group dribbble-input-group">
													<div class="input-group-addon dribbble-btn"><i class="fa fa-dribbble"></i></div>
													{{ Form::text('dribbble_link', Auth::user()->dribbble_link, array('class' => 'form-control', 'placeholder' => trans('admin.dribbble_link'))) }}
												</div>

											</fieldset>
										</div>			
										<div class="col-md-6">
											<fieldset class="form-group">
												{{ Form::label('linkedin_link', trans('admin.linkedin_link')) }}
												<div class="input-group linkedin-input-group">
													<div class="input-group-addon linkedin-btn"><i class="fa fa-linkedin"></i></div>
													{{ Form::text('linkedin_link', Auth::user()->linkedin_link, array('class' => 'form-control', 'placeholder' => trans('admin.linkedin_link'))) }}
												</div>

											</fieldset>
										</div>
									</div>

									<div class="pull-right">
										{{ Form::submit(trans('common.save_changes'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div><!-- /Socialite-form -->
						</div>
					</div>
					<!-- End of first panel -->

					<div class="panel panel-default">
						<div class="panel-heading no-bg panel-settings">
							<h3 class="panel-title">
								{{ trans('common.update_password') }}
							</h3>
						</div>
						<div class="panel-body nopadding">
							<div class="socialite-form">								
								<form method="POST" action="{{ url('/'.Auth::user()->username.'/settings/password/') }}">
									{{ csrf_field() }}

									<div class="row">
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('current_password') ? ' has-error' : '' }}">
												{{ Form::label('current_password', trans('common.current_password')) }}
												<input type="password" class="form-control" id="current_password" name="current_password" value="{{ old('current_password') }}" placeholder= "{{ trans('messages.enter_old_password') }}">

												@if ($errors->has('current_password'))
												<span class="help-block">
													<strong>{{ $errors->first('current_password') }}</strong>
												</span>
												@endif
											</fieldset>
										</div>
										<div class="col-md-6">
											<fieldset class="form-group {{ $errors->has('new_password') ? ' has-error' : '' }}">
												{{ Form::label('new_password', trans('common.new_password')) }}
												<input type="password" class="form-control" id="new_password" name="new_password" value="{{ old('new_password') }}" placeholder="{{ trans('messages.enter_new_password') }}">

												@if($errors->has('new_password'))
												<span class="help-block">
													<strong>{{ $errors->first('new_password') }}</strong>
												</span>
												@endif
											</fieldset>
										</div>
									</div>

									<div class="pull-right">
										{{ Form::submit(trans('common.save_password'), ['class' => 'btn btn-success']) }}
									</div>
									<div class="clearfix"></div>
								</form>
							</div><!-- /Socialite-form -->
						</div>
					</div>
					<!-- End of second panel -->

				</div>
			</div><!-- /row -->
		</div>
	<!-- </div> --><!-- /main-content -->
