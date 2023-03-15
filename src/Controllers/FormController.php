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
    public function showSignInForm(Twig $twig){
        echo $twig->render('signIn.twig');
    }

    /**
     * Summary of checkContactForm
     * Check if the submitted form is valid
     * @param Twig $twig
     * @return void
     */
    public function checkContactForm(Twig $twig): void
    {

        $formErrors = [];
        $mailSent = false;
        $isValid = true;

        // 'htmlName' => 'regexPattern'
        $patterns = [
            'fullName' => '/^([A-z]){3,25}\s{1}([A-z]){3,25}$/',
            'mail' => '/^([a-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/'
        ];

        //Check form data
        foreach ($patterns as $fieldName => $pattern) {
            //We add the name of the fields and their value in this array
            $formErrors[$fieldName] = $_POST[$fieldName];
            //Check the patterns
            if (!preg_match($pattern, $_POST[$fieldName]) && $isValid) {
                $isValid = false;
            }
        }

        //We add the comment data
        $formErrors['comment'] = $_POST['comment'];

        //We check if the field is not empty only if the form is still valid
        if (empty($_POST['comment']) && $isValid) {
            $isValid = false;
        }

        //If the form is valid, we clear the array form_errors
        if ($isValid) {
            $formErrors = [];
            $mail = new PHPMailer(true);
            //mailSent = true or false
            $mailSent = $this->sendContactForm($mail);
        }

        //Displays the home page with errors if there are any
        echo $twig->render('home.twig', [
            'page' => "Phrase d'accroche",
            'form_errors' => $formErrors,
            'isValid' => $isValid,
            'mailSent' => $mailSent
        ]);
    }

    /**
     * Summary of sendContactForm
     * This function can only be used after the validation of the form.
     * Used in checkContactForm
     * @param PHPMailer $mail
     * @return bool
     */
    private function sendContactForm(PHPMailer $mail): bool
    {
        // Send a message with the form data
        try {
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = $_ENV['SMTP_HOST']; //Set the SMTP server to send through
            $mail->SMTPAuth = true;//Enable SMTP authentication
            $mail->SMTPSecure = "tls";
            $mail->Username = $_ENV['SMTP_USERNAME']; //SMTP username
            $mail->Password = $_ENV['SMTP_PASSWORD']; //SMTP password
            $mail->Port = $_ENV['SMTP_PORT'];

            $mail->setFrom('florianpohu49@gmail.com', 'Florian Pohu'); //Sender's address
            $mail->addAddress('florianpohu49@gmail.com', 'Florian Pohu'); //Blog contact address
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = 'Here is the subject'; //We can format the mail using html

            $mail->Body = htmlspecialchars($_POST['comment']);
            $mail->send();
            $message = 'Message has been sent';
            $mailSent = true;
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $mailSent = false;
        }

        return $mailSent;
    }

}