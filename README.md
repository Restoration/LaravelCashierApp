# LaravelCashierApp

The test application for using Laravel Cashier.  
[Laravel Cashier](https://laravel.com/docs/5.7/billing)

## Setup
```
$ composer require "laravel/cashier"
```

```
$ vim database/migrations/XXXX_XX_XX_XXXXXX_create_users_table.php
```

Change the up() function as follows.
```PHP
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->collation('utf8mb4_bin');
            $table->string('card_brand')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });
        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name');
            $table->string('stripe_id')->collation('utf8mb4_bin');
            $table->string('stripe_plan');
            $table->integer('quantity');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }
```

```
$ vim app/User.php
```

```PHP
<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Notifiable;
    use Billable;

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
}
```

Login your stripe's dashboard and get your api keys.   
[API key](https://dashboard.stripe.com/account/apikeys)

In this time, create test application, so check test mode.  

Then, add follow this line to  .env file.
```
$ vim .env
```

```
STRIPE_KEY= <Your stripe key>
STRIPE_SECRET= <Your seacret key>
`


## Single payment system

```
$ php artisan make:controller HomeController
```

```
Route::get('/', 'HomeController@index')->name('home');
Route::post('/charge', 'HomeController@charge');
```


```
$ vim app/Http/Controllers/HomeController.php
```
```PHP
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;

class HomeController extends Controller
{
    //
    public function index()
    {
        return view('welcome');
    }
    // Single payment
    public function charge(Request $request)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $customer = Customer::create(array(
                'email' => $request->stripeEmail,
                'source' => $request->stripeToken
            ));

            $charge = Charge::create(array(
                'customer' => $customer->id,
                'amount' => 1000,
                'currency' => 'jpy'
            ));

            return back();
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }
}
```

```
$ vim  resouces/views/welcome.blade.php
```

```PHP
<div class="content">
	<div class="title m-b-md">
        	Laravel Cashier App
       	</div>
        <form action="{{ asset('charge') }}" method="POST">
        	{{ csrf_field() }}
                <script
                	src="https://checkout.stripe.com/checkout.js" class="stripe-button"
                        data-key="{{ env('STRIPE_KEY') }}"
                        data-amount="1000"
                        data-name="Stripe Demo"
                        data-label="Payment"
                        data-description="Online course about integrating Stripe"
                        data-image="https://stripe.com/img/documentation/checkout/marketplace.png"
                        data-locale="auto"
                        data-currency="JPY">
                </script>
        </form>
 </div>
```
