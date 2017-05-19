<?php

namespace App\Console\Commands;
require_once app_path('Libraries/GoogleAPIs/').'GmailAPI.class.php';

use Illuminate\Console\Command;
use DB;

class Fetch_gmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:Fetch_gmail {gmail} {hour}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch gmail';

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
        //获取参数
        $gmail = $this->argument('gmail');
        $hour = $this->argument('hour');

        $emailInfo = DB::table('msg_gmailtoken')
            ->where('gmail',$gmail)
            ->where('is_delete',0)
            ->first();
        $tokenJson = json_encode($emailInfo);

        $gmailObj = new \GmailAPI($emailInfo->platform);
        $gmailObj->setToken($tokenJson);
        $gmailObj->setQueryTime($hour);
        $msgIds = $gmailObj->listMessageIds($gmail);
        $totalCount = count($msgIds);

        $this->info("Email:{$gmail},total {$totalCount} email");
        $this->line('Begin fetch email ,'.date('Y-m-d'));
        
        $i = null;
        foreach ($msgIds as $msgId) {
            $i++;
            $this->line("fetch {$i}/{$totalCount} email");
            $msg_res = $gmailObj->getMessage($gmail, $msgId);
            dump($msg_res);
        }
    }
}
