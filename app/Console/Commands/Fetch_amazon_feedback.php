<?php

namespace App\Console\Commands;
require_once app_path('Libraries/AmazonApi/').'Feedback.class.php';

use Illuminate\Console\Command;

class Fetch_amazon_feedback extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Fetch_amazon_feedback {tokenkey} {hour}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        error_reporting(E_ALL);
        set_time_limit(0);
        $sleepReqTime = 100;
        $curTime = date("Y-m-d H:i:s");

        //获取参数
        $tokenkey = $this->argument('tokenkey');
        $hour = $this->argument('hour');

        $this->info("--------token key[{$tokenkey}]--------");
        $fdObj = new \Feedback($tokenkey);
        $fdObj->init($sleepReqTime, $curTime);

        //发送请求时间段报告请求(此步很重要，相当于申请，否则将不会返回最新的信息,缓存时间为等待报告为DONE)
        $reportRequestInfo = $fdObj->invokeGetRequestReport($fdObj->service, $hour);
        $this->info("--------ReportRequestId:{$reportRequestInfo['ReportRequestId']}");
        $this->info("--------ReportType:     {$reportRequestInfo['ReportType']}");
        $this->info("--------StartDate:      {$reportRequestInfo['StartDate']}");
        $this->info("--------EndDate:        {$reportRequestInfo['EndDate']}");
        $this->info("--------SubmittedDate:  {$reportRequestInfo['SubmittedDate']}");
        $this->info("--------ReportProcessingStatus:{$reportRequestInfo['ReportProcessingStatus']}");
        $this->info("--------We need to delay {$sleepReqTime} seconds before initiation request");
        sleep($sleepReqTime);

        //拉取评价请求
        $fdObj->fetchFeedbackRequest($reportRequestInfo, $hour);
    }
}
