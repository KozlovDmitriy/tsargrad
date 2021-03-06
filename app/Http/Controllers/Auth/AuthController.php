<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Request;
use Validator;
use App\Models\User;
use App\Models\Avatar;
use App\Models\CommentBlock;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use App\Models\News;


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
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|min:4|max:200',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required|min:6',
        ],[
            'required' => 'Все поля должны быть заполнены',
            'email.unique' => 'Такой email уже используется',
            'name.min' => 'Имя полководца не должно быть меньше :min символов',
            'name.max' => 'Имя полководца не должно быть больше :max символов',
            'password.min' => 'Пароль не может быть меньше :min символов',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return User
     */
    protected function create(array $data)
    {
        $avatar = Avatar::create([
                        'mustache_id' => 1,
                        'amulet_id' => 1,
                        'hair_id' => 1,
                        'flag_id' => 1,
        ]);
        $cb = CommentBlock::create();
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'avatar_id' => $avatar->id,
            'comment_block_id' => $cb->id
        ]);
    }

    public function getLogin()
    {
        if (Auth::check())
        {
            return redirect('game');
        }

        $news = News::orderBy('date', 'desc')->paginate(3);

        return view('auth/login', [
            'news' => $news
        ]);
    }

    public function postLogin()
    {
        $data = Request::all();

        $remember =isset($data['remember']) ? ($data['remember'] === 'true' ? true : false) : false;
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']], $remember))
        {
            return redirect('game');
        }
        return view('auth/login', ['error_message' => 'Неверный email или пароль']);
    }

    public function getLogout()
    {
        Auth::logout();
        return redirect('auth/login');
    }

    /*public function getRegister()
    {
        return redirect('auth/register');
    }*/

    public function postRegister()
    {
        $data = Request::all();

        $validator = $this->validator($data);

        if ($validator->passes()) {
            if ($data['password'] === $data['password_confirmation']) {
                if (User::where('email', $data['email'])->count() === 0) {
                    $new_user = $this->create($data);
                    if ($new_user !== null) {
                        Auth::login($new_user);
                        return redirect($new_user->pathToProfile());
                    }
                    return view('auth/register', ['error_message' => 'Не удалось создать пользователя. Попробуйте еще раз или обратитесь к разработчикам.']);
                }
                return view('auth/register', ['error_message' => 'Такой email уже используется.']);
            }
            return view('auth/register', ['error_message' => 'Пароли не совпадают.']);
        }
        return view('auth/register', ['error_message' => $validator->messages()->first()]);
    }
}
