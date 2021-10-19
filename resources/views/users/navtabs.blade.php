
Lesson15
Twitterクローン
難易度123
Lesson 15Chapter 1
学習の目標
メッセージボードでは、リソースのCRUD操作を Route::resource() によって生成される7つのRESTfulな基本アクションで行い、その構造に沿ってModel, Router, Controller, Viewの基礎を学びました。

ここからはそれを前提に、さらに高度な機能を開発します。具体的には、認証、ユーザのフォロー／フォロワー、お気に入り機能です。認証はログインのことです。

このレッスンを完了すれば、自分でイメージしているアプリケーションを作れるようになるでしょう。しかし、このレッスンはメッセージボードよりも難しい内容になります。1つ1つ納得しながら進むようにしましょう。以前の内容を忘れてしまっていると感じた場合には、何度も戻って復習するようにしてください。

また、ここからは、既出の内容については詳しく言及しないことがあります。たとえば php artisan tinker でtinkerを起動しようとか、 php artisan serve --host=$IP --port=$PORT でサーバを起動しよう、といった書き方はしていません。すでに学んだものは皆さん自身でコードをしっかり読むようにしてください。

本レッスンの主な内容
今回作成するWebアプリケーションの概要
ユーザ登録機能の構築
ログイン機能の構築
投稿機能の構築
ユーザとユーザがフォローする／フォローされる機能の構築
本レッスンのゴール
TwitterクローンのWebアプリケーションが完成していること
ユーザ認証を使ったログインなどの応用的な機能の開発について理解していること
本レッスンの前提条件
AWS Cloud9へアカウント登録してワークスペースを作成できていること（事前準備）
Webアプリケーションの画面（ブラウザに表示される内容）の作り方を習得していること（HTML/CSS, Bootstrap）
ターミナルの操作方法を習得していること（ターミナル）
PHPの文法およびPHPを使った簡単なWebアプリケーションの作り方を習得していること（PHPその1-4）
MySQLおよびSQL言語の基本的な使い方を習得していること（MySQL）
HTMLとPHP、MySQLを連携させたWebアプリケーションの作り方を習得していること（PHPとMySQLの連携）
GitおよびGitHubを使ってバージョン管理をする方法を習得していること（Git/GitHub）
サーバをはじめとするWebアプリケーションの全体構成について理解していること（Web開発で学ぶこと, インターネット通信の仕組み）
Laravelを使ったWebアプリケーションの作り方の基礎を習得していること（メッセージボード）
HerokuでWebアプリケーションを公開する方法について習得していること（Heroku）
Lesson 15Chapter 2
今回作成するWebアプリケーション
今回作成するWebアプリケーションはTwitterのクローンになります。名前はMicropostsにします。

Lesson 15Chapter 2.1
機能一覧
ユーザ登録／認証
ツイート（Micropost)の一覧表示
フォロー／フォロワー機能
お気に入り機能（提出課題）
（参考）画面イメージ


トップページ（ログイン前）



ユーザ登録ページ



ログインページ



トップページ（ログイン後）



ユーザ一覧ページ



ユーザ詳細ページ



該当ユーザがフォローしているユーザページ



該当ユーザをフォローしているユーザページ

Lesson 15Chapter 3
プロジェクトの開始
では、さっそくLaravelプロジェクトを作成します。

Lesson 15Chapter 3.1
プロジェクトの作成
最初に、念のためメモリの開放をしておきましょう。

sudo sh -c "echo 3 > /proc/sys/vm/drop_caches"
次に、composerでLaravelプロジェクトを作成します。プロジェクト名はMicropostsです。プロジェクト作成時はカレントディレクトリ（pwd で表示される現在フォルダ）には気をつけてください。

cd ~/environment/
composer create-project --prefer-dist laravel/laravel microposts ^6.0
「信用するプロキシの設定」と「リンクをHTTPSにする設定」も行います。

app/Http/Middleware/TrustProxies.php（proxiesプロパティのみ抜粋）

    protected $proxies = '*'; // 全プロキシを信用
app/Providers/AppServiceProvider.php（boot()メソッドのみ抜粋）

    public function boot()
    {
        \URL::forceScheme('https');
    }
Lesson 15Chapter 3.2
動作確認
作成できた microposts のディレクトリへ移動し、Laravelのアプリケーションを起動して welcome ページが表示されるか確認しておいてください。 welcome.blade.php が読み込まれ「Laravel」が表示されていれば大丈夫です。

