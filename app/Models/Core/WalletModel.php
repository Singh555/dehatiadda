<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Core;
use Log;
use DB;
/**
 * Description of WalletModel
 *
 * @author singh
 */
class WalletModel {
    //put your code here
    
    // credit in main wallet
    public static function creditInMainWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert   =   DB::table('wallet_txn')->insert(['customer_id'=>$customer_id,'txn_customer_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'CREDIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'reference_no'=>$order_id,'reference_with'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::mainWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    // Debit from main wallet
    public static function debitFromMainWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert = DB::table('wallet_txn')->insert(['customer_id'=>$customer_id,'txn_customer_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'DEBIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'reference_no'=>$order_id,'reference_with'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::mainWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    
    // update balance in customer/users table
    protected static function mainWalletBalanceUpdateInUsersTbl($customer_id, $balance){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with member $customer_id with wallet balance $balance");
        
        try {
            return DB::table('customers')->where('id',$customer_id)->update(['wallet_balance'=>$balance]);
            
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;
    }
    
    ###################################################################################################################################################################################################################
    
     // credit in advance wallet
    public static function creditInAdvanceWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert   =   DB::table('advance_wallet_txn')->insert(['user_id'=>$customer_id,'txn_user_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'CREDIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'order_id'=>$order_id,'order_type'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::advanceWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    // Debit from advance wallet
    public static function debitFromAdvanceWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert = DB::table('advance_wallet_txn')->insert(['user_id'=>$customer_id,'txn_user_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'DEBIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'order_id'=>$order_id,'order_type'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::advanceWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    
    // update balance in customer/users table
    protected static function advanceWalletBalanceUpdateInUsersTbl($customer_id, $balance){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with member $customer_id with wallet balance $balance");
        
        try {
            return DB::table('users')->where('id',$customer_id)->update(['advance_wallet'=>$balance]);
            
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // credit in shopping wallet
    public static function creditInShoppingWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert   =   DB::table('swallet_txn')->insert(['user_id'=>$customer_id,'txn_user_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'CREDIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'order_id'=>$order_id,'order_type'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
            return self::shoppingWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    // Debit from shopping wallet
    public static function debitFromShoppingWallet($customer_id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with $customer_id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert = DB::table('swallet_txn')->insert(['user_id'=>$customer_id,'txn_user_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'DEBIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'order_id'=>$order_id,'order_type'=>$order_type,'created_by'=>$user, 'created_at'=>$date]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::shoppingWalletBalanceUpdateInUsersTbl($customer_id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;  
    }
    
    // update balance in customer/users table
    protected static function shoppingWalletBalanceUpdateInUsersTbl($customer_id, $balance){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with member $customer_id with wallet balance $balance");
        
        try {
            return DB::table('users')->where('id',$customer_id)->update(['s_wallet'=>$balance]);
            
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;
    }
    
    
    // credit in shop wallet in shops_walllet_txn
    public static function creditInShopWallet($id, $txn_amount, $balance_after, $txn_desc, $order_id, $order_type, $txn_user_id = 'admin'){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with Shop $id with amount $txn_amount, balance after $balance_after");
        $user='admin';
        if(isset(auth()->user()->id) and !empty(auth()->user()->id)){
            $user = auth()->user()->id;
        }
        $date = date('Y-m-d h:i:s');
        Log::debug(__CLASS__." :: ".__FUNCTION__." user found as $user");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn description $txn_desc");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping ID $order_id");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn mapping type $order_type");
        Log::debug(__CLASS__." :: ".__FUNCTION__." txn with customer id $txn_user_id");
        try {
            $insert   =   DB::table('shops_wallet_txn')->insert(['shop_id'=>$id,'txn_user_id'=>$txn_user_id,'txn_amount'=>$txn_amount,'txn_type'=>'CREDIT','txn_desc'=>$txn_desc,'txn_date'=>$date,'balance_after'=>$balance_after,'order_id'=>$order_id,'order_type'=>$order_type, 'created_at' => $date,'created_by'=>$user]);
            if($insert){
                Log::debug(__CLASS__." :: ".__FUNCTION__." txn saved lets update the balance in users table");
                return self::shopWalletBalanceUpdateInShopsTbl($id, $balance_after);
            }
             
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;
    }
    
    // update balance in shops table
    protected static function shopWalletBalanceUpdateInShopsTbl($id, $balance){
        Log::debug(__CLASS__." :: ".__FUNCTION__." started with Shop $id with wallet balance $balance");
        
        try {
            return DB::table('shops')->where('id', $id)->update(['wallet_balance'=>$balance]);
            
        } catch (Exception $exc) {
            Log::error(__CLASS__." :: ".__FUNCTION__." exception while processing ".$exc->getMessage());
        }
        return false;
    }
    
    
}
