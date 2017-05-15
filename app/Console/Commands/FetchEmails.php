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
        //获取参数
        $account = $this->argument('account');
        //$email = $this->argument('email');
        $day = $this->argument('day');  //天
        $date = date('Y-m-d', time() - 86400);

        //根据帐号和email获取信息
        $emailInfo = DB::table('email_info')
            ->where('account', $account)
            ->where('is_delete', 0)
            ->first();

        if (empty($emailInfo)) {
            $this->error('EmailInfo is empty');
        }

        $email = $emailInfo->email;
        $password = $emailInfo->password;

        $this->info('fetch email ....');
        $this->line("email:{$email}  after:{$date}");

        switch (explode('@', $email)[1]) {
            case 'hotmail.com':
            case 'outlook.com':
                $addr = 'imap-mail.outlook.com:993/imap/ssl/novalidate-cert';
                break;
            default:
                $this->error('email error!');
                exit;
                break;
        }

        $mailbox = new ImapMailbox('{'.$addr.'}INBOX', $email, $password);

        //$mailsIds = $mailbox->searchMailbox('SINCE "'. $date .'"');
        $mailsIds = $mailbox->searchMailbox('ALL');

        if(!$mailsIds) {
            $this->error('Mailbox is empty');
        }
        $allEmail = count($mailsIds);
        $this->info('Has '.$allEmail.' email.');

        $mailInfo = array();
        $i = 1;
        foreach ($mailsIds as $mailsId) {
            $mailObj = $mailbox->getMail($mailsId);
            $mailInfo[$mailsId]['subject']      = $mailObj->subject;
            $mailInfo[$mailsId]['fromName']     = $mailObj->fromName;
            $mailInfo[$mailsId]['fromAddress']  = $mailObj->fromAddress;
            $mailInfo[$mailsId]['to']           = $mailObj->to;
            $mailInfo[$mailsId]['toString']     = $mailObj->toString;
            $mailInfo[$mailsId]['cc']           = $mailObj->cc;
            $mailInfo[$mailsId]['bcc']          = $mailObj->bcc;
            $mailInfo[$mailsId]['replyTo']      = $mailObj->replyTo;
            $mailInfo[$mailsId]['content']      = $mailObj->textPlain;

            $this->line('fetch '.$i.'/'.$allEmail.' '.$mailObj->fromAddress);
            $i++;
        }
        dd($mailInfo);
        $error = imap_last_error();
        if ($error) {
            $this->error('error!');
            $this->error(imap_last_error());
            exit;
        }

        //入库
        $result = DB::table('email_info')
            ->insert(
                []
            );
    }
}