Lesson 15Chapter 3.3
Git
Gitでバージョン管理を開始しておきましょう。
また、ブランチ名をmasterからmainに変更しておきましょう。

 必ずプロジェクトのディレクトリで実施してください。誤って *~/environment/* ディレクトリで実施しないように気をつけてください。
git init

git add .

git commit -m 'init'

git branch -M main
Lesson 15Chapter 4
データベースと接続
データベースとの接続設定を行います。

Lesson 15Chapter 4.1
.envの修正
.env を修正して、データベース設定に関する環境変数を変更します。

.env

DB_DATABASE=microposts
DB_USERNAME=dbuser
DB_PASSWORD=dbpass
Lesson 15Chapter 4.2
データベースの作成
DB_DATABASE=microposts と環境変数を設定したため、microposts データベースを作成します。

MySQLにログイン

sudo mysql -u root
mysql>が表示されたら以下のSQLを実行

CREATE DATABASE microposts;
exitでMySQLからログアウト

exit
Lesson 15Chapter 4.3
tinkerで接続確認
tinkerを起動します。

php artisan tinker
>>> が表示されましたら、データベースの接続を確認します。 DB::reconnect() でエラーが出なければ問題なく接続できています。

DB::reconnect();
Lesson 15Chapter 4.4
タイムゾーンと言語設定
タイムゾーンの設定
タイムゾーンの設定をしておけば、 Modelからレコードを保存したときなどにおいて、設定したタイムゾーンの時間情報が日時型のカラム（created_at 等）に保存されます。

config/app.php timezone抜粋

    'timezone' => 'Asia/Tokyo',
Lesson 15Chapter 4.5
Git
git status

git diff

git add .

git commit -m 'set timezone'
Lesson 15Chapter 5
トップページ
ログイン前のトップページにはModel操作はないため、Viewのみを作成します。共通で利用するレイアウトやエラーメッセージなども実装しておきましょう。トップページは welcome.blade.php をそのまま修正していきましょう。

Lesson 15Chapter 5.1
Model
トップページ専用のモデルを用意する必要はありません。

Lesson 15Chapter 5.2
Router
/ にアクセスしたとき、下記のようにルーティングが設定されているため、そのまま welcome.blade.php を編集します。

routes/web.php（抜粋・追記や変更は不要）

Route::get('/', function () {
    return view('welcome');
});
Lesson 15Chapter 5.3
トップページ
Controller
Routerでの指定の通り、トップページではControllerは使用しません。

View
共通レイアウト
resources/views/layouts/app.blade.php

<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>Microposts</title>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css">
    </head>

    <body>

        {{-- ナビゲーションバー --}}
        @include('commons.navbar')

        <div class="container">
            {{-- エラーメッセージ --}}
            @include('commons.error_messages')

            @yield('content')
        </div>

        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js"></script>
        <script defer src="https://use.fontawesome.com/releases/v5.7.2/js/all.js"></script>
    </body>
</html>
エラーメッセージ
resources/views/commons/error_messages.blade.php

@if (count($errors) > 0)
    <ul class="alert alert-danger" role="alert">
        @foreach ($errors->all() as $error)
            <li class="ml-4">{{ $error }}</li>
        @endforeach
    </ul>
@endif
ナビゲーションバー
resources/views/commons/navbar.blade.php

<header class="mb-4">
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        {{-- トップページへのリンク --}}
        <a class="navbar-brand" href="/">Microposts</a>

        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#nav-bar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav-bar">
            <ul class="navbar-nav mr-auto"></ul>
            <ul class="navbar-nav">
                {{-- ユーザ登録ページへのリンク --}}
                <li class="nav-item"><a href="#" class="nav-link">Signup</a></li>
                {{-- ログインページへのリンク --}}
                <li class="nav-item"><a href="#" class="nav-link">Login</a></li>
            </ul>
        </div>
    </nav>
</header>
トップページ
welcome.blade.php にあらかじめ書かれていた内容は不要であるため、すべて削除して下記の通りにしてください。

resources/views/welcome.blade.php

@extends('layouts.app')

@section('content')
    <div class="center jumbotron">
        <div class="text-center">
            <h1>Welcome to the Microposts</h1>
        </div>
    </div>
@endsection
これでトップページの表示は完成です。動作確認もしておいてください。

Lesson 15Chapter 5.4
Git
git status

git diff

git add .

git commit -m 'top page'
Lesson 15Chapter 6
ユーザ登録機能
次に、ユーザ登録機能を作成します。

Lesson 15Chapter 6.1
Model
ユーザテーブルの設計
ユーザ登録機能であるため、 Userモデルを作成します。まずはmicropostsデータベースにusersテーブルを作りましょう。

ユーザテーブル設計のマイグレーションファイル
実はLaravelが、ユーザテーブル作成のためのマイグレーションファイルを、あらかじめ用意してくれています。作成されるusersテーブルの内容を把握しておきましょう。

2014_10_12_000000_create_users_table.phpのup() とdown()

    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
usersテーブルには、 id, name, email, email_verified_at, passwordなどのカラムが作成されます。

マイグレーションの実行
では、マイグレーションを実行して、 usersテーブルを作成することにします。

php artisan migrate
Migration table created successfully.
Migrating: 2014_10_12_000000_create_users_table
Migrated:  2014_10_12_000000_create_users_table (0.02 seconds)
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated:  2014_10_12_100000_create_password_resets_table (0.02 seconds)
Migrating: 2019_08_19_000000_create_failed_jobs_table
Migrated:  2019_08_19_000000_create_failed_jobs_table (0.01 seconds)
ユーザテーブル以外にも、パスワードリセットやキュージョブのためのマイグレーションファイルがあらかじめ用意されており、それらのテーブルも作成されます。このカリキュラムでは扱わないため、詳しく知りたい方は公式ドキュメントをご覧ください。

パスワードリセット - Laravel 6.x
キュー - Laravel 6.x
マイグレーションが実行済みになったことを php artisan migrate:status コマンドで確認しておきましょう。 MySQL側でテーブルの内容を describe users; で確認しておくのも良いでしょう。

Userモデルの確認
こちらもLaravelプロジェクトを作成した時点で用意されています。

app/User.php（抜粋）

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    // 中略
}
$fillable と $hidden という配列が書かれています。

$fillable
    protected $fillable = [
        'name', 'email', 'password',
    ];
後ほど、ユーザの作成に create() という関数を使います。create() は save() と同じくデータベースにINSERTを発行する関数です。

create() は save() のようにインスタンスを作成する必要がなく、データを代入してそのままユーザを作成できます。ここで save() について、おさらいしましょう。 save() では、1つずつデータを代入し保存するしかありませんでした。たとえば、下記のようにです。

例（記述不要です）

$user = new User;
$user->name = $request->name;
$user->email = $request->email;
$user->password = Hash::make($request->password);
$user->save();
こうする必要があったのは、想定外のデータが代入されたパラメータの保存を防ぐためです。create() は一気にデータを代入できますが、すべての項目がデフォルトで「一気に保存可能」になっていると、想定外のデータが保存されるかもしれず、セキュリティ上それは良いことでありません。

そこで通常は、すべてのカラムをデフォルトでは「一気に保存不可」とし、$fillable で「一気に保存可能」なパラメータを指定します。こうすることで、想定外のデータが代入されるのを防ぎ、なおかつ、一気にデータを代入することが可能になります。

例（記述不要です）

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
create() を使ってデータを保存するときには、そのModelファイルの中に $fillable を定義し、create() で保存可能なパラメータを配列として代入しておく必要があることを覚えておいてください。

$hidden
パスワードなど秘匿しておきたいカラムを、モデルで $hidden に指定しておくと、見られないように隠してくれます。たとえば、 tinkerで User::first()->toArray() や (string)User::first() とUser::first()で取得した値を配列や文字列に変換したときに、 Userモデルのコーディングで $hidden = [‘password’] と代入されていると、password は変換されません。

（補足）$table
今回は必要ありませんが $table という変数も指定できます。

モデルと接続されるテーブル名は、モデル名によって決められます。たとえば、 Messageモデルはmessagesテーブルと自動的に接続されます。この規則を破って独自のテーブル名をつけたい場合に、 $table を使用します。たとえば、 Messageモデルだけど、 msgテーブルを使いたいとなれば $table = 'msg' とすれば接続されます。

今回はUserモデルに対して $table = 'users' で、規則通りであるため省略されています。

（補足）Hashファサード
まだ利用していませんが Hash ファサードについても、ここで触れておきます。

Hash ファサードはハッシュの機能を提供します。セキュリティの観点から、パスワードはハッシュしてからデータベースに保存すべきで、パスワードを平文（そのまま）保存すべきではありません。パスワードは必ずハッシュしましょう。

tinkerを起動してどんなハッシュが行われるか確認してください。

>>> $test = Hash::make('test')
=> "$2y$10$pyPEl29CHG87uJJbq5I8h.396UgdDi4MHD4wktFvPnlyIuzraZ6Zy"
'test' をハッシュすれば、 "$2y$10$pyPEl29CHG87uJJbq5I8h.396UgdDi4MHD4wktFvPnlyIuzraZ6Zy" となりました。

実は $test = Hash::make('test') を何度も実行するとわかりますが、ハッシュ値は一定ではなく変化します。

しかし、下記のように、 Hash::check() を利用すると「test という文字列をハッシュしたものかどうか」を判定できます。

>>> $test = Hash::make('test')

>>> Hash::check('test', $test)
=> true

>>> Hash::check('test1', $test)
=> false
詳しくは述べませんが、ログイン時には内部で Hash::check() が呼び出され、入力されたパスワードとデータベースに保存されているハッシュ値が一致しているかを確認しています。

これで、ユーザ登録時のパスワードをハッシュしてからデータベースに保存することができ、さらに、ログイン時にもパスワードの一致を確認できます。

試しにtinkerでユーザを作成してみる
>>> use App\User

>>> User::all()
=> Illuminate\Database\Eloquent\Collection {#686
     all: [],
   }

>>> User::create([
... 'name' => 'test',
... 'email' => 'test@test.com',
... 'password' => Hash::make('test') ])

>>> User::all()
=> Illuminate\Database\Eloquent\Collection {#690
     all: [
     App\User {#4147
          id: 1,
          name: "test",
          email: "test@test.com",
          email_verified_at: null,
          #password: "$2y$10$bc7cE6uW7Ou/FnaYD.JMWuhSd/rRlA2ZWsKThtwDy8gPp9s4VhI.e",
          #remember_token: null,
          created_at: "2021-04-13 17:01:03",
          updated_at: "2021-04-13 17:01:03",
       },
     ],
   }
最後の all() の結果を見ると、 passwordやremember_tokenに#がついています。これはModelファイル側のコードで $hidden として設定されているからです。

Lesson 15Chapter 6.2
Router
ユーザを登録するためのControllerもあらかじめ用意されています。 app/Http/Controllers/Auth/RegisterController.php です。これについては次で解説します。

ここでは、ルーティングを下記のように設定してください。

routes/web.phpユーザ登録のルーティング追加

// ユーザ登録
Route::get('signup', 'Auth\RegisterController@showRegistrationForm')->name('signup.get');
Route::post('signup', 'Auth\RegisterController@register')->name('signup.post');
->name() はこのルーティングに名前をつけているだけです。後ほど、 Formやlink_to_route() で使用することになります。

Lesson 15Chapter 6.3
RegisterController@showRegistrationForm, register
RegisterController
ではRegisterControllerを見ていきます。

app/Http/Controllers/Auth/RegisterController.php一部コメントやnamespaceなど省略

class RegisterController extends Controller
{
    // 中略

    use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }
}
RegisterControllerはユーザ登録のためのコントローラです。 RegisterControllerに関して、いくつか補足説明をします。

RegistersUsersトレイト
Routerで下記のように定義したのに、 showRegistrationForm アクションと register アクションはどこだと疑問に思うかもしれません。

Route::get('signup', 'Auth\RegisterController@showRegistrationForm')->name('signup.get');
Route::post('signup', 'Auth\RegisterController@register')->name('signup.post');
RegisterControllerでは特別に use RegistersUsers; という記述があることを確認できると思います。これによって、上記2つのアクションをそのまま取り込んでいるのです。

RegisterUsers は トレイト です。トレイトについておさらいすると、単にいくつかの機能（メソッド）をまとめているものです。そして use [トレイト名]; によって、まとめた機能をそのまま取り込めます。 RegistersUsers を取り込んだRegisterControllerは、 RegistersUsers で定義されているメソッドをそのまま取り込むことができます。

参考: トレイト
実際、 RegistersUsersトレイトのソースコードを見てみると、確かに showRegistrationForm() と register() が定義されていることが確認できます。

middleware() について
コントローラの __construct() でミドルウェア（middleware)を指定できます。 Laravelにおけるミドルウェアは コントローラのアクションが実行される前（後）に実行される前処理（後処理） であると思ってください。 RegisterController や LoginController では以下のように指定されています。

app/Http/Controllers/Auth/RegisterController.php（抜粋）

        $this->middleware('guest');
app/Http/Controllers/Auth/LoginController.php（抜粋）

        $this->middleware('guest')->except('logout');
後者は、 logout アクションを除くこのコントローラの全アクションに guest ミドルウェアを指定していることになります。

ゲスト（guest）とは、ログインしていない閲覧者のことです。 guest ミドルウェアは、アクションの実行前にログイン状態を確認し、ログインしていない場合はそのまま実行させますが、ログインしている場合は実行させず別のURLへ飛ばします。

これらのコントローラでは、ゲストにだけユーザ登録やログインを実行させるため guest ミドルウェアを指定しているのです。なお、別のURLへ飛ばすことを リダイレクト といい、そのURLをリダイレクト先といいます。

ユーザ登録直後のリダイレクト先の設定
ユーザ登録が完了すると、ログイン状態になった上で、指定のリダイレクト先へ飛ぶようになっています。

RegistersUsers@register
RedirectsUsers@redirectPath
そのリダイレクト先は $redirectTo 変数に設定されている定数 RouteServiceProvider::HOME で定義されています。

app/Http/Controllers/Auth/RegisterController.php（抜粋）

    protected $redirectTo = RouteServiceProvider::HOME;
app/Providers/RouteServiceProvider.php（抜粋）

    public const HOME = '/home';
これを以下のように変更しましょう。これでリダイレクト先がトップページになります。

app/Providers/RouteServiceProvider.php（抜粋）

    public const HOME = '/';
guestミドルウェア
guest の定義はどこにあるのでしょうか。Laravelの中に guest という名前のクラスがあるわけではありません。 guest は エイリアス（ニックネームのようなもの）としてつけられた名前です。 guest の定義は App\Http\Kernel クラスで確認できます。

app/Http/Kernel.php（抜粋）

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
これを読むと guest の正体は \App\Http\Middleware\RedirectIfAuthenticated というクラスであることがわかります。なお、::class は名前空間を含む正しいクラス名（完全修飾名）を取得するための指定であるため、あまり深く考えなくて大丈夫です。

内容も確認してみましょう。 if (Auth::guard($guard)->check()) でログインしているかどうかを判断し、ログイン済みの場合は RouteServiceProvider::HOME にリダイレクトさせています。リダイレクト先はユーザ登録成功後と同じです。

app/Http/Middleware/RedirectIfAuthenticated.php（抜粋）

    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
validator()
validator() では、ユーザ登録の際のフォームデータのバリデーションを行っています。

RegistersUsersトレイトのregisterメソッドの中身を見ると、 validator() を呼び出しているのがわかります。

RegistersUsers@register
RegisterControllerの中で validator() を実装することで、ユーザ登録時のバリデーション処理の内容を定義しています。

create()
名前がややこしいのですが、これはRESTfulなアクション7つの内の1つであるcreateアクションではなく、Userを新規作成しているメソッドになります。これもRegistersUsersトレイトのregisterメソッドの中で呼び出されているのがわかります。

RegistersUsers@register
View
LaravelCollective HTMLのインストール
最初に、メッセージボードでもインストールした、 Form や link_to_route() などが利用できる外部ライブラリをTwitterクローンのViewでも利用しましょう。

LaravelCollective HTML
composer require laravelcollective/html:^6.0
ユーザ登録（作成）ページ
RegisterController => RegistersUsersへと辿りました。そしてRegistersUsersを見ると showRegistrationForm() が定義されており、中には return view('auth.register'); の1行だけが記述されていることがわかります。

RegistersUsersのshowRegistrationForm() の中身
showRegistrationForm() アクションは、ただ単に resources/views/auth/register.blade.php を表示するアクションだということです。ただし、このビューのファイルは作成されていません。

ややこしくなってきたため、話をまとめます。

今ここではユーザ登録の機能を作ろうとしています。ModelとControllerは最初から用意されており、Routingの設定は先ほど行いました。

あとは用意されていない auth/ フォルダと register.blade.php を作成するだけでユーザ登録が動作します。

ただし、ユーザ登録の機能を試すのは「ログイン機能」を構築してからにしてください（理由は後述）。

では、register.blade.php を作りましょう。

resources/views/auth/register.blade.php

@extends('layouts.app')

@section('content')
    <div class="text-center">
        <h1>Sign up</h1>
    </div>

    <div class="row">
        <div class="col-sm-6 offset-sm-3">

            {!! Form::open(['route' => 'signup.post']) !!}
                <div class="form-group">
                    {!! Form::label('name', 'Name') !!}
                    {!! Form::text('name', null, ['class' => 'form-control']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::email('email', null, ['class' => 'form-control']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('password', 'Password') !!}
                    {!! Form::password('password', ['class' => 'form-control']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('password_confirmation', 'Confirmation') !!}
                    {!! Form::password('password_confirmation', ['class' => 'form-control']) !!}
                </div>

                {!! Form::submit('Sign up', ['class' => 'btn btn-primary btn-block']) !!}
            {!! Form::close() !!}
        </div>
    </div>
@endsection
このフォームはコード記載の通り、 signup.post のルーティング、つまり register() アクションに送信されます。

そして register() の中ではログインも自動的に実行されます（参考：RegisterUsers.php）が、次のチャプターで扱う「ログイン機能」を実装するまではログアウトもできません。今はユーザ登録の機能は試さず、すぐに次へ進んでください。

トップページにユーザ登録リンクを作成
resources/views/welcome.blade.php

@extends('layouts.app')

@section('content')
    <div class="center jumbotron">
        <div class="text-center">
            <h1>Welcome to the Microposts</h1>
            {{-- ユーザ登録ページへのリンク --}}
            {!! link_to_route('signup.get', 'Sign up now!', [], ['class' => 'btn btn-lg btn-primary']) !!}
        </div>
    </div>
@endsection
ナビゲーションバー
ナビゲーションバーのSignupのリンクも正しいリンク先を設定しておきます。

resources/views/commons/navbar.blade.php

                <ul class="navbar-nav">
                    {{-- ユーザ登録ページへのリンク --}}
                    <li>{!! link_to_route('signup.get', 'Signup', [], ['class' => 'nav-link']) !!}</li>
                    {{-- ログインページへのリンク --}}
                    <li class="nav-item"><a href="#" class="nav-link">Login</a></li>
                </ul>
Lesson 15Chapter 6.4
Git
git status

git diff

git add .

git commit -m 'user registration'
Lesson 15Chapter 7
ログイン機能
ログイン機能もユーザ登録機能と同じくLaravel側であらかじめ用意されています。

Lesson 15Chapter 7.1
ログインの仕組み
機能として用意されているとはいえ、「一般的にWebアプリへのログインはどのような仕組みになっているか」を簡単にでも知っておくと、Laravelでのログイン機能の構築に関する理解が深まります。

ここでは、ログインというものが、ブラウザと Web アプリの間でどのように実現されているかを解説します。

HTTP はステートレスな通信
まず、前提として、HTTP はステートレスな通信だということを知っておきましょう。ステートレスとは、ステート（状態）がレス（無い）ということで、状態を維持しないということです。HTTP は以前の通信を全く覚えないと言い換えることもできます。

ステートレスな通信として例え話をします。ハンバーガーショップでの、客と店員のやりとりを考えてみましょう。

ステートフル（状態を維持する）な会話の場合

店員「ご注文をどうぞ」
客「ハンバーガーセットをお願いします」
店員「サイドメニューは何になさいますか？」
客「ポテトで」
店員「ドリンクは何になさいますか？」
客「コーラで」
店員「+50円でドリンクをLサイズにできますがいかがですか？」
客「Mでいいです」
店員「以上でよろしいですか？」
客「はい」
店員「かしこまりました」
客の要望は店員に伝わっているため、このような会話で以下のような情報が店員に伝わっています。

注文はハンバーガーセット
サイドメニューはポテト
ドリンクはコーラ
ドリンクのサイズはM
人と人との会話ではステートフルなのが基本です。では、ステートレスな会話とはどういうものか見てみます。

ステートレス（状態を維持しない）な会話の場合

店員「ご注文をどうぞ」
客「ハンバーガーセットをお願いします」
店員「サイドメニューは何になさいますか？」
客「ハンバーガーセットをポテトで」
店員「ドリンクは何になさいますか？」
客「ハンバーガーセットをポテトとコーラで」
店員「+50円でドリンクをLサイズにできますがいかがですか？」
客「ハンバーガーセットをポテトとコーラ(M)で」
店員「以上でよろしいですか？」
客「ハンバーガーセットをポテトとコーラ(M)で。以上です。」
店員「かしこまりました」
客が何度も同じ事を繰り返し言っています。これは、店員がステートレスなせいで、毎回言ってあげないと忘れてしまうからです。

その割に、店員の会話が進展しているのは、客が一言にどこまで含めているかを毎回確認しているからです。セットメニューとサイドメニューの名前を言っても、ドリンクの名前がなければ、店員は「ドリンクは何になさいますか？」と尋ねることになります。

HTTP 通信の実態も、まさに、このような形です。置き換える場合、客がブラウザであり、店員がサーバ（Webアプリ）となります。

ログインの仕組み
HTTP はステートレスなので、ログインを維持するのは HTTP という通信内ではありません。ログイン情報は、ブラウザ内部と、サーバ（Webアプリ）内部で保存されることになっていて、以下の流れでログインの有無を判定します。

ブラウザが保存しているログイン情報を、ブラウザがサーバにアクセスするたびに毎回 HTTP で送受信します。
サーバ側はブラウザからのアクセスがあるたびに、受け取ったブラウザからのログイン情報を毎回自分（サーバ）の持っているログイン情報と照合します。
照合した結果で、ログインされているかを判定します。
このとき、ブラウザが持っているログイン情報は Cookie として保存され、サーバが持っているログイン情報は Session として保存されます。つまり、Cookie はブラウザ側の情報保管所で、Session はサーバ側の情報保管所だと思ってください。

ログインしているかどうかは、お互いを照合することのできる情報が、ブラウザの Cookie とサーバの Session に保存されているかどうかで決まります。

TechAcademyのログイン情報
TechAcademyもログイン情報を扱っているので、当然ブラウザの Cookie にログイン情報を保存しています。

Google Chrome ブラウザを使用している場合、下記の URL にアクセスすると、皆さんの Google Chrome に保存された Cookie 一覧を確認することができます。URL は1つですが、自分の Google Chrome の Cookie だけ見られます。ですので、他人に見られることはありません。

chrome://settings/content/cookies （URL に入力してアクセスしてください）
ここで、techacademy.jp の Cookie を見てください。いくつか保存されていますが、 _billy_session というのが保存されたログイン情報です。当然暗号化されているので、ぱっと見ただけではどのような形でログイン情報を記録しているのかはわかりません。

 この値はプライベートな値なので、他人に漏らさないようにしてください。暗号化されているとはいえ、この値が漏れると、他人に自分のアカウントが乗っ取られる可能性があります。充分ご注意ください。
他のサイトの Cookie を確認するのも面白いでしょう。また、Cloud9 や Heroku で Twitter クローンのログイン機能を実装したあとに、自分のブラウザの Cookie を調べてみるのも良いでしょう。

Lesson 15Chapter 7.2
Router
認証は、LoginControllerが担当します。以下の3つのルーティングを routes/web.php に追記してください。

routes/web.php

// 認証
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('login', 'Auth\LoginController@login')->name('login.post');
Route::get('logout', 'Auth\LoginController@logout')->name('logout.get');
Lesson 15Chapter 7.3
LoginController@showLoginForm, login, logout
LoginController
app/Http/Controllers/Auth/LoginController.php を確認してください。

use AuthenticatesUsers; とあるように、 AuthenticatesUsers トレイトを使っていることがわかります。 Routerで設定した showLoginForm や login のアクションはそちらに定義されています。

リダイレクト
ログイン成功後のリダイレクト先もユーザ登録成功後と同じ RouteServiceProvider::HOME に設定されています。

app/Http/Controllers/Auth/LoginController.php（抜粋）

    protected $redirectTo = RouteServiceProvider::HOME;
View
ログインページ
ログインページを作成しましょう。

resources/views/auth/login.blade.php

@extends('layouts.app')

@section('content')
    <div class="text-center">
        <h1>Log in</h1>
    </div>

    <div class="row">
        <div class="col-sm-6 offset-sm-3">

            {!! Form::open(['route' => 'login.post']) !!}
                <div class="form-group">
                    {!! Form::label('email', 'Email') !!}
                    {!! Form::email('email', null, ['class' => 'form-control']) !!}
                </div>

                <div class="form-group">
                    {!! Form::label('password', 'Password') !!}
                    {!! Form::password('password', ['class' => 'form-control']) !!}
                </div>

                {!! Form::submit('Log in', ['class' => 'btn btn-primary btn-block']) !!}
            {!! Form::close() !!}

            {{-- ユーザ登録ページへのリンク --}}
            <p class="mt-2">New user? {!! link_to_route('signup.get', 'Sign up now!') !!}</p>
        </div>
    </div>
@endsection
ナビゲーションバー
ログインができるようになったため、ナビゲーションバーを充実させましょう。「Signup」「Login」のリンクはログアウト状態のときのみ表示するようにし、ログイン状態のときはログアウトできるようにします。

まずは以下のコードに書き換えてください。

resources/views/commons/navbar.blade.php

<header class="mb-4">
    <nav class="navbar navbar-expand-sm navbar-dark bg-dark">
        {{-- トップページへのリンク --}}
        <a class="navbar-brand" href="/">Microposts</a>

        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#nav-bar">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav-bar">
            <ul class="navbar-nav mr-auto"></ul>
            <ul class="navbar-nav">
                @if (Auth::check())
                    {{-- ユーザ一覧ページへのリンク --}}
                    <li class="nav-item"><a href="#" class="nav-link">Users</a></li>
                    <li class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">{{ Auth::user()->name }}</a>
                        <ul class="dropdown-menu dropdown-menu-right">
                            {{-- ユーザ詳細ページへのリンク --}}
                            <li class="dropdown-item"><a href="#">My profile</a></li>
                            <li class="dropdown-divider"></li>
                            {{-- ログアウトへのリンク --}}
                            <li class="dropdown-item">{!! link_to_route('logout.get', 'Logout') !!}</li>
                        </ul>
                    </li>
                @else
                    {{-- ユーザ登録ページへのリンク --}}
                    <li class="nav-item">{!! link_to_route('signup.get', 'Signup', [], ['class' => 'nav-link']) !!}</li>
                    {{-- ログインページへのリンク --}}
                    <li class="nav-item">{!! link_to_route('login', 'Login', [], ['class' => 'nav-link']) !!}</li>
                @endif
            </ul>
        </div>
    </nav>
</header>
Bladeのファイル内で、条件によって表示内容を分けるために if-else 文を使いたいときは @if (条件式） ... @else ... @endif の指定をしてください。条件式に指定した Auth::check() は、ユーザがログインしているかどうかを調べるための関数です。

Authファサードについて
ファサードとは、各クラスのメソッドを、クラスのインスタンスを作らなくてもstaticメソッドとして利用できるようにし、使いやすくしたものです。

また、そのファサードを、さらに使いやすいようにグローバールの名前空間（\）で扱えるようにしたものがエイリアスです。

以下に'Auth' => Illuminate\Support\Facades\Auth::class,とありますが、右側のIlluminate\Support\Facades\Auth::classがエイリアスに登録したいクラスです。
通常はIlluminate\Support\Facades\Authと記述する必要があるところを、\Authと短く記述して呼び出せるようになります。

エイリアスは、 config/app.phpのaliasesの中で設定されています。

config/app.php

    'aliases' => [

        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,

    ],
実は、今までにも上記にあるDBファサードを使った DB::reconnect()（DB::connection()）や、Routeファサードを使ってのルーティングの設定などいくつかのファサードを利用しています。

参考: ファサードLaravel 6.x
中でも、 Authファサードは認証に関する一連のメソッドを提供しています。先ほど紹介した Auth::check()もその1つで、 別のメソッドであるAuth::user() を利用するとログイン中のユーザを取得できます。

このタイミングで、ユーザ登録とログイン/ログアウト機能を試してみましょう。適当なユーザを追加し、ログイン/ログアウトを行ってみてください。

※ユーザ登録の際のメールアドレスは下記の形式（@以降に1つ以上の.（ドット）を含む）を守って登録してください。そうでないとGravatarを取得する際のメールアドレスのバリデーションでエラーが発生してしまいます。

xxxx@yyyy.zzzz

トップページ
トップページも今後は充実させていきますが、ここではいったんログインしているかどうかで分岐させるようにしておきましょう。

resources/views/welcome.blade.php

@extends('layouts.app')

@section('content')
    @if (Auth::check())
        {{ Auth::user()->name }}
    @else
        <div class="center jumbotron">
            <div class="text-center">
                <h1>Welcome to the Microposts</h1>
                {{-- ユーザ登録ページへのリンク --}}
                {!! link_to_route('signup.get', 'Sign up now!', [], ['class' => 'btn btn-lg btn-primary']) !!}
            </div>
        </div>
    @endif
@endsection
ちなみに、Bladeファイルの中には素のPHPコードを埋め込むこともできます。今回のコードでは記述しませんが、{{ Auth::user()->name }} とは違う方法として以下のような形でユーザ名を表示することも可能です。

一例であるため追記不要です

<?php $user = Auth::user(); ?>
{{ $user->name }}
Lesson 15Chapter 7.4
Git
git status

git diff

git add .

git commit -m 'user login'
ポイントの確認
ここまでの内容がどんな内容だったか、改めて振り返っておきましょう。
一度に網羅する必要はありません。まずは下記の項目を理解できていれば大丈夫です。

・ステートレスな通信内容を確認する
・ログインの仕組み内容を確認する
・CookieとSession内容を確認する
・ログインの有無によって表示内容を変える方法内容を確認する
Lesson 15Chapter 8
その他のユーザ機能
ユーザ登録（作成）や認証についてはすでに用意されていたRegisterControllerやLoginControllerが担ってくれました。しかし、下記のようなUserの機能を加えようとすると、新たにControllerを作成する必要があります。

Userの一覧表示
Userの詳細情報の表示
ここでは、Userの「一覧表示」と「詳細情報の表示」機能を作成します。

Lesson 15Chapter 8.1
Model
用意されていたUserモデルを引き続き利用するため、新規作成するモデルはありません。

Lesson 15Chapter 8.2
Router
RegisterControllerが用意していたユーザ登録アクション以外に、下記のアクションを作成します。

ユーザ一覧（index)
ユーザ詳細（show)
この2つのアクションは、RegisterControllerとは別に UsersController を用意し、その中に書いていきましょう。

認証付きのルーティング
ユーザ一覧とユーザ詳細はログインしていない閲覧者には見せたくありません。そのようなときは auth ミドルウェアを使いましょう。

guest ミドルウェアと同じようにコントローラのコンストラクタで指定することもできますが、ここではルーティングで指定します。

routes/web.phpに下記を追記

Route::group(['middleware' => ['auth']], function () {
    Route::resource('users', 'UsersController', ['only' => ['index', 'show']]);
});
Route::group() でルートのグループを作り、 auth ミドルウェアを指定することで、このグループ内のルートへアクセスする際に、認証を必要とするようにできます。

なお、 ['only' => ['index', 'show']] は作成されるルートを絞り込んでいます。この UsersController は7つのアクションのうち index（ユーザ一覧）と show（ユーザ詳細）だけで良いからです。

ユーザに対するそれ以外のアクション
ここでは作成しませんが、ユーザが自分の名前を編集するアクション（edit, update)や退会アクション（destroy)を作っても問題ありませんし、さらにユーザの登録情報（年齢や自己紹介など）を充実（usersテーブルのカラム追加）させても良いでしょう。

これらはUsersControllerに実装すれば実現可能であり、ここまで学んだ内容で皆さんも充分に対応可能です。カラムの追加についてはメッセージボードの内容で扱いました。このアプリケーションへのアクションやカラムの追加は提出課題にはしませんが、復習を兼ねて学びを深めたい方は実装してみてください。

Lesson 15Chapter 8.3
UsersController@index
UsersControllerの作成
php artisan make:controller UsersController
ここではindexとshowのみを実装します。 createアクションやstoreアクションについてはRegisterControllerで実装されているため不要です。
Userに関するControllerを2つ用意する形となります。

Controllerの編集
まずは、 indexから実装していきましょう。

app/Http/Controllers/UsersController.php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User; // 追加

class UsersController extends Controller
{
    public function index()
    {
        // ユーザ一覧をidの降順で取得
        $users = User::orderBy('id', 'desc')->paginate(10);

        // ユーザ一覧ビューでそれを表示
        return view('users.index', [
            'users' => $users,
        ]);
    }
}
View
Gravatar表示ライブラリをインストール
Viewを作っていく前にGravatarを設定していきましょう。

Gravatarとは、 メールアドレスに対して自分のアバター画像を登録するサービスです。Gravatarを登録しておき、 Gravatarに対応しているサイトでメールアドレスを設定して発言などをすると、そのアバター画像が表示されるようになります。

Gravatar
MicropostsもGravatarへ対応させ、メールアドレスからアバター画像を表示させるようにしましょう。実際にメールアドレスに対してGravatarを作成してみるとよくわかるかと思います。

では、以下のGitHubで配布されているパッケージをインストールします。

Gravatar for Laravel 5.x and 6.0
composer require を使って、以下のコマンドを実行してください。

composer require creativeorange/gravatar ~1.0
users.index
ユーザ一覧を表示します。

resources/views/users/index.blade.php

@extends('layouts.app')

@section('content')
    {{-- ユーザ一覧 --}}
    @include('users.users')
@endsection
ユーザ一覧の表示部分は、あとで「ユーザがフォローしているユーザ一覧／ユーザのフォロワー一覧」にも使用するため、1つにまとめておきます。なお、この表示部分ではBootstrapのメディアリストを利用しています。

参考：メディアリスト

resources/views/users/users.blade.php

@if (count($users) > 0)
    <ul class="list-unstyled">
        @foreach ($users as $user)
            <li class="media">
                {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                <img class="mr-2 rounded" src="{{ Gravatar::get($user->email, ['size' => 50]) }}" alt="">
                <div class="media-body">
                    <div>
                        {{ $user->name }}
                    </div>
                    <div>
                        {{-- ユーザ詳細ページへのリンク --}}
                        <p>{!! link_to_route('users.show', 'View profile', ['user' => $user->id]) !!}</p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
@endif
ページネーション
$users = User::orderBy('id', 'desc')->paginate(10); で、10件ずつ取得する形式にしています（10件ないとページネーションが表示されないため、試しに paginate(1) として確認しても良いでしょう）。

ControllerだけでなくViewも追記する必要があります。 {{ $users->links() }} を追記してください。このコードでページネーションのためのBootstrapの部品が表示されます。

resources/views/users/users.blade.php（抜粋）

        ...
        @endforeach
    </ul>
    {{-- ページネーションのリンク --}}
    {{ $users->links() }}
@endif
ナビゲーションバー
usersのindexを作成したため、ナビゲーションバーにあったUsersのリンクをつけましょう。

resources/views/commons/navbar.blade.php（抜粋）

            <ul class="navbar-nav">
                @if (Auth::check())
                    {{-- ユーザ一覧ページへのリンク --}}
                    <li class="nav-item">{!! link_to_route('users.index', 'Users', [], ['class' => 'nav-link']) !!}</li>
                    <li class="nav-item dropdown">
                        ...
Lesson 15Chapter 8.4
UsersController@show
Controller
UsersController
showでは、 $id の引数を利用して表示すべきユーザを特定します。

app/Http/Controllers/UsersController.phpのshowアクション

    public function show($id)
    {
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // ユーザ詳細ビューでそれを表示
        return view('users.show', [
            'user' => $user,
        ]);
    }
View
users.show
現時点で、ユーザ詳細ページでは、ユーザの名前とGravatarを表示しているだけです。後ほど、さまざまな機能を実装して充実させていきます。

resources/views/users/show.blade.php

@extends('layouts.app')

@section('content')
    <div class="row">
        <aside class="col-sm-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $user->name }}</h3>
                </div>
                <div class="card-body">
                    {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                    <img class="rounded img-fluid" src="{{ Gravatar::get($user->email, ['size' => 500]) }}" alt="">
                </div>
            </div>
        </aside>
        <div class="col-sm-8">
            <ul class="nav nav-tabs nav-justified mb-3">
                {{-- ユーザ詳細タブ --}}
                <li class="nav-item"><a href="#" class="nav-link">TimeLine</a></li>
                {{-- フォロー一覧タブ --}}
                <li class="nav-item"><a href="#" class="nav-link">Followings</a></li>
                {{-- フォロワー一覧タブ --}}
                <li class="nav-item"><a href="#" class="nav-link">Followers</a></li>
            </ul>
        </div>
    </div>
@endsection
なお、アバターの表示部分にはBootstrapのカードを利用しています。

参考：カード

ナビゲーションバー
usersのshowを作成したので、ナビゲーションバーにあった My profileのリンクをつけましょう。

resources/views/commons/navbar.blade.php

                            <ul class="dropdown-menu dropdown-menu-right">
                                {{-- ユーザ詳細ページへのリンク --}}
                                <li class="dropdown-item">{!! link_to_route('users.show', 'My profile', ['user' => Auth::id()]) !!}</li>
                                <li class="dropdown-divider"></li>
                                {{-- ログアウトへのリンク --}}
                                <li class="dropdown-item">{!! link_to_route('logout.get', 'Logout') !!}</li>
                            </ul>
ここで Auth::id() というクラスメソッドを使いましたが、これはログインユーザのIDを取得することができるメソッドで、Auth::user()->id と同じ動きになります。覚えておきましょう。

Lesson 15Chapter 8.5
Git
git status

git diff

git add .

git commit -m 'user pages'
Lesson 15Chapter 8.6
ここまでのまとめ
このレッスンではTwitterのクローンサイトを構築しています。まずはサイトのユーザに関わる機能として「ユーザ登録」と「ログイン」の機能を作りました。これから作る投稿機能などは、ユーザがログインしていなければ利用できない機能となっています。

Lesson 15Chapter 9
投稿機能
次は、投稿機能を作成します。

Lesson 15Chapter 9.1
Model
ユーザの投稿をMicropostというモデル名で作成します。

一対多の関係
UserとMicropostは 一対多 の関係です。

一対多の関係とは、ある1つのModelインスタンス（Aに対して複数のModelインスタンス（B, B, …)を保持する関係のことです。

たとえば今回のUserとMicropostでは、1人のUserは複数のMicropostをツイートすることが可能（hasMany）であり、 1つのMicropostは必ず1人のUserに所属（belongsTo）することが決まっています。



Model同士が一対多の関係であることに気付くことは重要です。今後のWebアプリケーション開発において、 一対多の関係となるリソースを次々と作っていくことになります。ここで一対多のModelの作成方法や扱い方をしっかり抑えておきましょう。

テーブル設計
マイグレーションファイルの作成
php artisan make:migration create_microposts_table --create=microposts
database/migrations/年月日時_create_microposts_table.phpのup() とdown()

    public function up()
    {
        Schema::create('microposts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('content');
            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::dropIfExists('microposts');
    }
Micropostには、各Micropostを識別する id、そのMicropostを投稿したユーザのID（user_id）、投稿内容（content）、登録日時と更新日時（timestamps()）をカラムとして持たせます。

外部キー制約について
$table->foreign(外部キーを設定するカラム名)->references(参照先のカラム名)->on(参照先のテーブル名);
外部キー制約 の機能はLaravelの機能ではなく、データベース側の機能です。

この機能は保存されるテーブルの整合性を担保するために利用します。整合性とは、データが矛盾なく一貫している状態を意味します。

たとえば、あるMicropostのuser_idに存在しないUserのidが設定されていたとします。このとき、そのMicropostの情報自体はデータベース上に存在しますが、どのUserとも紐づいていない状態となります。

このようなデータは表示されないゴミデータになるだけでなく、エラーを引き起こす原因にもなりかねません。言い換えれば、外部キー制約はUserとMicropostの「つながり」を保証するための機能です。

つまり、外部キー制約は外部テーブルのデータを参照する場合には必須機能と言えます。

マイグレーションの実行
php artisan migrate
マイグレーションが実行済みになったことを php artisan migrate:status コマンドで確認しておきましょう。 MySQL側でテーブルの内容を describe microposts; で確認しておくのも良いでしょう。

Micropost Model
まず、 Micropostのモデルファイルを作成します。

php artisan make:model Micropost
作成したモデルファイルに $fillable を設定しておきましょう。

そして、モデルファイルの中にも一対多の関係を記述しておきましょう。

Micropostを持つUserは1人であるため、 function user() のように単数形userでメソッドを定義し、中身は return $this->belongsTo(User::class) とします。

このようにすることで、 Micropostのインスタンスが所属している唯一のUser（投稿者の情報）を $micropost->user()->first() もしくは $micropost->user という簡単な記述で取得できるようになります。

app/Micropost.php（namespaceなど省略）

class Micropost extends Model
{
    protected $fillable = ['content'];

    /**
     * この投稿を所有するユーザ。（ Userモデルとの関係を定義）
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
User Model
Userモデルファイルにも一対多の表現を記述しておきます。 Userが持つMicropostは複数存在するため、 function microposts() のように複数形micropostsでメソッドを定義します。

中身は return $this->hasMany(Micropost::class); とします（先ほどとは異なりhasManyを使用していることに着目してください）。

app/User.php追記分

class User extends Authenticatable
{
    // 中略

    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }
}
Micropostのときと同様に記述することで、 UserのインスタンスからそのUserが持つMicropostsを $user->microposts()->get() もしくは $user->microposts という簡単な記述で取得できるようになります。

Micropostの数をカウントする機能を追加
Userが持つMicropostの数をカウントするためのメソッドも作成しておきます。

loadCount メソッドの引数に指定しているのはリレーション名です。先ほどモデル同士の関係を表すメソッドを定義しましたが、そのメソッド名がリレーション名になります。これによりUserのインスタンスに {リレーション名}_count プロパティが追加され、件数を取得できるようになります。

app/User.php追記分

    // 中略

    /**
     * このユーザに関係するモデルの件数をロードする。
     */
    public function loadRelationshipCounts()
    {
        $this->loadCount('microposts');
    }
