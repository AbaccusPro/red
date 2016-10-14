<div class="login-block">
    <div class="panel panel-default">
        <div class="panel-body nopadding">
            <div class="login-head">
                {{ trans('auth.login_welcome_heading') }}
                <div class="header-circle"><i class="fa fa-paper-plane" aria-hidden="true"></i></div>
                <div class="header-circle login-progress hidden"><i class="fa fa-spinner fa-spin" aria-hidden="true"></i></div>
            </div>
            <div class="login-bottom">
                <div class="login-errors text-danger"></div>
                @if (Config::get('app.env') == 'demo')
                    <div class="alert alert-success">
                        username : <code>bootstrapguru</code> &nbsp;&nbsp;&nbsp;   password : <code>socialite</code>
                    </div>
                @endif
                <form method="POST" class="login-form" action="{{ url('/login') }}">
                    {{ csrf_field() }}
                    <fieldset class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
                        {{ Form::label('email', trans('auth.enter_email_or_username')) }}
                        {{ Form::text('email', NULL, ['class' => 'form-control', 'id' => 'email', 'placeholder'=> trans('auth.enter_email_or_username')]) }}
                    </fieldset>
                    <fieldset class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                        {{ Form::label('password', trans('auth.password')) }}
                        {{ Form::password('password', ['class' => 'form-control', 'id' => 'password', 'placeholder'=> trans('auth.password')]) }}
                    </fieldset>
                    {{ Form::button( trans('auth.signin_to_dashboard') , ['type' => 'submit','class' => 'btn btn-success btn-submit']) }}
                </form>
            </div>  
            @if((env('GOOGLE_CLIENT_ID') != NULL && env('GOOGLE_CLIENT_SECRET') != NULL) ||
                (env('TWITTER_CLIENT_ID') != NULL && env('TWITTER_CLIENT_SECRET') != NULL) ||
                (env('FACEBOOK_CLIENT_ID') != NULL && env('FACEBOOK_CLIENT_SECRET') != NULL) ||
                (env('LINKEDIN_CLIENT_ID') != NULL && env('LINKEDIN_CLIENT_SECRET') != NULL) )
                <div class="divider-login">
                    <div class="divider-text"> {{ trans('auth.login_via_social_networks') }}</div>
                </div>
            @endif
            <ul class="list-inline social-connect">
                @if(env('GOOGLE_CLIENT_ID') != NULL && env('GOOGLE_CLIENT_SECRET') != NULL)
                    <li><a href="{{ url('google') }}" class="btn btn-social google-plus"><span class="social-circle"><i class="fa fa-google-plus" aria-hidden="true"></i></span></a></li> 
                @endif

                @if(env('TWITTER_CLIENT_ID') != NULL && env('TWITTER_CLIENT_SECRET') != NULL)
                    <li><a href="{{ url('twitter') }}" class="btn btn-social tw"><span class="social-circle"><i class="fa fa-twitter" aria-hidden="true"></i></span></a></li>
                @endif

                @if(env('FACEBOOK_CLIENT_ID') != NULL && env('FACEBOOK_CLIENT_SECRET') != NULL)
                    <li><a href="{{ url('facebook') }}" class="btn btn-social fb"><span class="social-circle"><i class="fa fa-facebook" aria-hidden="true"></i></span></a></li>
                @endif

                @if(env('LINKEDIN_CLIENT_ID') != NULL && env('LINKEDIN_CLIENT_SECRET') != NULL) 
                    <li><a href="{{ url('linkedin') }}" class="btn btn-social linkedin"><span class="social-circle"><i class="fa fa-linkedin" aria-hidden="true"></i></span></a></li>
                @endif
            </ul>
        </div>
    </div>
    <div class="problem-login">
        <div class="pull-left">{{ trans('auth.dont_have_an_account_yet') }}<a href="{{ url('/register') }}"> {{ trans('auth.get_started') }}</a></div>
        <div class="pull-right"><a href="{{ url('/password/reset') }}">{{ trans('auth.forgot_password').'?' }}</a></div>
        <div class="clearfix"></div>
    </div>
</div><!-- /login-block -->
