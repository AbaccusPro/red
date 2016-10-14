<div class="panel panel-default">
	<div class="panel-body">
	@include('flash::message')
		<div class="panel-heading no-bg panel-settings">
			<h3 class="panel-title">
				{{ trans('admin.edit_user') }} ({{$timeline->name}})
			</h3>
		</div>
		<form method="POST" action="{{ url('admin/users/'.$username.'/edit') }}" class="socialite-form">
			{{ csrf_field() }}
			<fieldset class="form-group">
				{{ Form::label('verified', trans('admin.verified'), ['class' => 'control-label']) }}
				{{ Form::select('verified', array('1' => trans('common.yes'), '0' => trans('common.no')) , $user->verified , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.verified_user_text') }}</small>				
			</fieldset>
			<fieldset class="form-group required {{ $errors->has('name') ? ' has-error' : '' }}">
				{{ Form::label('name', trans('auth.name'), ['class' => 'control-label']) }}
				<input type="text" class="form-control" name="name" value="{{ $timeline->name }}" placeholder="Name">
				<small class="text-muted">{{ trans('admin.user_name_text') }}</small>
				@if ($errors->has('name'))
				<span class="help-block">
					<strong>{{ $errors->first('name') }}</strong>
				</span>
				@endif
			</fieldset>
			<fieldset class="form-group required {{ $errors->has('username') ? ' has-error' : '' }}">
				{{ Form::label('username', trans('common.username'), ['class' => 'control-label']) }}
				<input type="text" class="form-control content-form" placeholder="{{ trans('common.username') }}" name="username" value="{{ $timeline->username }}">
				<small class="text-muted">{{ trans('admin.user_username_text') }}</small>
				@if ($errors->has('username'))
				<span class="help-block">
					<strong>{{ $errors->first('username') }}</strong>
				</span>
				@endif
			</fieldset>
			<fieldset class="form-group required {{ $errors->has('email') ? ' has-error' : '' }}">
				{{ Form::label('email', trans('auth.email_address'), ['class' => 'control-label']) }}
				<input type="text" class="form-control" name="email" value="{{  $user->email }}" placeholder="{{ trans('common.email') }}">
				<small class="text-muted">{{ trans('admin.user_email_text') }}</small>
				@if ($errors->has('email'))
				<span class="help-block">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
				@endif
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('about', trans('common.about'), ['class' => 'control-label']) }}
				<textarea class="form-control about-form" name="about" rows="3" value="" placeholder="{{ trans('common.about') }}">{{ $timeline->about }}</textarea>
				<small class="text-muted">{{ trans('admin.user_about_text') }}</small>
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('city', trans('common.current_city'), ['class' => 'control-label']) }}
				<input type="text" class="form-control" name="city" value="{{ $user->city }}" placeholder="{{ trans('common.current_city') }}">
				<small class="text-muted">{{ trans('admin.user_city_text') }}</small>
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('country', trans('admin.hometown'), ['class' => 'control-label']) }}
				<input type="text" class="form-control" name="country" value="{{ $user->country }}" placeholder="{{ trans('common.country') }}">
				<small class="text-muted">{{ trans('admin.user_country_text') }}</small>

			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('gender', trans('common.gender'), ['class' => 'control-label']) }}
				{{ Form::select('gender', array('male' => trans('common.male'),'female' => trans('common.female'),'other' => trans('common.other')) , $user->gender , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.user_gender_text') }}</small>				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('birthday', trans('common.birthday'), ['class' => 'control-label']) }}
				<input class="datepicker form-control hasDatepicker" size="16" id="datepick2" name="birthday" type="text" value="{{ $user->birthday }}" data-date-format="yyyy-mm-dd">				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('confirm_follow', trans('admin.confirm_followers'), ['class' => 'control-label']) }}
				{{ Form::select('confirm_follow', array('no' => trans('common.no'),'yes' => trans('common.yes')) , $user_settings->confirm_follow , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.confirm_follow') }}</small>				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('follow_privacy', trans('admin.follow_privacy_label'), ['class' => 'control-label']) }}
				{{ Form::select('follow_privacy', array('everyone' => trans('common.everyone'),'only_follow' => trans('admin.only_follow')) , $user_settings->follow_privacy , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.follow_privacy') }}</small>				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('post_privacy', trans('admin.post_privacy_label'), ['class' => 'control-label']) }}
				{{ Form::select('post_privacy', array('everyone' => trans('common.everyone'),'only_follow' => trans('admin.only_follow')) , $user_settings->post_privacy , ['class' => 'form-control']) }}	
				<small class="text-muted">{{ trans('admin.post_privacy') }}</small>				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('timeline_post_privacy', trans('admin.user_timeline_post_privacy_label'), ['class' => 'control-label']) }}
				{{ Form::select('timeline_post_privacy', array('everyone' => trans('common.everyone'),'only_follow' => trans('admin.only_follow'), 'none' => trans('common.no_one')) , $user_settings->timeline_post_privacy , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.user_timeline_post_privacy') }}</small>				
			</fieldset>
			<fieldset class="form-group">
				{{ Form::label('comment_privacy', trans('admin.comment_privacy_label'), ['class' => 'control-label']) }}
				{{ Form::select('comment_privacy', array('everyone' => trans('common.everyone'),'only_follow' => trans('admin.only_follow')) , $user_settings->comment_privacy , ['class' => 'form-control']) }}
				<small class="text-muted">{{ trans('admin.comment_privacy') }}</small>				
			</fieldset>
			<div class="pull-right">
				<button type="submit" class="btn btn-primary btn-sm">{{ trans('common.save_changes') }}</button>
			</div>
		</form>
		
	</div>
</div>

<div class="panel panel-default">	
	<div class="panel-body">
		<form class="edit-form" method="POST" action="{{ url('admin/users/'.$username.'/newpassword') }}">
			{{ csrf_field() }}
			<div class="panel-heading no-bg panel-settings">
				<h3 class="panel-title">
					{{ trans('common.update_password') }} ({{ $timeline->name }})
				</h3>
			</div>
			<fieldset class="form-group required {{ $errors->has('password') ? ' has-error' : '' }}">
				{{ Form::label('new_password', trans('common.new_password'), ['class' => 'control-label']) }}
				<input type="password" class="form-control" name="password" placeholder="{{ trans('common.new_password') }}">
				<small class="text-muted">{{ trans('common.new_password_text') }}</small>
				@if ($errors->has('password'))
				<span class="help-block">
					<strong>{{ $errors->first('password') }}</strong>
				</span>
				@endif
			</fieldset>
			<fieldset class="form-group required {{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
				{{ Form::label('password_confirmation', trans('common.confirm_password'), ['class' => 'control-label']) }}
				<input type="password" class="form-control" name="password_confirmation" placeholder="{{ trans('common.confirm_password') }}">
				<small class="text-muted">{{ trans('common.confirm_password_text') }}</small>
				@if ($errors->has('password_confirmation'))
				<span class="help-block">
					<strong>{{ $errors->first('password_confirmation') }}</strong>
				</span>
				@endif
			</fieldset>
			<div class="pull-right">
				<button type="submit" class="btn btn-primary btn-sm">{{ trans('common.save_changes') }}</button>
			</div>
		</form>	
	</div>
</div>
