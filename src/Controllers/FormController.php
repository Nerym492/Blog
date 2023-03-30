<?php

namespace App\Controllers;

use App\Entity\Post;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\EntityManager\CommentManager;
use Exception;
use \Twig\Environment as Twig;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Summary of FormController
 * Forms management
 */
class FormController extends AbstractController
{

    public function showRegisterForm(Twig $twig): void
    {
        echo $twig->render('signIn.twig');
    }

    public function showLogInForm(Twig $twig): void
    {
        echo $twig->render('logIn.twig');
    }

    public function showPostForm(Twig $twig, ?int $postNum = null): void
    {
        //Editing a post
        if (!is_null($postNum)) {
            $postManager = new PostManager();
            $post = $postManager->getPost($postNum);
            $formTitle = "Edit a post";
            $formButtonText = "Edit";
        } else {
            $post = null;
            $formTitle = "Create a new post";
            $formButtonText = "Create";
        }

        echo $twig->render('postForm.twig', [
            'page' => 'New post',
            'form' => $post,
            'formTitle' => $formTitle,
            'formButtonText' => $formButtonText
        ]);
    }

    public function checkCommentForm(int $postId): void
    {
        $messageClass = "danger";
        $commentManager = new CommentManager();

        if ($_SESSION['user_id']) {
            if ($commentManager->createComment($postId)) {
                $message = "Your comment has been added !";
                $messageClass = "success";
            } else {
                $message = "An error occurred while adding the comment.\nPlease try again later.";
            }
        } else {
            $message = "You must be logged to write a comment.";
        }

        $_SESSION['message'] = $message;
        $_SESSION['messageClass'] = $messageClass;
    }


    public function checkLogInForm(Twig $twig): void
    {
        $mail = strip_tags($_POST['mail']);
        $password = strip_tags($_POST['password']);

        $userManager = new UserManager();

        if (!$userManager->checkLogin($mail, $password)) {
            $message = "Your email or password is not valid !";
            $messageClass = "danger";

            //Displays a red alert box on the login page
            echo $twig->render('logIn.twig', [
                'message' => $message,
                'messageClass' => $messageClass
            ]);
        } else {
            //Connect the user
            $userManager->connectUser($twig, $mail);
            //Displays home page and "Log in" is replaced by "Log out" in the navbar
            header("Location: /blog/public/home/", true ,303);
        }
    }

    /**
     * Summary of checkContactForm
     * Check if the submitted form is valid
     * @param Twig $twig
     * @return void
     */
    public function checkContactForm(Twig $twig): void
    {
        $mailStatus['mailSent'] = [];
        $mailStatus['message'] = "";

        // 'htmlName' => 'regexPattern'
        $patterns = [
            'fullName' => '/^([A-z]){3,25}\s{1}([A-z]){3,25}$/',
            'mail' => '/^([A-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/'
        ];

        /*Check form data
        return array $checkForm['form'] and $checkForm['isValid']*/
        $checkForm = $this->checkFormPatterns($patterns);

        //We add the comment data
        $checkForm['form']['comment'] = strip_tags($_POST['comment']);

        //We check if the field is not empty only if the form is still valid
        if (empty($checkForm['form']['comment']) && $checkForm['isValid']) {
            $checkForm['isValid'] = false;
        }

        //If the form is valid, we clear the array form
        if ($checkForm['isValid']) {
            //mailSent = true or false
            $mailStatus = $this->sendMail($checkForm['form']['mail'], $checkForm['form']['fullName'],
                'Here is the subject', $checkForm['form']['comment']);
            //The form can be deleted, the comment has been sent
            $checkForm['form'] = [];
        }

        //Displays the home page with errors if there are any
        echo $twig->render('home.twig', [
            'page' => "Phrase d'accroche",
            'form' => $checkForm['form'],
            'isValid' => $checkForm['isValid'],
            'mailSent' => $mailStatus['mailSent'],
            'message' => $mailStatus['message']
        ]);

    }

