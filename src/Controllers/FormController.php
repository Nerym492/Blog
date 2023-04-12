<?php

namespace App\Controllers;

use App\Entity\Post;
use App\EntityManager\PostManager;
use App\EntityManager\UserManager;
use App\EntityManager\CommentManager;
use Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Summary of FormController
 * Forms management
 */
class FormController extends AbstractController
{


    /**
     * Display the register form.
     *
     * @return void
     */
    public function showRegisterForm(): void
    {
        $this->renderView('signIn.twig');

    }//end showRegisterForm()


    /**
     * Displays the login form page
     *
     * @return void
     */
    public function showLogInForm(): void
    {
        $this->renderView('logIn.twig');

    }//end showLogInForm()


    /**
     * Display the post form page
     * If the GET postNum is defined then the corresponding post is retrieved
     *
     * @param integer|null $postNum The post id if the user is editing the post.
     *
     * @return void
     */
    public function showPostForm(?int $postNum=null): void
    {
        $post           = null;
        $formTitle      = 'Create a new post';
        $formButtonText = 'Create';

        // Editing a post.
        if (isset($postNum) === true) {
            $postManager    = new PostManager();
            $post           = $postManager->getPost($postNum);
            $formTitle      = 'Edit a post';
            $formButtonText = 'Edit';
        }

        $this->renderView(
            'postForm.twig',
            [
             'page'           => 'New post',
             'form'           => $post,
             'formTitle'      => $formTitle,
             'formButtonText' => $formButtonText,
            ]
        );

    }//end showPostForm()


    /**
     * Check the comment form.
     * If the form is valid display a green alert box with a confirmation message.
     * If it is not the alert box is red with an error message.
     *
     * @param integer $postId Post being read.
     *
     * @return void
     */
    public function checkCommentForm(int $postId): void
    {
        $commentContent = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS);
        $commentCreated = false;

