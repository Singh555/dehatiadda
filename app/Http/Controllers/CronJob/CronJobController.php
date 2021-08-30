<?php

namespace App\Http\Controllers\CronJob;

use App\Http\Controllers\Controller;
use App\Models\Cron\CronJobModel;
//use Illuminate\Support\Facades\DB;
//use Log;

/**
 * Description of CronJobController
 *
 * @author ajit
 */
class CronJobController extends Controller {
    //put your code here
    
    public function levelIncomeClosing(){
        CronJobModel::levelIncomeClosing();
    }





    // Razor Pay webhook handler (this will handle webhook calls from razor pay)
    public function razorPayWebHookHandler(){
        CronJobModel::razorPayWebHookHandler();
    }

    //Check for pending order
    public function checkForPendingOrders(){
    	CronJobModel::checkForPendingOrders();
    }

}
