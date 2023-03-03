<?php

namespace App\Controllers;

use Exception;
use \Twig\Environment as Twig;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class ContactController
{

    public function showContactForm(Twig $twig)
    {
        echo $twig->render('contact.twig', ['page' => 'Contact']);
    }

    public function sendMessage(Twig $twig, PHPMailer $mail)
    {
        try{ 
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = $_ENV['SMTP_HOST'];                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;     
            $mail->SMTPSecure = "tls";                              //Enable SMTP authentication
            $mail->Username   = $_ENV['SMTP_USERNAME'];                     //SMTP username
            $mail->Password   = $_ENV['SMTP_PASSWORD'];                               //SMTP password
            $mail->Port       = $_ENV['SMTP_PORT'];

            $mail->setFrom('florianpohu49@gmail.com', 'Florian Pohu');
            $mail->addAddress('florianpohu49@gmail.com', 'Florian Pohu'); 
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Here is the subject';
            $mail->Body    = $_POST['comment'];
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e){
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}