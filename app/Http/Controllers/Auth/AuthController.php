<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Media;
use App\Setting;
use App\Timeline;
use App\User;
use DB;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Intervention\Image\Facades\Image;
use Laravel\Socialite\Facades\Socialite;
use Teepluss\Theme\Facades\Theme;
use Validator;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware($this->guestMiddleware(), ['except' => 'logout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $captcha = null)
    {
        $messages = [];
        $rules = [
            'name'      => 'required|max:255',
            'email'     => 'required|email|max:255|unique:users',
            'password'  => 'required|min:6',
            'username'  => 'required|max:25|min:5|alpha_num|unique:timelines',
            'gender'    => 'required',
            'affiliate' => 'exists:timelines,username',
        ];

        if ($captcha) {
            $messages = ['g-recaptcha-response.required' => 'The captcha field is required'];
            $rules['g-recaptcha-response'] = 'required';
        }

        return Validator::make($data, $rules, $messages);
    }

    public function getLogin()
    {
        if (Auth::user()) {
            return Redirect::to('/');
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('guest');
        $theme->setTitle(trans('auth.login').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users.login')->render();
    }

    public function login(Request $request)
    {
        $data = $request->all();
        $validate = Validator::make($data, [
            'email'    => 'required',
            'password' => 'required',
        ]);

        if (!$validate->passes()) {
            return response()->json(['status' => '201', 'message' => trans('auth.login_failed')]);
        } else {
            $user = '';
            $nameoremail = '';
            $canLogin = false;
            $remember = ($request->remember ? true : false);

            if (filter_var(($request->email), FILTER_VALIDATE_EMAIL)  == true) {
                $nameoremail = $request->email;
                $user = DB::table('users')->where('email', $request->email)->first();
            } else {
                $timeline = DB::table('timelines')->where('username', $request->email)->first();
                if ($timeline != null) {
                    $user = DB::table('users')->where('timeline_id', $timeline->id)->first();
                    if ($user) {
                        $nameoremail = $user->email;
                    }
                }
            }

            if (Setting::get('mail_verification') == 'off') {
                $canLogin = true;
            } else {
                if ($user != null) {
                    if ($user->email_verified == 1) {
                        $canLogin = true;
                    } else {
                        return response()->json(['status' => '201', 'message' => trans('Please verify your email')]);
                    }
                }
            }
        }

        if ($canLogin && Auth::attempt(['email' => $nameoremail, 'password' => $request->password], $remember)) {
            return response()->json(['status' => '200', 'message' => trans('auth.login_success')]);
        } else {
            return response()->json(['status' => '201', 'message' => trans('auth.login_failed')]);
        }
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $timeline = Timeline::create([
            'username' => $data['username'],
            'name'     => $data['name'],
        ]);

        return User::create([
            'email'       => $data['email'],
            'password'    => bcrypt($data['password']),
            'timeline_id' => $timeline->id,
        ]);
    }

    public function register()
    {
        if (Auth::user()) {
            return Redirect::to('/');
        }

        $theme = Theme::uses(Setting::get('current_theme', 'default'))->layout('guest');
        $theme->setTitle(trans('auth.register').' | '.Setting::get('site_title').' | '.Setting::get('site_tagline'));

        return $theme->scope('users.register')->render();
    }

    protected function registerUser(Request $request, $socialLogin = false)
    {
        if (Setting::get('captcha') == 'on' && !$socialLogin) {
            $validator = $this->validator($request->all(), true);
        } else {
            $validator = $this->validator($request->all());
        }

        if ($validator->fails()) {
            return response()->json(['status' => '201', 'err_result' => $validator->errors()->toArray()]);
        }

        if ($request->affiliate) {
            $timeline = Timeline::where('username', $request->affiliate)->first();
            $affiliate_id = $timeline->user->id;
        } else {
            $affiliate_id = null;
        }

        //Create timeline record for the user
        $timeline = Timeline::create([
            'username' => $request->username,
            'name'     => $request->name,
            ]);

        //Create user record
        $user = User::create([
            'email'             => $request->email,
            'password'          => bcrypt($request->password),
            'timeline_id'       => $timeline->id,
            'gender'            => $request->gender,
            'affiliate_id'      => $affiliate_id,
            'verification_code' => str_random(30),
            ]);
        if (Setting::get('birthday') == 'on' && $request->birthday != '') {
            $user->birthday = date('Y-m-d', strtotime($request->birthday));
            $user->save();
        }

        if (Setting::get('city') == 'on' && $request->city != '') {
            $user->city = $request->city;
            $user->save();
        }

        $user->name = $timeline->name;
        $user->email = $request->email;

        //saving default settings to user settings
        $user_settings = [
          'user_id'               => $user->id,
          'confirm_follow'        => Setting::get('confirm_follow'),
          'follow_privacy'        => Setting::get('follow_privacy'),
          'comment_privacy'       => Setting::get('comment_privacy'),
          'timeline_post_privacy' => Setting::get('user_timeline_post_privacy'),
          'post_privacy'          => Setting::get('post_privacy'), ];

        //Create a record in user settings table.
        $userSettings = DB::table('user_settings')->insert($user_settings);

        if ($user) {
            if ($socialLogin) {
                return $timeline;
            } else {
                //dd('not social');
              if (Setting::get('mail_verification') == 'on') {
                  Mail::send('emails.welcome', ['user' => $user], function ($m) use ($user) {
                      $m->from(Setting::get('noreply_email'), Setting::get('site_name'));

                      $m->to($user->email, $user->name)->subject('Welcome to '.Setting::get('site_name'));
                  });
              }

                return response()->json(['status' => '200', 'message' => trans('auth.verify_email')]);
            }
        }
    }

    public function verifyEmail(Request $request)
    {
        $user = User::where('email', '=', $request->email)->where('verification_code', '=', $request->code)->first();

        if ($user->email_verified) {
            return Redirect::to('login')
            ->with('login_notice', '<div class="alert alert-success">You have already verified your email</div>');
        } elseif ($user) {
            $user->email_verified = 1;
            $user->update();

            return Redirect::to('login')
          ->with('login_notice', '<div class="alert alert-success">You have successfully verified your email. Please login now.</div>');
        } else {
            echo 'Invalid verification code or request';
        }
    }

    public function facebookRedirect()
    {
        return Socialite::with('facebook')->redirect();
    }

    // to get authenticate user data
    public function facebook()
    {
        $facebook_user = Socialite::with('facebook')->user();

        $email = $facebook_user->email;

        if ($email == null) {
            $email = $facebook_user->id.'@facebook.com';
        }

        $user = User::firstOrNew(['email' => $email]);

        if ($facebook_user->name != null) {
            $name = $facebook_user->name;
        } else {
            $name = $email;
        }

        if (!$user->id) {
            $request = new Request(['username' => $facebook_user->id,
              'name'                           => $name,
              'email'                          => $email,
              'password'                       => bcrypt(str_random(8)),
              'gender'                         => 'none',
            ]);

            $timeline = $this->registerUser($request, true);
            //  Prepare the image for user avatar
            if ($facebook_user->avatar != null) {
                $avatar = Image::make($facebook_user->avatar);
                $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
                $avatar->save(storage_path().'/uploads/users/avatars/'.$photoName, 60);
                $media = Media::create([
                        'title'  => $photoName,
                        'type'   => 'image',
                        'source' => $photoName,
                      ]);
                $timeline->avatar_id = $media->id;
                $timeline->save();
            }

            $user = $timeline->user;
        } else {
            $timeline = $user->timeline;
        }


        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => 'Facebook doesn\'t provide username, so please change your temporary username ', 'status' => 'warning']);
        } else {
            return redirect($timeline->username)->with(['message' => 'User authentication problem', 'status' => 'success']);
        }
    }

    public function googleRedirect()
    {
        return Socialite::with('google')->redirect();
    }

    // to get authenticate user data
    public function google()
    {
        $google_user = Socialite::with('google')->user();
        $user = User::firstOrNew(['email' => $google_user->email]);
        if (!$user->id) {
            $request = new Request(['username' => $google_user->id,
              'name'                           => $google_user->name,
              'email'                          => $google_user->email,
              'password'                       => bcrypt(str_random(8)),
              'gender'                         => $google_user->user['gender'],
            ]);
            $timeline = $this->registerUser($request, true);

            //  Prepare the image for user avatar
        $avatar = Image::make($google_user->avatar);
            $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
            $avatar->save(storage_path().'/uploads/users/avatars/'.$photoName, 60);

            $media = Media::create([
                      'title'  => $photoName,
                      'type'   => 'image',
                      'source' => $photoName,
                    ]);

            $timeline->avatar_id = $media->id;

            $timeline->save();
            $user = $timeline->user;
        }

        if (Auth::loginUsingId($user->id)) {
            return redirect('/')->with(['message' => 'Google doesn\'t provide username, so please change your temporary username ', 'status' => 'warning']);
        } else {
            return redirect($timeline->username)->with(['message' => 'User authentication problem', 'status' => 'success']);
        }
    }

    public function twitterRedirect()
    {
        return Socialite::with('twitter')->redirect();
    }

  // to get authenticate user data
  public function twitter()
  {
      $twitter_user = Socialite::with('twitter')->user();

      $user = User::firstOrNew(['email' => $twitter_user->id.'@twitter.com']);
      if (!$user->id) {
          $request = new Request(['username'   => $twitter_user->id,
              'name'                           => $twitter_user->name,
              'email'                          => $twitter_user->id.'@twitter.com',
              'password'                       => bcrypt(str_random(8)),
              'gender'                         => 'none',
            ]);
          $timeline = $this->registerUser($request, true);
            //  Prepare the image for user avatar
        $avatar = Image::make($twitter_user->avatar_original);
          $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
          $avatar->save(storage_path().'/uploads/users/avatars/'.$photoName, 60);

          $media = Media::create([
                      'title'  => $photoName,
                      'type'   => 'image',
                      'source' => $photoName,
                    ]);

          $timeline->avatar_id = $media->id;

          $timeline->save();
          $user = $timeline->user;
      }

      if (Auth::loginUsingId($user->id)) {
          return redirect('/')->with(['message' => 'Twitter doesn\'t provide email, so please change your temporary email <b>'.$user->email.'</b>', 'status' => 'warning']);
      } else {
          return redirect('login')->with(['message' => 'User authentication problem', 'status' => 'error']);
      }
  }

    public function linkedinRedirect()
    {
        return Socialite::with('linkedin')->redirect();
    }

  // to get authenticate user data
  public function linkedin()
  {
      $linkedin_user = Socialite::with('linkedin')->user();

      $user = User::firstOrNew(['email' => $linkedin_user->email]);
      if (!$user->id) {
          $request = new Request(['username'   => preg_replace('/[^A-Za-z0-9 ]/', '', $linkedin_user->id),
              'name'                           => $linkedin_user->name,
              'email'                          => $linkedin_user->email,
              'password'                       => bcrypt(str_random(8)),
              'gender'                         => 'none',
            ]);

          $timeline = $this->registerUser($request, true);

            //  Prepare the image for user avatar
        $avatar = Image::make($linkedin_user->avatar_original);
          $photoName = date('Y-m-d-H-i-s').str_random(8).'.png';
          $avatar->save(storage_path().'/uploads/users/avatars/'.$photoName, 60);

          $media = Media::create([
                      'title'  => $photoName,
                      'type'   => 'image',
                      'source' => $photoName,
                    ]);

          $timeline->avatar_id = $media->id;

          $timeline->save();
          $user = $timeline->user;
      }

      if (Auth::loginUsingId($user->id)) {
          return redirect('/')->with(['message' => 'linkedin doesn\'t provide username, so please change your temporary username ', 'status' => 'warning']);
      } else {
          return redirect('login')->with(['message' => 'User authentication problem', 'status' => 'error']);
      }
  }
}