    public function checkRegisterForm(Twig $twig): void
    {
        $message = "";
        $messageClass = "";

        $patterns = [
            'pseudo' => '/^[A-z\d]{3,25}$/',
            'fullName' => '/^([A-z]){3,25}\s([A-z]){3,25}$/',
            'mail' => '/^([A-z\d.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/',
            'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
        ];

        $checkForm = $this->checkFormPatterns($patterns);

        $checkForm['form']['passwordConfirm'] = strip_tags($_POST['passwordConfirm']);

        if (($checkForm['form']['password'] != $checkForm['form']['passwordConfirm']) && $checkForm['isValid']) {
            //Passwords are not the same -> form not valid
            $checkForm['isValid'] = false;
        } else if ($checkForm['isValid']) {
            $userManager = new UserManager();

            if (!$userManager->checkDataAlreadyExists("mail", $checkForm['form']['mail'])) {
                /*This mail is not used, so we can create a new account
                 confirmation mail is sent to the user's email address*/
                try {
                    $mailConfirmationLink = $userManager->createUser($checkForm['form']);
                    $this->sendMail($checkForm['form']['mail'], $checkForm['form']['fullName'], "Confirm your email", $mailConfirmationLink);
                    $message = "Your account has been successfully created !\n";
                    $message .= "Please confirm your email address by clicking on the link that was sent to you.";
                    $messageClass = "success";
                    $checkForm['form'] = [];
                } catch (Exception $e) {
                    $message = "An error occurred while creating your account.\nPlease try again later.";
                    $messageClass = "danger";
                }

            } else {
                $message = "This mail is already used !";
                $messageClass = "danger";
                $checkForm['form']['mail'] = "";
                $checkForm['isValid'] = false;
            }
        }

        //Passwords are not returned for security reasons (even if it's encrypted)
        $checkForm['form']['password'] = "";
        $checkForm['form']['passwordConfirm'] = "";


        echo $twig->render('signIn.twig', [
            'page' => "Create an account",
            'form' => $checkForm['form'],
            'isValid' => $checkForm['isValid'],
            'message' => $message,
            'messageClass' => $messageClass
        ]);
    }

    public function checkPostForm(Twig $twig, ?int $postId = null): void
    {
        $checkForm['isValid'] = true;

        foreach ($_POST as $inputName => $inputValue) {
            if ($inputValue == "" && $checkForm['isValid']) {
                $checkForm['isValid'] = false;
            }

            $checkForm['form'][$inputName] = strip_tags($inputValue);
        }

        if ($checkForm['isValid']) {
            $postManager = new PostManager();
            if (!is_null($postId)){
                //Post before edit
                $post = $postManager->getPost($postId);
                //Post after edit
                $editedPost = new Post();
                $editedPost->setPostId($postId);
                $editedPost->setTitle($checkForm['form']['title']);
                $editedPost->setExcerpt($checkForm['form']['excerpt']);
                $editedPost->setContent($checkForm['form']['content']);
                $editedPost->setLastUpdateDate($post->getLastUpdateDate());
                //Comparing edited post with old post
                if ($editedPost->getTitle() != $post->getTitle() ||
                    $editedPost->getExcerpt() != $post->getExcerpt() ||
                    $editedPost->getContent() != $post->getContent()){

                    if($postManager->updatePost($editedPost)){
                        $_SESSION['message'] = 'The post has been successfully modified !';
                        $_SESSION['messageClass'] = 'success';
                    }
                } else {
                    $_SESSION['message'] = 'Nothing to update !';
                    $_SESSION['messageClass'] = 'warning';
                }

                $twig->addGlobal('session', $_SESSION);

                echo $twig->render('postForm.twig', [
                    'page' => 'Edit post',
                    'form' => $checkForm['form'],
                    'formTitle' => 'Edit a post',
                    'formButtonText' => 'Edit'
                ]);

                unset($_SESSION['message']);
                unset($_SESSION['messageClass']);
            } else {
                if ($postManager->createPost($checkForm['form'])) {
                    $_SESSION['message'] = 'The post has been successfully added !';
                    $_SESSION['messageClass'] = 'success';
                    header('Location: /blog/public/posts/#site-heading', true, 303);
                }
            }
        }

        // If something goes wrong in the database, we get here
        if (isset($_SESSION['messageClass']) and $_SESSION['messageClass'] == "danger") {
            echo $twig->render('postForm.twig', [
                'page' => 'New post',
                'form' => $checkForm['form']
            ]);
        }
    }

    /**
     * This function can only be used after the validation of the form.
     * Used in checkContactForm
     * @param string $recipientsMail
     * @param string $recipientsFullName
     * @param string $subject
     * @param string $content
     * @return array
     */
    private function sendMail(string $recipientsMail, string $recipientsFullName, string $subject, string $content): array
    {

        $recipientsFullName = explode(" ", $recipientsFullName);
        $recipientsLastName = $recipientsFullName[0];
        $recipientsFirstName = $recipientsFullName[1];

        $mail = new PHPMailer(true);
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
            $mail->addAddress($recipientsMail, $recipientsFirstName . " " . $recipientsLastName); //Recipient's address
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $subject; //We can format the mail using html

            $mail->Body = $content;
            $mail->send();
            $message = 'Message has been sent';
            $mailSent = true;
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            $mailSent = false;
        }

        return ['mailSent' => $mailSent, 'message' => $message];
    }

    private function checkFormPatterns(array $formPatterns): array
    {
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
}