後で出てきますが、アクションでこのメソッドを $user->loadRelationshipCounts() のように呼び出し、ビューで $user->microposts_count のように件数を取得することになります。

参考: 関連するモデルのカウント - Laravel 6.x
tinkerで投稿を作成
Laravelは、一対多などの関係を持つモデルに新しいレコードを追加するための便利なメソッドを用意しています。

たとえば、 User モデルに関係する新しい Micropost を挿入するためには create メソッドを使うことができます。このメソッドは、引数で受け取った連想配列をもとに、モデルを作成しデータベースへ挿入します。

※ 参考資料：createメソッド（Eloquent - Laravel 6.xドキュメント）

tinkerを使って、新しいMicropost 1個をデータベースへ挿入してみましょう。投稿の作成は $user->microposts()->create(['content' => 'micropost test']) のように操作します（以下の表示結果は一例です）。

>>> use App\User
>>> $user = User::first()
=> App\User {#756
     id: 1,
     name: "test",
     email: "test@test.com",
     email_verified_at: null,
     created_at: "2016-09-28 12:50:52",
     updated_at: "2016-09-28 12:50:52",
   }
>>> $user->microposts()->get()
=> Illuminate\Database\Eloquent\Collection {#759
     all: [],
   }
>>> $user->microposts()->create(['content' => 'micropost test'])
=> App\Micropost {#700
     id: 3,
     user_id: 1,
     content: "micropost test",
     created_at: "2016-12-08 18:48:39",
     updated_at: "2016-12-08 18:48:39",
   }