        if (empty($commentContent) === false) {
            $commentManager = new CommentManager();
            $this->session->set('message', 'You must be logged to write a comment.');
            $this->session->set('messageClass', 'danger');

            if ($this->session->get('user_id') === true) {
                $commentCreated = $commentManager->createComment($postId, $this->session);
            }

            if ($commentCreated === true) {
                /*
                    After processing the data, we redirect the browser to the same page with the HTTP status code303
                    The old header is replaced with a new one that does not contain $_POST data
                    Post/Redirect/Get
                    Prevent the form from being submitted multiple times by refreshing the page
                */

                header('Location: /blog/public/posts/'.$postId.'#comments-box-post', true, 303);
            }

        }//end if

    }//end checkCommentForm()


    /**
     * If the form is valid the user is redirected to the home page.
     * Otherwise, display the login form with an error.
     *
     * @return void
     */
    public function checkLogInForm(): void
    {
        $mail     = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

        $userManager = new UserManager();

        $loginIsValid = $userManager->checkLogin($mail, $password);
        switch ($loginIsValid) {
            case false :
                $message      = 'Your email or password is not valid !';
                $messageClass = 'danger';

                // Displays a red alert box on the login page.
                $this->renderView(
                    'logIn.twig',
                    [
                     'message'      => $message,
                     'messageClass' => $messageClass,
                    ]
                );
                break;
            default:
                // Connect the user.
                $userManager->connectUser($mail);
                $this->setTwigSessionGlobals();
                // Displays home page and "Log in" is replaced by "Log out" in the navbar.
                header('Location: /blog/public/home/', true, 303);

        }//end switch

    }//end checkLogInForm()


    /**
     * Summary of checkContactForm
     * Check if the submitted form is valid
     *
     * @return void
     */
    public function checkContactForm(): void
    {
        $mailStatus['mailSent'] = [];
        $mailStatus['message']  = '';

        // Example : 'htmlName' => 'regexPattern'.
        $patterns = [
                     'fullName' => '/^([A-z]){3,25}\s{1}([A-z]){3,25}$/',
                     'mail'     => '/^([A-z\d\.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/',
                    ];

        // Check form data with patterns above.
        $checkForm = $this->checkFormPatterns($patterns);

        // We add the comment data.
        $checkForm['form']['comment'] = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_SPECIAL_CHARS);

        // We check if the field is not empty only if the form is still valid.
        if (empty($checkForm['form']['comment']) === true && $checkForm['isValid'] === true) {
            $checkForm['isValid'] = false;
        }

        // If the form is valid, we clear the array form.
        if ($checkForm['isValid'] === true) {
            // Return a boolean.
            $mailStatus = $this->sendMail(
                $checkForm['form']['mail'],
                $checkForm['form']['fullName'],
                'Here is the subject',
                $checkForm['form']['comment']
            );
            // The form can be deleted, the comment has been sent.
            $checkForm['form'] = [];
        }

        // Displays the home page with errors if there are any.
        $this->renderView(
            'home.twig',
            [
             'page'     => "Phrase d'accroche",
             'form'     => $checkForm['form'],
             'isValid'  => $checkForm['isValid'],
             'mailSent' => $mailStatus['mailSent'],
             'message'  => $mailStatus['message'],
            ]
        );

    }//end checkContactForm()


    /**
     * Check if the register is valid
     * If it is, send a mail to user who has just registered
     *
     * @return void
     */
    public function checkRegisterForm(): void
    {
        $message      = '';
        $messageClass = '';

        $patterns = [
                     'pseudo'   => '/^[A-z\d]{3,25}$/',
                     'fullName' => '/^([A-z]){3,25}\s([A-z]){3,25}$/',
                     'mail'     => '/^([A-z\d.-]+)@([a-z\d-]+)\.([a-z]{2,8})(\.[a-z]{2,8})?$/',
                     'password' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                    ];

        $checkForm = $this->checkFormPatterns($patterns);

        $checkForm['form']['passwordConfirm'] = filter_input(
            INPUT_POST,
            'passwordConfirm',
            FILTER_SANITIZE_SPECIAL_CHARS
        );

        if (($checkForm['form']['password'] !== $checkForm['form']['passwordConfirm'])
            && $checkForm['isValid'] === true
        ) {
            // Passwords are not the same -> form not valid.
            $checkForm['isValid'] = false;
        } else if ($checkForm['isValid'] === true) {
            $userManager = new UserManager();

            $dataAlreadyExists = $userManager->checkDataAlreadyExists('mail', $checkForm['form']['mail']);
            switch ($dataAlreadyExists) {
                case false:
                    try {
                        $mailConfirmationLink = $userManager->createUser($checkForm['form']);
                        $this->sendMail(
                            $checkForm['form']['mail'],
                            $checkForm['form']['fullName'],
                            'Confirm your email',
                            $mailConfirmationLink
                        );
                        $message      = 'Your account has been successfully created !\n';
                        $message     .= 'Please confirm your email address by clicking on the link that was sent to you.';
                        $messageClass = 'success';

                        $checkForm['form'] = [];
                    } catch (Exception) {
                        $message      = 'An error occurred while creating your account.\nPlease try again later.';
                        $messageClass = 'danger';
                    }
                    break;
                default:
                    $message                   = 'This mail is already used !';
                    $messageClass              = 'danger';
                    $checkForm['form']['mail'] = '';
                    $checkForm['isValid']      = false;
            }//end switch

        }//end if

        // Passwords are not returned for security reasons (even if it's encrypted).
        $checkForm['form']['password']        = '';
        $checkForm['form']['passwordConfirm'] = '';

        $this->renderView(
            'signIn.twig',
            [
             'page'         => 'Create an account',
             'form'         => $checkForm['form'],
             'isValid'      => $checkForm['isValid'],
             'message'      => $message,
             'messageClass' => $messageClass,
            ]
        );

    }//end checkRegisterForm()


    /**
     * Verify if the post form is valid and if it is, the user is redirected to the posts page.
     * This check is used when creating or editing a post
     *
     * @param integer|null $postId Id of the post being modified.
     *
     * @return void
     */
    public function checkPostForm(?int $postId=null): void
    {
        $checkForm = ['isValid' => true];

        foreach (filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS) as $inputName => $inputValue) {
            if ($inputValue === '' && $checkForm['isValid'] === true) {
                $checkForm['isValid'] = false;
            }

            $checkForm['form'][$inputName] = strip_tags($inputValue);
        }

        if ($checkForm['isValid'] === true) {
            $postManager = new PostManager();
            // Check if the user is editing or creating a post.
            switch (isset($postId)) {
                case false:
                    // Post before edit.
                    $post = $postManager->getPost($postId);
                    // Post after edit.
                    $editedPost = new Post();
                    $editedPost->setPostId($postId);
                    $editedPost->setTitle($checkForm['form']['title']);
                    $editedPost->setExcerpt($checkForm['form']['excerpt']);
                    $editedPost->setContent($checkForm['form']['content']);
                    $dateNow = date_format($post->getLastUpdateDate(), 'Y-m-d H:i:s');
                    $editedPost->setLastUpdateDate($dateNow);
                    // Comparing edited post with old post.
                    $postsAreIdentical = $this->postManager->checkIdenticalPost($post, $editedPost);
                    switch ($postsAreIdentical) {
                        case true:
                            $postManager->updatePost($editedPost, $this->session);
                            break;
                        default:
                            $this->session->set('message', 'Nothing to update !');
                            $this->session->set('messageClass', 'warning');
                    }

                    $this->setTwigSessionGlobals();

                    $this->renderView(
                        'postForm.twig',
                        [
                         'page'           => 'Edit post',
                         'form'           => $checkForm['form'],
                         'formTitle'      => 'Edit a post',
                         'formButtonText' => 'Edit',
                        ]
                    );
                    $this->session->clearKeys(['message', 'messageClass']);
                    break;
                default:
                    if ($postManager->createPost($checkForm['form']) === true) {
                        $this->session->set('message', 'The post has been successfully added !');
                        $this->session->set('messageClass', 'success');
                        header('Location: /blog/public/posts/#site-heading', true, 303);
                    }

            }//end switch
        }//end if

        // If something goes wrong in the database, we get here.
        if ($this->session->get('messageClass') === 'danger') {
            $this->renderView(
                'postForm.twig',
                [
                 'page' => 'New post',
                 'form' => $checkForm['form'],
                ]
            );
        }

    }//end checkPostForm()


    /**
     * This function can only be used after the validation of the form.
     * Used in checkContactForm
     *
     * @param string $sendersMail     Mail of the sender.
     * @param string $sendersFullName Full name of the sender.
     * @param string $subject         Subject of the mail.
     * @param string $content         Content of the mail.
     *
     * @return array
     */
    private function sendMail(
        string $sendersMail,
        string $sendersFullName,
        string $subject,
        string $content
    ): array {
        $sendersFullName     = explode(' ', $sendersFullName);
        $recipientsLastName  = $sendersFullName[0];
        $recipientsFirstName = $sendersFullName[1];

        $mail = new PHPMailer(true);
        // Send a message with the form data.
        try {
            // Send using SMTP.
            $mail->isSMTP();
            // Set the SMTP server to send through.
            $mail->Host = $this->env->getVar('SMTP_HOST');
            // Enable SMTP authentication.
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            // SMTP username.
            $mail->Username = $this->env->getVar('SMTP_USERNAME');
            // SMTP password.
            $mail->Password = $this->env->getVar('SMTP_PASSWORD');
            $mail->Port = $this->env->getVar('SMTP_PORT');
            // Sender's address.
            $mail->setFrom('florianpohu49@gmail.com', 'Florian Pohu');
            // Recipient's address.
            $mail->addAddress($sendersMail, $recipientsFirstName . ' ' . $recipientsLastName);
            // Set email format to HTML.
            $mail->isHTML();
            // We can format the mail using html.
            $mail->Subject = $subject;
            $mail->Body    = $content;
            $mail->send();
            $message = 'Message has been sent';
            $mailSent = true;
        } catch (Exception) {
            $message  = 'Message could not be sent. Mailer Error: $mail->ErrorInfo';
            $mailSent = false;
        }//end try

        return [
                'mailSent' => $mailSent,
                'message'  => $message,
               ];

    }//end sendMail()


    /**
     * Verify if the patterns are valid
     *
     * @param array $formPatterns The keys of the $formPatterns = html names in the form.
     *
     * @return array
     */
    private function checkFormPatterns(array $formPatterns): array
    {
        $formErrors = [];
        $isValid    = true;

        foreach ($formPatterns as $fieldName => $pattern) {
            // We add the name of the fields and their value in this array + html tags deletion.
            $formErrors[$fieldName] = filter_input(INPUT_POST, $fieldName, FILTER_SANITIZE_SPECIAL_CHARS);
            // Check the patterns.
            if (preg_match($pattern, $formErrors[$fieldName]) === false && $isValid === true) {
                $isValid = false;
            }
        }

        return [
                'form'    => $formErrors,
                'isValid' => $isValid,
               ];

    }//end checkFormPatterns()


}//end class
