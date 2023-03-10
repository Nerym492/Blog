<?php

namespace App\Controllers;

use Exception;
use \Twig\Environment as Twig;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Summary of FormController
 * Forms management
 */
class FormController
{
    /**
     * Summary of checkContactForm
     * Check if the submitted form is valid
     * @param Twig $twig
     * @return void
     */
    public function checkContactForm(Twig $twig): void
    {

        $form_errors = [];
        $is_valid = true;

        // 'htmlName' => 'regexPattern'
        $patterns = [
            'fullName' => '/^([A-z]){3,25}\s{1}([A-z]){3,25}$/',
            'mail' => '/^([a-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/'
        ];

        //Check form data
        foreach ($patterns as $fieldName => $pattern) {
            //We add the name of the fields and their value in this array
            $form_errors[] = [$fieldName => $_POST[$fieldName]];
            //Check the patterns
            if (!preg_match($pattern, $_POST[$fieldName]) && $is_valid) {
                $is_valid = false;
            }
        }

        //We add the comment data
        $form_errors[] = ['comment' => $_POST['comment']];

        //We check if the field is not empty only if the form is still valid
        if (empty($_POST['comment']) && $is_valid){
            $is_valid = false;
        }

        //If the form is valid, we clear the array form_errors
        if ($is_valid) {
            $form_errors = [];
            $mail = new PHPMailer(true);
            $this->sendContactForm($twig, $mail);
        }

        //Displays the home page with errors if there are any
        echo $twig->render('home.twig', [
            'page' => "Phrase d'accroche",
            'form_errors' => $form_errors
        ]);
    }

    /**
     * Summary of sendContactForm
     * This function can only be used after the validation of the form.
     * Used in checkContactForm
     * @param Twig $twig
     * @param PHPMailer $mail
     * @return void
     */
    private function sendContactForm(Twig $twig, PHPMailer $mail): void
    {
        // Send a message with the form data
        try {
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = $_ENV['SMTP_HOST']; //Set the SMTP server to send through
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = "tls"; //Enable SMTP authentication
            $mail->Username = $_ENV['SMTP_USERNAME']; //SMTP username
            $mail->Password = $_ENV['SMTP_PASSWORD']; //SMTP password
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom('florianpohu49@gmail.com', 'Florian Pohu'); //Sender's address
            $mail->addAddress('florianpohu49@gmail.com', 'Florian Pohu'); //Blog contact address
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = 'Here is the subject'; //We can format the mail using html

            $mail->Body = htmlspecialchars($_POST['comment']);
            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}