>>> $user->microposts()->get()
=> Illuminate\Database\Eloquent\Collection {#701
     all: [
       App\Micropost {#710
         id: 3,
         user_id: 1,
         content: "micropost test",
         created_at: "2016-12-08 18:48:39",
         updated_at: "2016-12-08 18:48:39",
       },
     ],
   }
Lesson 15Chapter 9.2
Router
認証を必要とするルーティンググループ内に、 Micropostsのルーティングを設定します（登録のstoreと削除のdestroyのみ）。これで、認証済みのユーザだけがこれらのアクションにアクセスできます。

routes/web.php（抜粋）

Route::group(['middleware' => ['auth']], function () {
    // 中略
    Route::resource('microposts', 'MicropostsController', ['only' => ['store', 'destroy']]);
});
また、 今まで / はRouterからControllerへ飛ばさずに直接welcomeのViewを表示させていました。

routes/web.php（抜粋）

Route::get('/', function () {
    return view('welcome');
});
ここからは少し複雑なことを行っていくのですが、上記の記述を下記のように変更し、Controller ( MicropostsController@index ) を経由してwelcomeを表示するようにします。

Route::get('/', 'MicropostsController@index');
この設定内容に変更した結果、 routes/web.php は以下のようになります。

routes/web.php

<?php

