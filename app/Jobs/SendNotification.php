<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mail;
use App\Models\MainTeam;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mail = new PHPMailer(true);

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        //Server settings
        $mail->SMTPDebug = 0;                                 // Enable verbose debug output
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'thieuhao2525@gmail.com';                 // SMTP username
        $mail->Password = 'smnhhmadztqrkspw';                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('thieuhao2525@gmail.com', 'Mailer Test');
         $mail->addAddress($this->input['email'], $this->input['name']);     // nguyenthieupro93@gmail.com
        if(isset($this->input['email_arr'])){
            foreach($this->input['email_arr'] as $email){
                $mail->addAddress($email);
            }
        }
        // $mail->addReplyTo('info@example.com', 'Information');
//         $mail->addCC('nguyenthieupro93@gmail.com');
        // $mail->addBCC('bcc@example.com');

        //Attachments
//        $mail->addAttachment('invoice9267054355559.pdf');        // Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $this->input['subject']; //
        $mail->Body = $this->input['message'];
        $mail->AltBody = '';

        $mail->send();
    }
}
