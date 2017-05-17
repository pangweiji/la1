<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SendEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SendEmails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send email';

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
        $conn = new AMQPConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_LOGIN'), env('RABBITMQ_PASSWORD'), env('RABBITMQ_MESSAGE_VHOST'));
        $channel = $conn->channel();

        //交换机
        $channel -> exchange_declare('message_exchenge', 'fanout', false, true, false);
        //声明queue
        $channel -> queue_declare('message_queue', false, true, false, false);
        $channel->queue_bind('message_queue', 'message_exchenge');
        $tag = time().'comsumer';

        $this->line('[*] Waiting for messages. To exit press CTRL+C');

        $callback = function ($msg) {

            //内容
            $ms = json_decode($msg->body, true);
            extract($ms);
            $status = 2;

            $emailInfo = DB::table('email_info')
                ->where('email', $sendid)
                ->where('is_delete',0)
                ->first();

            if (empty($emailInfo)) {
                $status = 4;
            } else {
                $mail = new \PHPMailer;
                $mail->isSMTP();
                $mail->SMTPDebug = 2;
                $mail->Host = 'smtp-mail.outlook.com';
                $mail->Port = '587';
                $mail->SMTPAuth = true;
                $mail->Username = $emailInfo->email;
                $mail->Password = $emailInfo->password;
                //发件人
                $mail->setFrom($emailInfo->email, $emailInfo->account);
                //收件人
                $mail->addAddress($receiveid, ' ');
                //主题
                $mail->Subject = $subject;
                //内容
                $mail->Body = $msgbody;
                //嵌入html
                $mail->msgHTML(file_get_contents(storage_path().$replyhtml), storage_path().'/reply_html');

                if (!$mail->send()) {
                    echo "Mailer Error: " . $mail->ErrorInfo;
                    $status = 4;
                } else {
                    echo "Message sent!";
                } 
            } 
            DB::table('message')
                ->where('id', $mid)
                ->update(['status' => $status]);  
            //回馈
            $msg ->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $channel->basic_consume('message_queue', $tag, false, false, false, false, $callback);
        while(count($channel->callbacks)){
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}