// 中略

Route::get('/', 'MicropostsController@index');    // 上書き

// 中略

Route::group(['middleware' => ['auth']], function () {
    Route::resource('users', 'UsersController', ['only' => ['index', 'show']]);
    Route::resource('microposts', 'MicropostsController', ['only' => ['store', 'destroy']]);
});
それぞれのアクションを実装していきましょう。

Lesson 15Chapter 9.3
MicropostsController@index
MicropostsControllerを生成して、indexアクションを書いていきましょう。

MicropostsController
php artisan make:controller MicropostsController
app/Http/Controllers/MicropostsController.php

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MicropostsController extends Controller
{
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザの投稿の一覧を作成日時の降順で取得
            // （後のChapterで他ユーザの投稿も取得するように変更しますが、現時点ではこのユーザの投稿のみ取得します）
            $microposts = $user->microposts()->orderBy('created_at', 'desc')->paginate(10);

            $data = [
                'user' => $user,
                'microposts' => $microposts,
            ];
        }

        // Welcomeビューでそれらを表示
        return view('welcome', $data);
    }
}
View
Micropostの一覧を表示する共通のViewとして、 microposts.blade.php を作成します。

resources/views/microposts/microposts.blade.php

@if (count($microposts) > 0)
    <ul class="list-unstyled">
        @foreach ($microposts as $micropost)
            <li class="media mb-3">
                {{-- 投稿の所有者のメールアドレスをもとにGravatarを取得して表示 --}}
                <img class="mr-2 rounded" src="{{ Gravatar::get($micropost->user->email, ['size' => 50]) }}" alt="">
                <div class="media-body">
                    <div>
                        {{-- 投稿の所有者のユーザ詳細ページへのリンク --}}
                        {!! link_to_route('users.show', $micropost->user->name, ['user' => $micropost->user->id]) !!}
                        <span class="text-muted">posted at {{ $micropost->created_at }}</span>
                    </div>
                    <div>
                        {{-- 投稿内容 --}}
                        <p class="mb-0">{!! nl2br(e($micropost->content)) !!}</p>
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    {{-- ページネーションのリンク --}}
    {{ $microposts->links() }}
