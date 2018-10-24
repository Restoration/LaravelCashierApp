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
