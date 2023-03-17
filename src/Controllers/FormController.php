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
    public function showRegisterForm(Twig $twig){
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
        $mailStatus = [];

        // 'htmlName' => 'regexPattern'
        $patterns = [
            'fullName' => '/^([A-z]){3,25}\s{1}([A-z]){3,25}$/',
            'mail' => '/^([a-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/'
        ];

        //Check form data
        $checkForm = $this->checkFormPatterns($patterns);

        //We add the comment data
        $checkForm['form']['comment'] = $_POST['comment'];

        //We check if the field is not empty only if the form is still valid
        if (empty($_POST['comment']) && $checkForm['isValid']) {
            $checkForm['isValid'] = false;
        }

        //If the form is valid, we clear the array form_errors
        if ($checkForm['isValid']) {
            $checkForm['form'] = [];
            $mail = new PHPMailer(true);
            //mailSent = true or false
            $mailStatus = $this->sendContactForm($mail);
        }

        //Displays the home page with errors if there are any
        echo $twig->render('home.twig', [
            'page' => "Phrase d'accroche",
            'form_errors' => $checkForm['form'],
            'isValid' => $checkForm['isValid'],
            'mailSent' => $mailStatus['mailSent']
        ]);

    }

    /**
     * Summary of sendContactForm
     * This function can only be used after the validation of the form.
     * Used in checkContactForm
     * @param PHPMailer $mail
     * @return bool
     */
    private function sendContactForm(PHPMailer $mail): array
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

            $mail->Body = strip_tags($_POST['comment']);
            $mail->send();
            $message = 'Message has been sent';
            $mailSent = true;
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $mailSent = false;
        }

        return ['mailSent' => $mailSent, 'message' => $message];
    }

    private function checkFormPatterns(array $formPatterns): array{
        $formErrors = [];
        $isValid = true;

        foreach ($formPatterns as $fieldName => $pattern) {
            /*We add the name of the fields and their value in this array
            + html tags deletion */
            $formErrors[$fieldName] = strip_tags($_POST[$fieldName]);
            //Check the patterns
            if (!preg_match($pattern, $formErrors[$fieldName]) && $isValid) {
                $isValid = false;
            }
        }

        return ['form' => $formErrors, 'isValid' => $isValid];
    }

    public function checkRegisterForm(Twig $twig){

    }

}