@endif
これをwelcomeの中で @include すれば、ログイン後のトップページに自分の投稿したMicropostsが表示されるようになります。

resources/views/welcome.blade.php

@extends('layouts.app')

@section('content')
    @if (Auth::check())
        <div class="row">
            <aside class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ Auth::user()->name }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- 認証済みユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                        <img class="rounded img-fluid" src="{{ Gravatar::get(Auth::user()->email, ['size' => 500]) }}" alt="">
                    </div>
                </div>
            </aside>
            <div class="col-sm-8">
                {{-- 投稿一覧 --}}
                @include('microposts.microposts')
            </div>
        </div>
    @else
        <div class="center jumbotron">
            <div class="text-center">
                <h1>Welcome to the Microposts</h1>
                {{-- ユーザ登録ページへのリンク --}}
                {!! link_to_route('signup.get', 'Sign up now!', [], ['class' => 'btn btn-lg btn-primary']) !!}
            </div>
        </div>
    @endif
@endsection
では次にWeb上のフォームでMicropostを投稿できるようにします。

Lesson 15Chapter 9.4
MicropostsController@store
Controller
storeアクションを実装します。 9.1節ではtinkerで投稿を作成しました。それと同様に、storeアクションでは create メソッドを使ってMicropostを保存しています。

app/Http/Controllers/MicropostsController.php追記部分（storeアクション）のみ

    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'content' => 'required|max:255',
        ]);

        // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->microposts()->create([
            'content' => $request->content,
        ]);

        // 前のURLへリダイレクトさせる
        return back();
    }
return back() とすることで、前のページへリダイレクトされます。この store アクションの場合、リクエスト元の投稿フォームのページへ戻ることになります。

参考: back() - Laravel 6.x
Lesson 15Chapter 9.5
MicropostsController@destroy
投稿の削除を実装します。

Controller
app/Http/Controllers/MicropostsController.php追記部分（destroy）のみ抜粋

    public function destroy($id)
    {
        // idの値で投稿を検索して取得
        $micropost = \App\Micropost::findOrFail($id);

        // 認証済みユーザ（閲覧者）がその投稿の所有者である場合は、投稿を削除
        if (\Auth::id() === $micropost->user_id) {
            $micropost->delete();
        }

        // 前のURLへリダイレクトさせる
        return back();
    }
削除を実行する部分は、if文で囲みました。

追記不要です

        if (\Auth::id() === $micropost->user_id) {
            $micropost->delete();
        }
他者のMicropostを勝手に削除されないよう、ログインユーザのIDとMicropostの所有者のID（user_id）が一致しているかを調べるようにしています。

View
Viewに削除ボタンをつけ足します。

resources/views/microposts/microposts.blade.php

@if (count($microposts) > 0)
    <ul class="list-unstyled">
        @foreach ($microposts as $micropost)
            <li class="media mb-3">
                {{-- 投稿の所有者のメールアドレスをもとにGravatarを取得して表示 --}}
                <img class="mr-2 rounded" src="{{ Gravatar::get($micropost->user->email, ['size' => 50]) }}" alt="">
                <div class="media-body">
                    <div>
                        {{-- 投稿の所有者のユーザ詳細ページへのリンク --}}
                        {!! link_to_route('users.show', $micropost->user->name, ['user' => $micropost->user->id]) !!}
                        <span class="text-muted">posted at {{ $micropost->created_at }}</span>
                    </div>
                    <div>
                        {{-- 投稿内容 --}}
                        <p class="mb-0">{!! nl2br(e($micropost->content)) !!}</p>
                    </div>
                    <div>
                        @if (Auth::id() == $micropost->user_id)
                            {{-- 投稿削除ボタンのフォーム --}}
                            {!! Form::open(['route' => ['microposts.destroy', $micropost->id], 'method' => 'delete']) !!}
                                {!! Form::submit('Delete', ['class' => 'btn btn-danger btn-sm']) !!}
                            {!! Form::close() !!}
                        @endif
                    </div>
                </div>
            </li>
        @endforeach
    </ul>
    {{-- ページネーションのリンク --}}
    {{ $microposts->links() }}
@endif
Lesson 15Chapter 9.6
UsersController@show
Controller
対象のUserを取得後に、関係するモデルの件数と投稿の一覧を取得して、ビューに渡します。

app/Http/Controllers/UsersController.php showアクションのみ抜粋

    public function show($id)
    {
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // 関係するモデルの件数をロード
        $user->loadRelationshipCounts();

        // ユーザの投稿一覧を作成日時の降順で取得
        $microposts = $user->microposts()->orderBy('created_at', 'desc')->paginate(10);

        // ユーザ詳細ビューでそれらを表示
        return view('users.show', [
            'user' => $user,
            'microposts' => $microposts,
        ]);
    }
View
ユーザ詳細ページではユーザの投稿の一覧を表示します。また、ログインユーザ自身の詳細ページである場合は投稿フォームも設置します。投稿フォームは別のページにも設置するため、共通のViewとして作成しておきます。なお、このアプリケーションでは投稿用の専用ページを作りません。

resources/views/users/show.blade.php

@extends('layouts.app')

