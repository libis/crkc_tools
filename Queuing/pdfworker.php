<?php
/**
 * User: NaeemM
 * Date: 13/03/14
 *
 * This script generates pdfs of contents received through rabbitmq queues
 */

require_once __DIR__ . '/queue_server_conf.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/phpmailer/PHPMailerAutoload.php';

use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection($co_rabbit_mq_server, $co_rabbit_mq_port, $co_rabbit_mq_uid, $co_rabbit_mq_pwd);
$channel = $connection->channel();

$channel->queue_declare($co_queue_name, false, false, false, false);

echo " [*] Waiting for requests to generate pdfs. To exit press CTRL+C", "\n";

$callback = function($msg) {
    require_once __DIR__ . '/pdf_worker_conf.php';

    $microtime = round(microtime(true) * 1000);
    $va_base_directory = dirname(__FILE__)."/pdfdownload/";
    $va_request_directory = $va_base_directory.$microtime;

    if (!file_exists($va_request_directory)) {
        mkdir($va_request_directory);
    }

    $va_content_file = $va_request_directory."/".date("m_d_y")."_".$microtime.".html";
    $va_pdf_file = $va_request_directory."/".date("m_d_y")."_".$microtime.".pdf";
    $va_pdf_download_link = $co_base_url.date("m_d_y")."_".$microtime.".pdf";

    $va_pdf_message = json_decode($msg->body);
    $va_pdf_contents = array_key_exists('pdf_contents', $va_pdf_message) ? $va_pdf_message->pdf_contents : '' ;

    if (array_key_exists('pdf_settings', $va_pdf_message))
        $va_pdf_settings = $va_pdf_message->pdf_settings;

    file_put_contents($va_content_file, print_r($va_pdf_contents,true));

    if(file_exists($va_content_file)){
        $va_pdf_orientation = isset($va_pdf_settings->orientation) ? $va_pdf_settings->orientation : "portrait" ;
        $va_pdf_paper_size = isset($va_pdf_settings->page_format) ? $va_pdf_settings->page_format : "A4" ;

        $va_command = $co_pdf_tool_path.'/wkhtmltopdf'.' '
            .'-O '.$va_pdf_orientation.' '
            .'-s '.$va_pdf_paper_size.' '
            .$va_content_file.' '
            .$va_pdf_file;

        system($va_command, $va_ret_val);    //execute pdf generation command

        $endtime = round(microtime(true) * 1000);

        if(isset($va_pdf_message->user_info->email)&& filter_var($va_pdf_message->user_info->email, FILTER_VALIDATE_EMAIL)){

            //send email to inform the user about success or failure. In case of success send the corresponding pdf download link.
            $mail = new PHPMailer;
            $mail->isSMTP();                                      // Set mailer to use SMTP

            $mail->Host = $co_mail_smtp_server;
            $mail->From = $co_mail_from_email;
            $mail->FromName = $co_mail_from_name;

            $va_user_name = isset($va_pdf_message->user_info->name)? $va_pdf_message->user_info->name : '';

            $mail->addAddress("naeemmuhammad@gmail.com", $va_user_name);  // Add a recipient

            $mail->isHTML(true);
            $mail->Subject = "Collective Access Record Search PDF";

            $va_email_message = ($va_ret_val === 0)
                ?
                "Dear ".$va_user_name. ",<br><br>".
                "Requested pdf is available at:<br>".
                $va_pdf_download_link."<br><br>".
                "Total pdf generation time (seconds): ".(int)($endtime-$microtime)/1000
                :
                "Error in generating pdf, please try again";

            $mail->Body    = $va_email_message;

            echo "\n".$va_pdf_download_link."\n";
            echo "\nTotal pdf generation time (seconds): ".(($endtime-$microtime)/1000)."\n";

            if(!$mail->send()) {
                echo "Message could not be sent."."\n";;
                echo "Mailer Error: " . $mail->ErrorInfo."\n";
            }
            else
                echo "Message has been sent to:".$va_pdf_message->user_info->email."\n";
        }
    }
    else
        echo "Error in writing contents in temp file"."\n";
};

$channel->basic_consume($co_queue_name, '', false, true, false, false, $callback);


while(count($channel->callbacks)) {
    $channel->wait();
}