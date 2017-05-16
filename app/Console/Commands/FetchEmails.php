<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpImap\Mailbox as ImapMailbox;
use PhpImap\IncomingMail;
use PhpImap\IncomingMailAttachment;
use DB;

class FetchEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:fetchemails {account} {day}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fetch email';

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
        ini_set('max_execution_time', '0');
        //获取参数
        $account = $this->argument('account');
        //$email = $this->argument('email');
        $day = $this->argument('day');  //天
        $date = date('d M Y', time() - 86400 * $day);

        //根据帐号和email获取信息
        $emailInfo = DB::table('email_info')
            ->where('account', $account)
            ->where('is_delete', 0)
            ->first();

        if (empty($emailInfo)) {
            $this->error('EmailInfo is empty');
        }

        $platform = $emailInfo->platform;
        $email = $emailInfo->email;
        $password = $emailInfo->password;
        $uploadPath = '/uploads/email_attachment/' . $account . '/' . date('Y-m-d') . '/';
        $attachmentPath = storage_path(). $uploadPath;
        
        if (!file_exists($attachmentPath)) {
            mkdir($attachmentPath, 0777, true);
        }

        $this->info('fetch email ....');
        $this->line("email:{$email}  after:".date('Y-m-d', time() - 86400 * $day));

        $serverEncoding = 'UTF-8';
        switch (explode('@', $email)[1]) {
            case 'hotmail.com':
            case 'outlook.com':
                $addr = 'imap-mail.outlook.com:993/imap/ssl/novalidate-cert';
                $serverEncoding = 'US-ASCII';
                break;
            default:
                $this->error('email error!');
                exit;
                break;
        }

        $mailbox = new ImapMailbox('{'.$addr.'}INBOX', $email, $password, $attachmentPath, $serverEncoding);

        $mailsIds = $mailbox->searchMailbox('SINCE "'. $date .'"');
        //$mailsIds = $mailbox->searchMailbox('ALL');
        //$mailsIds = $mailbox->searchMailbox('ALL');

        if(!$mailsIds) {
            $this->error('Mailbox is empty');
            exit();
        }

        $allEmail = count($mailsIds);
        $this->info('Has '.$allEmail.' email.');

        $mailInfo = array();
        $i = 1;
        $insertData = array();
        foreach ($mailsIds as $mailsId) {
            $mailObj = $mailbox->getMail($mailsId);
            $insertData[$mailsId] = [
                'message_id' => $mailsId, 
                'receiveid' => $mailObj->fromAddress, 
                'receivename' => $mailObj->fromName, 
                'sendid' => $mailObj->to, 
                'sendname' => $mailObj->toString, 
                'subject' => $mailObj->subject, 
                'status' => 0,
                'sendtime' => strtotime($mailObj->date) + 8*3600*24,
                'account' => $account, 
                'receivetimestamp' => time(), 
                'plaincontent' => $mailObj->textPlain
            ];

            $this->line('fetch '.$i.'/'.$allEmail.' '.$mailObj->fromAddress);
            $i++;

            $hasAttachment = $mailObj->getAttachments();
            if (!empty($hasAttachment)) {
                $insertData[$mailsId]['hasAttachment'] = 1;
                $this->line('保存附件中...');

                //保存附件信息
                $atArr = array();
                foreach ($hasAttachment as $ha) {
                    $atArr[] = [
                        'platform' => $platform,
                        'mailbox'  => $email,
                        'messageid'=> $mailsId,
                        'attachmentName' => $ha->name,
                        'attachmentPath' => $uploadPath . basename($ha->filePath)
                    ];

                    //插入
                    $result1 = DB::table('msg_fetchattachment')
                        ->insert($atArr);
                    if (!$result1) {
                        $this->error('save attachment fail!');
                    } else {
                        $this->info('save attachment success!');
                    }
                }
            }
        }

        if ($error = imap_last_error()) {
            $this->error('error!');
            $this->error($error);
            exit;
        }
        dd($insertData);
        //入库
        $result2 = DB::table('message')
            ->insert($insertData);
        if ($result2) {
            $this->info('save email success!');
        } else {
            $this->error('save email fail!');
        }
    }
}