@section('content')
    <div class="row">
        <aside class="col-sm-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $user->name }}</h3>
                </div>
                <div class="card-body">
                    {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                    <img class="rounded img-fluid" src="{{ Gravatar::get($user->email, ['size' => 500]) }}" alt="">
                </div>
            </div>
        </aside>
        <div class="col-sm-8">
            <ul class="nav nav-tabs nav-justified mb-3">
                {{-- ユーザ詳細タブ --}}
                <li class="nav-item">
                    <a href="{{ route('users.show', ['user' => $user->id]) }}" class="nav-link {{ Request::routeIs('users.show') ? 'active' : '' }}">
                        TimeLine
                        <span class="badge badge-secondary">{{ $user->microposts_count }}</span>
                    </a>
                </li>
                {{-- フォロー一覧タブ --}}
                <li class="nav-item"><a href="#" class="nav-link">Followings</a></li>
                {{-- フォロワー一覧タブ --}}
                <li class="nav-item"><a href="#" class="nav-link">Followers</a></li>
            </ul>
            @if (Auth::id() == $user->id)
                {{-- 投稿フォーム --}}
                @include('microposts.form')
            @endif
            {{-- 投稿一覧 --}}
            @include('microposts.microposts')
        </div>
    </div>
@endsection
resources/views/microposts/form.blade.php

{!! Form::open(['route' => 'microposts.store']) !!}
    <div class="form-group">
        {!! Form::textarea('content', null, ['class' => 'form-control', 'rows' => '2']) !!}
        {!! Form::submit('Post', ['class' => 'btn btn-primary btn-block']) !!}
    </div>
{!! Form::close() !!}
show.blade.phpの記述のうち、下記の部分は少しややこしいため解説します。

                {{-- ユーザ詳細タブ --}}
                <li class="nav-item">
                    <a href="{{ route('users.show', ['user' => $user->id]) }}" class="nav-link {{ Request::routeIs('users.show') ? 'active' : '' }}">
                        TimeLine
                        <span class="badge badge-secondary">{{ $user->microposts_count }}</span>
                    </a>
                </li>
{{ Request::routeIs('users.show') ? 'active' : '' }} は、リクエストされたルートが users.show の場合は active という文字列を出力します。

Bootstrapのタブ部品は a 要素の class 属性に active クラスを付与することで、強調表示されて今開いているタブであることがわかりやすくなるため、このような記述にしています。

なお、 (式1) ? (式2) : (式3) の形式は三項演算子です。 式1 が true なら 式2 、 式1 が false なら 式3 が値となります。

参考: Request::routeIs - Laravel API
参考: 三項演算子
<a href="{{ route('users.show', ['user' => $user->id]) }}"> で使用している route() はヘルパー関数と呼ばれるもので、今までは link_to_route を使用してきましたが、ここではこちらを使用しています。

理由は、link_to_route だと <span class="badge">{{ $user->microposts_count }}</span> を含めたリンク名がうまく表示されないからです（これはLaravelCollectiveの仕様であるため、今のところは使う関数を変更するしかありません）。

参考: ヘルパー関数 - Laravel 6.x
$user->microposts_count はUserに関係するMicropostの件数を取得しています。

これは、 UsersController@show アクションで呼び出したUserの loadRelationshipCounts メソッドの中で、リレーション microposts の件数をロードしたことより可能になっています。

これでログインユーザ自身の情報が表示されるUserのページからMicropostが投稿できるようになりました。実際にいくつか投稿して表示内容を確認してみてください。

せっかくなのでトップページにも投稿フォームを設置しましょう。

resources/views/welcome.blade.php

@extends('layouts.app')

@section('content')
    @if (Auth::check())
        <div class="row">
            <aside class="col-sm-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ Auth::user()->name }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                        <img class="rounded img-fluid" src="{{ Gravatar::get(Auth::user()->email, ['size' => 500]) }}" alt="">
                    </div>
                </div>
            </aside>
            <div class="col-sm-8">
                {{-- 投稿フォーム --}}
                @include('microposts.form')
                {{-- 投稿一覧 --}}
                @include('microposts.microposts')
            </div>
        </div>
    @else
        <div class="center jumbotron">
            <div class="text-center">
                <h1>Welcome to the Microposts</h1>
                {{-- ユーザ登録ページへのリンク --}}
                {!! link_to_route('signup.get', 'Sign up now!', [], ['class' => 'btn btn-lg btn-primary']) !!}
            </div>
        </div>
    @endif
@endsection
Lesson 15Chapter 9.7
Git
git status

git diff

git add .

git commit -m 'post'
Lesson 15Chapter 9.8
ここまでのまとめ
ここまでで、文章を投稿できる機能を作りました。

ポイントの確認
ここまでの内容がどんな内容だったか、改めて振り返っておきましょう。
一度に網羅する必要はありません。まずは下記の項目を理解できていれば大丈夫です。

・一対多の関係（投稿機能）内容を確認する
・hasManyとbelongsTo内容を確認する
課題認証と一対多をタスク管理アプリケーションに追加する
Twitterクローンのアプリケーションに認証機能を実装する方法について学習しました。

内容をおさらいする意味で、Lesson13〜14で実装したタスク管理アプリケーション（Tasklist） に認証機能をつけて、ログインしているユーザが 自身の作成したタスクのみ にアクセスできる形としてください。

内容
ユーザ登録と認証（ログイン、ログアウト）の機能を追加してください。
未ログイン状態ではタスクの作成、編集、削除、表示ができないようにしてください。
ログイン状態では、自分自身のタスクのみを操作可能（表示、編集、削除）にしてください。（URL直打ちなどで）他人のタスクにアクセスしようとした場合はトップページにリダイレクトさせてください。
GitHubに完成した最新のソースコードをプッシュしてください。
 Heroku にデプロイしてください。Heroku アプリケーションに対してもマイグレーションや動作確認を忘れないようにしてください。
注意点
タスクの削除ができなくなった等、以前に実装できた機能が使えなくなる状態（デグレードといいます）にはならないよう、注意しながらコーディングを行ってください。
Twitterクローン風のアプリケーションになってしまわないよう十分にご注意ください。 Twitterクローンのコードを参考にする場合は、本当に必要な部分を見極めましょう。指示されている機能を従来のタスク管理アプリケーションに追加してください。ユーザ一覧、ユーザ詳細、 Gravatarの表示などは必要ありません。
マイグレーションについて
一対多を実装するため tasks テーブルに user_id カラムを追加します。

既存の「tasks テーブルを作成するマイグレーション」を変更するのではなく、新しく「tasks テーブルに user_id カラムを追加するマイグレーション」を作成して実行すると良いでしょう。

Lesson 13「10. カラムの追加」や「課題：タスク管理アプリケーションにカラムの追加」が参考になります。

「tasks テーブルに user_id カラムを追加するマイグレーション」では外部キー制約も設定します。

このマイグレーションが実行されたとき、既存のタスクがあるとその user_id カラムは NULL となってしまい、外部キー制約に違反するためエラーが発生します。

これを回避するには、このマイグレーションを実行する前に既存のタスクを削除します。 mysql コマンドを使っても良いですが、Tinkerを使うと良いでしょう。

以下の例はローカル（Cloud9上）のアプリケーションに対して作業する場合です。Herokuアプリケーションに対して作業する場合はコマンドの前に heroku run を付けて heroku run php artisan tinker としましょう。

$ php artisan tinker

>>> DB::table('tasks')->delete()
=> (ここに削除された件数が表示されます)
ヒント
マイグレーションを作成し、up()メソッドで、tasksテーブルに、1. user_idカラムを追加し、2. 外部キー制約をつける必要があります。 その際、down()メソッドでは、1. 外部キー制約の削除し、2. user_idカラムを削除するようにしましょう。 up()メソッドとdown()メソッドはちょうど対になりますので、実行順序も重要です。

laravelが外部キー制約を追加する際に、タスク名_カラム名_foreign という名前で外部キー制約を作成しますので、外部キー制約の削除は、以下のようになります。

$table->dropForeign('tasks_user_id_foreign');
あるいは、省略形の

$table->dropForeign(['user_id']);
を使うこともできます。

詳しくは、Laravel 6.xのマニュアルを参照してください。

Lesson 15Chapter 10
フォロー機能
続いて、ユーザがユーザをフォローする／フォローされる機能を構築します。少しむずかしい話になるため、少しずつ理解していってください。

Lesson 15Chapter 10.1
Model
多対多の関係
一対多だけでなく、多対多 の関係もあります。

このチャプターで解説する機能である「フォローしている／フォローされている」の関係は、多対多の関係です。また、課題である「お気に入り機能」も、多対多の関係です。

多対多の関係は、一対多の関係を拡張した関係です。一対多との違いから理解しましょう。

一対多の関係だったUserとMicropostでは、 Userが複数のMicropostを持ち、 Micropostは必ず1つのUserに所属しました。

「フォローしている／フォローされている」の関係では、あるUser が フォローしているUserは複数存在する場合があり、逆にあるUser を フォローしているUserも複数存在する場合があるという、双方に複数相手が存在する可能性のある関係なのです。



この違いは必ず理解してください。一対多と同様に多対多のリソースを扱う方法もしっかりと学んでおく必要があります。多対多の関係が稀な関係だとは思わないでください。一対多と同様、頻繁に出てくる関係です。

多対多では中間テーブルが必要
一対多では、 micropostsテーブルを作成するときにuser_idを付与しました。micropostsテーブルにuser_idを設置することで、 Micropostが所属するUserを特定できたのです。

そして、 belongsToとhasManyのメソッドによって両者をModelファイルで繋げたので、 $user->microposts や $micropost->user が使用可能になったわけです。

多対多では、片方のテーブルにxxxx_idのようなカラムを設置するだけでは実現できません。実現できなくもないですが、カラムの中身が配列になってしまいます。データベースの1つの値が配列になってしまうのは、とても扱いにくく好ましくありません。

そこで、多対多の場合には、中間テーブル を設置するのがもっとも有効な方法です。

中間テーブルとは、 usersやmicropostsのようなメインとなるリソースではなく、その関係を繋げるためだけのテーブルを言います。

上図の中間テーブルでは、フォローする側のUserとフォローされる側のUserの情報をペアとして持っており、たとえば [ 1 | 2 ] となっている中間テーブルのレコードは「id: 1のユーザがid: 2のユーザをフォローしている」ことを意味していると思ってください。

このように中間テーブルを用意することで、フォローしているUserとフォローされているUserの関係を表現できるのです。

一見難しく思えますが、この中間テーブルを利用する方法は今まで使用してきた一対多の関係を拡張した関係になっています。

中間テーブルはUserのフォローの関係を表すだけのテーブルであり、「左側のUser」と「中間テーブルの左側」の関係だけで見ると一対多、同じく「右側のユーザ」と「中間テーブルの右側」の関係も一対多です。

一対多の関係を2つ（上図で言うと左右から）くっつけることで、相手が双方向に複数存在する『多対多』という関係を表現できるようになっているわけです。

マイグレーション
マイグレーションファイルの作成
では、 UserとUserのフォロー関係のレコードを保存する中間テーブルuser_followを作成し、up() にカラムの情報を追記します。念のため id と timestamps() は残していますが、中間テーブルとして重要なのは user_id と follow_id です。

php artisan make:migration create_user_follow_table --create=user_follow
database/migrations/年月日時_create_user_follow_table.phpのup() とdown()

    public function up()
    {
        Schema::create('user_follow', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('follow_id');
            $table->timestamps();

            // 外部キー制約
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('follow_id')->references('id')->on('users')->onDelete('cascade');

            // user_idとfollow_idの組み合わせの重複を許さない
            $table->unique(['user_id', 'follow_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_follow');
    }
follow_idという名前にしていますが、保存する内容はユーザIDです。user_idとカラム名が被ることを避けるため、follow_idにしています。これで「フォローしている／フォローされている」の関係を保存できます。

また、$table->unique(['user_id', 'follow_id']); を追加することでuser_idとfollow_idの組み合わせの重複を許さないようにしています。これは一度保存したフォロー関係を何度も保存しないようにテーブルの制約として入れています。

さらに、onDeleteは参照先のデータが削除されたときにこのテーブルの行をどのように扱うかを指定します。 オプションとして以下の値をセットして、削除後の挙動を制御できます。　

set null: NULLに設定 (IDをNULLに変更します）

no action: なにもしない (存在しないIDが残ります）

cascade: 一緒に消す (このテーブルのデータも一緒に消えます）

restrict: 禁止する (参照先のデータが消せなくなります）
今回は、onDelete('cascade') とすることで、ユーザテーブルのデータが削除されると同時に、それにひもづくフォローテーブルのフォロー、フォロワーのレコードも削除されるようにしました。

マイグレーションの実行
php artisan migrate
マイグレーションが実行済みになったことを php artisan migrate:status コマンドで確認しておきましょう。 MySQL側でテーブルの内容を describe user_follow; で確認しておくのも良いでしょう。

belongsToMany()
中間テーブルのためのModelファイルは不要です。

その代わり、 Userのモデルファイルに多対多の関係を記述します。そのためには belongsToMany メソッドを使用します。

フォロー関係の場合、多対多の関係がどちらもUserに対するものなので、どちらもUserのModelファイルに記述します。microposts() の定義の下に追記してください。

app/User.php（抜粋）

class User extends Authenticatable
{
    // 中略

    /**
     * このユーザが所有する投稿。（ Micropostモデルとの関係を定義）
     */
    public function microposts()
    {
        return $this->hasMany(Micropost::class);
    }

    /**
     * このユーザがフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'user_id', 'follow_id')->withTimestamps();
    }

    /**
     * このユーザをフォロー中のユーザ。（ Userモデルとの関係を定義）
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_follow', 'follow_id', 'user_id')->withTimestamps();
    }
}
これで、一対多のときと同様に $user->followings で $user が フォローしているUser達を取得できます。 $user->followers も同様で $user をフォローしているUser達を取得可能です。

followingsはUser が フォローしているUser達で、 followersはUser を フォローしているUser達です。

（followingsを例にとると）belongsToMany() では、第一引数に得られるModelクラス（User::class) を指定します。

第二引数に中間テーブル（user_follow）を指定します。

第三引数には中間テーブルに保存されている自分のidを示すカラム名（user_id）を指定します。

第四引数には中間テーブルに保存されている関係先のidを示すカラム名（follow_id）を指定します。

followersの場合、第三引数と第四引数が逆になります。つまり、followingsは「user_id のUserは follow_id のUserをフォローしている」ことを表し、followersは「follow_id のUserは user_id のUserからフォローされている」ことを表しています。

なお、 withTimestamps() は中間テーブルにもcreated_atとupdated_atを保存するためのメソッドで、タイムスタンプを管理することができるようになります。

参考: 多対多 - Laravel 6.x
follow(), unfollow()
フォロー／アンフォローできるように follow() とunfollow() メソッドをUserモデルで定義しておきましょう。

app/User.php（抜粋）

class User extends Authenticatable
{
    // 中略

    /**
     * $userIdで指定されたユーザをフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function follow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist || $its_me) {
            // すでにフォローしていれば何もしない
            return false;
        } else {
            // 未フォローであればフォローする
            $this->followings()->attach($userId);
            return true;
        }
    }

    /**
     * $userIdで指定されたユーザをアンフォローする。
     *
     * @param  int  $userId
     * @return bool
     */
    public function unfollow($userId)
    {
        // すでにフォローしているかの確認
        $exist = $this->is_following($userId);
        // 対象が自分自身かどうかの確認
        $its_me = $this->id == $userId;

        if ($exist && !$its_me) {
            // すでにフォローしていればフォローを外す
            $this->followings()->detach($userId);
            return true;
        } else {
            // 未フォローであれば何もしない
            return false;
        }
    }

    /**
     * 指定された $userIdのユーザをこのユーザがフォロー中であるか調べる。フォロー中ならtrueを返す。
     *
     * @param  int  $userId
     * @return bool
     */
    public function is_following($userId)
    {
        // フォロー中ユーザの中に $userIdのものが存在するか
        return $this->followings()->where('follow_id', $userId)->exists();
    }
}
フォロー／アンフォローするときには、以下の2点に注意が必要です。

すでにフォローしているか
対象が自分自身かどうかの確認
これらをしっかり判定してからフォロー／アンフォローを実行しましょう。

フォロー／アンフォローとは、中間テーブルのレコードを保存／削除することです。そのために、あらかじめ用意されたattach() と detach() というメソッドを使用します。

念のため、フォロー／アンフォローに成功すれば return true 、失敗すれば return false を実行するようにしています。今回は true, false の結果を使用していませんが、何かしらの処理で成功失敗を判定したい場合には利用できます。

参考: attach/detach - Laravel 6.x
tinkerでフォロー／アンフォロー
前提としてUserを2人以上作成しておきましょう。1人がもう1人をフォロー／アンフォローするためです。

そのために、あらかじめデータベース上に2人以上のユーザを登録しておいてください。登録がないとfindメソッドを実行してもnullが返ってきてしまいます。

>>> use App\User

// 今回はユーザIDが1と2のユーザを使ってテストしますが、任意のID番号でも問題ありません。
>>> $user1 = User::find(1)
=> App\User {#2871
      // 中略
}
>>> $user2 = User::find(2)
=> App\User {#2864
      // 中略
}

// ユーザ1がユーザ2をフォロー
>>> $user1->follow($user2->id)
=> true

// ユーザ1がフォロー中のユーザの一覧
>>> $user1->followings()->get()
=> Illuminate\Database\Eloquent\Collection {#2865
     all: [
       App\User {#2876
         id: 2,

         // 中略
       },
     ],
   }

// ユーザ1がユーザ2をアンフォロー
>>> $user1->unfollow($user2->id)
=> true

// ユーザ1がフォロー中のユーザの一覧
>>> $user1->followings()->get()
=> Illuminate\Database\Eloquent\Collection {#2880
     all: [],
   }
これでUserのフォロー／アンフォローが自由にできるようになりました。

中間テーブルがどうなっているのか気になれば、 MySQLクライアントで直接レコードを確認してみるのも良いでしょう。

Lesson 15Chapter 10.2
Router
routes/web.phpのauthグループ抜粋

Route::group(['middleware' => ['auth']], function () {
    Route::group(['prefix' => 'users/{id}'], function () {
        Route::post('follow', 'UserFollowController@store')->name('user.follow');
        Route::delete('unfollow', 'UserFollowController@destroy')->name('user.unfollow');
        Route::get('followings', 'UsersController@followings')->name('users.followings');
        Route::get('followers', 'UsersController@followers')->name('users.followers');
    });

    Route::resource('users', 'UsersController', ['only' => ['index', 'show']]);

    Route::resource('microposts', 'MicropostsController', ['only' => ['store', 'destroy']]);
});
authの Route::group の中に ['prefix' => 'users/{id}'] とした Route::group を追加しています。このグループ内のルーティングではURLの最初に /users/{id}/ が付与され、以下のような形となります。

POST /users/{id}/follow
DELETE /users/{id}/unfollow
GET /users/{id}/followings
GET /users/{id}/followers
上記だけではイメージしづらいと思うため、以下にデモサイトのURLを基にした完全なURLを参考情報として記載します。あくまでもURLは参考であるため、一覧以外のURLにはアクセスしても何も起こりません。

例） ユーザID125のユーザの場合

// 125番目のユーザをフォローする
http://laravel-microposts.herokuapp.com/users/125/follow [POST形式]

// 125番目のユーザをアンフォローする
http://laravel-microposts.herokuapp.com/users/125/unfollow [DELETE形式]

// 125番目のユーザがフォローしているユーザ一覧を表示する
http://laravel-microposts.herokuapp.com/users/125/followings [GET形式]

// 125番目のユーザをフォローしているユーザ一覧を表示する
http://laravel-microposts.herokuapp.com/users/125/followers [GET形式]
上記のPOSTとDELETEはフォロー／アンフォローをHTTPで操作可能にするルーティングです。UserFollowControllerは後で新規作成するため、その際に説明します。

そして、 GETの2つはフォローしている人とフォローされている人のUser一覧を表示するルーティングとなります。

Lesson 15Chapter 10.3
UserFollowController@store, destroy
Controller
フォロー機能のためのモデルやルーティングが作成できたため、次はコントローラを作成しましょう。

UsersController.phpやMicropostsController.phpのファイルがすでにありますが、今回は中間テーブルを操作するアクションであるため新しくUserFollowController.phpファイルを作成します。

その中に、フォローするためのstoreメソッドとアンフォローするためのdestroyメソッドを作成することにします。

storeメソッドではUser.phpに定義されているfollowメソッドを使って、ユーザをフォローできるようにします。
destroyメソッドではUser.phpに定義されているunfollowメソッドを使って、ユーザをアンフォローできるようにします。
php artisan make:controller UserFollowController
app/Http/Controllers/UserFollowController.phpのstoreとdestroy

namespace App\Http\Controllers;

class UserFollowController extends Controller
{
    /**
     * ユーザをフォローするアクション。
     *
     * @param  $id  相手ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function store($id)
    {
        // 認証済みユーザ（閲覧者）が、 idのユーザをフォローする
        \Auth::user()->follow($id);
        // 前のURLへリダイレクトさせる
        return back();
    }

    /**
     * ユーザをアンフォローするアクション。
     *
     * @param  $id  相手ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 認証済みユーザ（閲覧者）が、 idのユーザをアンフォローする
        \Auth::user()->unfollow($id);
        // 前のURLへリダイレクトさせる
        return back();
    }
}
フォロー／フォロワー数のカウント
投稿数に加えて、フォロー数とフォロワー数も取得したいため、以下のように loadCount に指定するリレーション名として 'followings' と 'followers' も追加します。

app/User.php（抜粋）

    /**
     * このユーザに関係するモデルの件数をロードする。
     */
    public function loadRelationshipCounts()
    {
        $this->loadCount(['microposts', 'followings', 'followers']);
    }
View
フォロー／アンフォローボタン
フォロー／アンフォローボタンを表示する部分を共通のViewとして用意しましょう。

resources/views/user_follow/follow_button.blade.php

@if (Auth::id() != $user->id)
    @if (Auth::user()->is_following($user->id))
        {{-- アンフォローボタンのフォーム --}}
        {!! Form::open(['route' => ['user.unfollow', $user->id], 'method' => 'delete']) !!}
            {!! Form::submit('Unfollow', ['class' => "btn btn-danger btn-block"]) !!}
        {!! Form::close() !!}
    @else
        {{-- フォローボタンのフォーム --}}
        {!! Form::open(['route' => ['user.follow', $user->id]]) !!}
            {!! Form::submit('Follow', ['class' => "btn btn-primary btn-block"]) !!}
        {!! Form::close() !!}
    @endif
@endif
これを @include すると、フォローボタンを表示できます。すでにフォローしている場合にはアンフォローボタンになります。また、自分自身の場合には表示されません。

フォロー／アンフォローボタンの設置
users.showにボタンを設置しましょう。

resources/views/users/show.blade.php抜粋

        <aside class="col-sm-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $user->name }}</h3>
                </div>
                <div class="card-body">
                    {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
                    <img class="rounded img-fluid" src="{{ Gravatar::get($user->email, ['size' => 500]) }}" alt="">
                </div>
            </div>
            {{-- フォロー／アンフォローボタン --}}
            @include('user_follow.follow_button')
        </aside>
Lesson 15Chapter 10.4
UsersController@followings, followers
Controller
こちらはUserの情報を取得できれば良いため、UsersController へ記述します。

app/Http/Controllers/UsersController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;

class UsersController extends Controller
{
    // 中略

    /**
     * ユーザのフォロー一覧ページを表示するアクション。
     *
     * @param  $id  ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function followings($id)
    {
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // 関係するモデルの件数をロード
        $user->loadRelationshipCounts();

        // ユーザのフォロー一覧を取得
        $followings = $user->followings()->paginate(10);

        // フォロー一覧ビューでそれらを表示
        return view('users.followings', [
            'user' => $user,
            'users' => $followings,
        ]);
    }

    /**
     * ユーザのフォロワー一覧ページを表示するアクション。
     *
     * @param  $id  ユーザのid
     * @return \Illuminate\Http\Response
     */
    public function followers($id)
    {
        // idの値でユーザを検索して取得
        $user = User::findOrFail($id);

        // 関係するモデルの件数をロード
        $user->loadRelationshipCounts();

        // ユーザのフォロワー一覧を取得
        $followers = $user->followers()->paginate(10);

        // フォロワー一覧ビューでそれらを表示
        return view('users.followers', [
            'user' => $user,
            'users' => $followers,
        ]);
    }
}
View
ユーザの詳細情報表示（users.show）、自分がフォローしているUser一覧（users.followings）、自分をフォローしているUser一覧（users.followers）には共通している部分があるため、まずは共通部分（ユーザ名とGravatarの表示部分、ナビゲーションタブの部分）を切り出しましょう。

また、ナビゲーションタブの方にfollowingsとfollowersのリンクを追記します。

resources/views/users/card.blade.php

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $user->name }}</h3>
    </div>
    <div class="card-body">
        {{-- ユーザのメールアドレスをもとにGravatarを取得して表示 --}}
        <img class="rounded img-fluid" src="{{ Gravatar::get($user->email, ['size' => 500]) }}" alt="">
    </div>
</div>
{{-- フォロー／アンフォローボタン --}}
@include('user_follow.follow_button')
resources/views/users/navtabs.blade.php

<ul class="nav nav-tabs nav-justified mb-3">
    {{-- ユーザ詳細タブ --}}
    <li class="nav-item">
        <a href="{{ route('users.show', ['user' => $user->id]) }}" class="nav-link {{ Request::routeIs('users.show') ? 'active' : '' }}">
            TimeLine
            <span class="badge badge-secondary">{{ $user->microposts_count }}</span>
        </a>
    </li>
    {{-- フォロー一覧タブ --}}
    <li class="nav-item">
        <a href="{{ route('users.followings', ['id' => $user->id]) }}" class="nav-link {{ Request::routeIs('users.followings') ? 'active' : '' }}">
            Followings
            <span class="badge badge-secondary">{{ $user->followings_count }}</span>
        </a>
    </li>
    {{-- フォロワー一覧タブ --}}
    <li class="nav-item">
        <a href="{{ route('users.followers', ['id' => $user->id]) }}" class="nav-link {{ Request::routeIs('users.followers') ? 'active' : '' }}">
            Followers
            <span class="badge badge-secondary">{{ $user->followers_count }}</span>
        </a>
    </li>
</ul>