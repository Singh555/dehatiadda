<?php

use Illuminate\Database\Seeder;

class PaymentMethodsDetailTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('payment_methods_detail')->delete();
        
        \DB::table('payment_methods_detail')->insert(array (
            0 => 
            array (
                'id' => 3,
                'payment_methods_id' => 1,
                'key' => 'merchant_id',
                'value' => '',
            ),
            1 => 
            array (
                'id' => 4,
                'payment_methods_id' => 1,
                'key' => 'public_key',
                'value' => '',
            ),
            2 => 
            array (
                'id' => 5,
                'payment_methods_id' => 1,
                'key' => 'private_key',
                'value' => '',
            ),
            3 => 
            array (
                'id' => 9,
                'payment_methods_id' => 2,
                'key' => 'secret_key',
                'value' => '',
            ),
            4 => 
            array (
                'id' => 10,
                'payment_methods_id' => 2,
                'key' => 'publishable_key',
                'value' => '',
            ),
            // 5 => 
            // array (
            //     'id' => 15,
            //     'payment_methods_id' => 3,
            //     'key' => 'id',
            //     'value' => '',
            // ),
            // 6 => 
            // array (
            //     'id' => 18,
            //     'payment_methods_id' => 3,
            //     'key' => 'payment_currency',
            //     'value' => 'USD',
            // ),
            7 => 
            array (
                'id' => 21,
                'payment_methods_id' => 5,
                'key' => 'api_key',
                'value' => '',
            ),
            8 => 
            array (
                'id' => 22,
                'payment_methods_id' => 5,
                'key' => 'auth_token',
                'value' => '',
            ),
            9 => 
            array (
                'id' => 23,
                'payment_methods_id' => 5,
                'key' => 'client_id',
                'value' => '',
            ),
            10 => 
            array (
                'id' => 24,
                'payment_methods_id' => 5,
                'key' => 'client_secret',
                'value' => '',
            ),
            11 => 
            array (
                'id' => 32,
                'payment_methods_id' => 6,
                'key' => 'userid',
                'value' => '',
            ),
            12 => 
            array (
                'id' => 33,
                'payment_methods_id' => 6,
                'key' => 'password',
                'value' => '',
            ),
            13 => 
            array (
                'id' => 34,
                'payment_methods_id' => 6,
                'key' => 'entityid',
                'value' => '',
            ),
            14 => 
            array (
                'id' => 35,
                'payment_methods_id' => 7,
                'key' => 'RAZORPAY_KEY',
                'value' => '',
            ),
            15 => 
            array (
                'id' => 36,
                'payment_methods_id' => 7,
                'key' => 'RAZORPAY_SECRET',
                'value' => '',
            ),
            16 => 
            array (
                'id' => 37,
                'payment_methods_id' => 8,
                'key' => 'paytm_mid',
                'value' => '',
            ),
            17 => 
            array (
                'id' => 39,
                'payment_methods_id' => 8,
                'key' => 'paytm_key',
                'value' => 'w#',
            ),
            18 => 
            array (
                'id' => 40,
                'payment_methods_id' => 8,
                'key' => 'current_domain_name',
                'value' => '',
            ),
            19 => 
            array (
                'id' => 41,
                'payment_methods_id' => 9,
                'key' => 'account_name',
                'value' => '',
            ),
            20 => 
            array (
                'id' => 42,
                'payment_methods_id' => 9,
                'key' => 'account_number',
                'value' => '',
            ),
            21 => 
            array (
                'id' => 43,
                'payment_methods_id' => 9,
                'key' => 'bank_name',
                'value' => '',
            ),
            22 => 
            array (
                'id' => 44,
                'payment_methods_id' => 9,
                'key' => 'short_code',
                'value' => '',
            ),
            23 => 
            array (
                'id' => 45,
                'payment_methods_id' => 9,
                'key' => 'iban',
                'value' => '',
            ),
            24 => 
            array (
                'id' => 46,
                'payment_methods_id' => 9,
                'key' => 'swift',
                'value' => '',
            ),
            25 => 
            array (
                'id' => 47,
                'payment_methods_id' => 10,
                'key' => 'secret_key',
                'value' => '',
            ),
            26 => 
            array (
                'id' => 48,
                'payment_methods_id' => 10,
                'key' => 'public_key',
                'value' => '',
            ),
            28 => 
            array (
                'id' => 49,
                'payment_methods_id' => 11,
                'key' => 'merchant_id',
                'value' => '',
            ),
            29 => 
            array (
                'id' => 50,
                'payment_methods_id' => 11,
                'key' => 'server_key',
                'value' => '',
            ),
            30 => 
            array (
                'id' => 51,
                'payment_methods_id' => 11,
                'key' => 'client_key',
                'value' => '',
            ),
        ));
        
        
    }
}