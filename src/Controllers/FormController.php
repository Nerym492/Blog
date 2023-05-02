<?php

namespace App\Controllers;

use App\Entity\Post;
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
     * @throws Exception
     */
    public function showRegisterForm(): void
    {
        $this->generateToken();
        $this->renderView('signIn.twig');

    }//end showRegisterForm()


    /**
     * Displays the login form page
     *
     * @return void
     * @throws Exception
     */
    public function showLogInForm(): void
    {
        $this->generateToken();
        $this->renderView('logIn.twig');

    }//end showLogInForm()


    /**
     * Display the post form page
     * If the GET postNum is defined then the corresponding post is retrieved
     *
     * @param integer|null $postNum The post id if the user is editing the post.
     *
     * @return void
     * @throws Exception
     */
    public function showPostForm(?int $postNum=null): void
    {
        $this->generateToken();

        $post           = null;
        $formTitle      = 'Create a new post';
        $formButtonText = 'Create';

        // Editing a post.
        if (isset($postNum) === true) {
            $post           = $this->postManager->getPost($postNum);
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
     * @throws Exception
     */
    public function checkCommentForm(int $postId): void
    {
        if ($this->session->get('user_id') === null) {
            $this->session->set('message', 'You must be logged to write a comment.');
            $this->session->set('messageClass', 'danger');

            $post = $this->postManager->getPost($postId);
            $comments = $this->commentManager->getCommentsByPost($postId);
            $userPost = $this->userManager->getUser(userId: $post->getUserId());

            $this->renderView(
                'post.twig',
                [
                 'post'     => $post,
                 'userPost' => $userPost,
                 'comments' => $comments,
                ]
            );
            return;
        }

        $commentContent = html_entity_decode(
            filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS)
        );

        if (empty($commentContent) === false) {
            $commentIsCreated = $this->commentManager->createComment($postId, $commentContent);
        }//end if

        if (isset($commentIsCreated) === true && $commentIsCreated === true) {
            // PRG pattern (Post/Redirect/Get).
            $this->redirectTo($this->env->getVar('PUBLIC_PATH').'/posts/'.$postId.'#comments-box-post');
        }

    }//end checkCommentForm()


    /**
     * If the form is valid the user is redirected to the home page.
     * Otherwise, display the login form with an error.
     *
     * @return void
     */
    public function checkLogInForm(): void
    {
        $tokenVerified = $this->verifyToken($this->env->getVar('PUBLIC_PATH').'/logIn');

        if ($tokenVerified === false) {
            return;
        }

        $mail     = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $loginIsValid = $this->userManager->checkLogin($mail, $password);
        if ($loginIsValid === false) {
            $this->session->set('message', 'Your email or password is not valid !');
            $this->session->set('messageClass', 'danger');
            $this->renderView('logIn.twig');
            return;
        }

        // Connect the user.
        $this->userManager->connectUser($mail);
        // Displays home page and "Log in" is replaced by "Log out" in the navbar.
        $this->redirectTo($this->env->getVar('PUBLIC_PATH').'/home/');

    }//end checkLogInForm()


    /**
     * Summary of checkContactForm
     * Check if the submitted form is valid
     *
     * @return void
     */
    public function checkContactForm(): void
    {
        $verifiedToken = $this->verifyToken($this->env->getVar('PUBLIC_PATH').'/home/#form-contact');
        if ($verifiedToken === false) {
            return;
        }

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
        $checkForm['form']['comment'] = filter_input(INPUT_POST, 'comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

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
        $verifiedToken = $this->verifyToken($this->env->getVar('PUBLIC_PATH').'/register/');

        if ($verifiedToken === false) {
           return;
        }

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
            FILTER_SANITIZE_FULL_SPECIAL_CHARS
        );

        $identicalPasswords = ($checkForm['form']['password'] === $checkForm['form']['passwordConfirm']);

        if ($identicalPasswords === false && $checkForm['isValid'] === true) {
            // Passwords are not the same -> form not valid.
            $checkForm['isValid'] = false;
        }

        if ($checkForm['isValid'] === true) {
            $dataCheck = $this->userManager->checkDataAlreadyExists('mail', $checkForm['form']['mail']);
            // If the mail is already used, the form is invalid.
            $checkForm['isValid'] = $dataCheck['formIsValid'];
            // Mail not used and the form is valid.
            if ($dataCheck['alreadyExists'] === false && $checkForm['isValid'] === true) {
                try {
                    $mailConfirmationLink = $this->userManager->createUser($checkForm['form']);
                    $this->sendMail(
                        $checkForm['form']['mail'],
                        $checkForm['form']['fullName'],
                        'Confirm your email',
                        $mailConfirmationLink
                    );
                    $this->session->set(
                        'message',
                        "Your account has been successfully created !\nPlease confirm your email address by clicking on the link that was sent to you."
                    );
                    $this->session->set('messageClass', 'success');

                    // Form data is cleared because not needed anymore.
                    $checkForm['form'] = [];
                } catch (Exception) {
                    $this->session->set('message', "An error occurred while creating your account.\nPlease try again later.");
                    $this->session->set('messageClass', 'danger');
                }//end try
            }//end if
        }//end if

        // Passwords are not returned for security reasons (even if it's encrypted).
        $checkForm['form']['password']        = '';
        $checkForm['form']['passwordConfirm'] = '';

        $this->renderView(
            'signIn.twig',
            [
             'page'    => 'Create an account',
             'form'    => $checkForm['form'],
             'isValid' => $checkForm['isValid'],
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
     * @throws Exception
     */
    public function checkPostForm(?int $postId=null): void
    {
        $url = $this->env->getVar('PUBLIC_PATH').'/posts/create/#alert-box';
        if ($postId !== null) {
            $url = $this->env->getVar('PUBLIC_PATH').'/posts/edit/'.$postId.'/#alert-box';
        }

        $verifiedToken = $this->verifyToken($url);
        if ($verifiedToken === false) {
            return;
        }

        $checkForm = ['isValid' => true];

        foreach (filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS) as $inputName => $inputValue) {
            if ($inputValue === '' && $checkForm['isValid'] === true) {
                $checkForm['isValid'] = false;
            }

            $checkForm['form'][$inputName] = html_entity_decode($inputValue);
        }

        // Checks if the post has been edited and if so, saves the modification.
        if ($this->checkEditedPost($postId, $checkForm) === true) {
            return;
        }

        // Create a new post.
        if ($checkForm['isValid'] === true && $this->postManager->createPost($checkForm['form']) === true) {
            $this->redirectTo($this->env->getVar('PUBLIC_PATH').'/posts/#site-heading');
            return;
        }

        // If something goes wrong in the database, we get here.
        $this->renderView(
            'postForm.twig',
            [
             'page' => 'New post',
             'form' => $checkForm['form'],
            ]
        );

    }//end checkPostForm()


    /**
     * Checks if the post has been edited and if so, saves the modification
     *
     * @param int|null $postId    Id of the post being read
     * @param array    $checkForm Form data
     * @return bool True if has been edited, otherwise false.
     * @throws Exception
     */
    private function checkEditedPost(?int $postId, array $checkForm) :bool
    {
        if (isset($postId) === true && $checkForm['isValid'] === true) {
            // Post before edit.
            $post = $this->postManager->getPost($postId);
            // Post after edit.
            $editedPost = new Post();
            $editedPost->setPostId($postId);
            $editedPost->setTitle($checkForm['form']['title']);
            $editedPost->setExcerpt($checkForm['form']['excerpt']);
            $editedPost->setContent($checkForm['form']['content']);
            $dateNow = date_format($post->getLastUpdateDate(), 'Y-m-d H:i:s');
            $editedPost->setLastUpdateDate($dateNow);
            // Comparing edited post with old post and update it if necessary.
            $this->postManager->updatePost($post, $editedPost);

            $this->redirectTo($this->env->getVar('PUBLIC_PATH').'/posts/edit/'.$postId.'/#alert-box');

            return true;
        }//end if

        return false;

    }//end checkEditedPost()


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
            $mail->addAddress($sendersMail, $recipientsFirstName.' '.$recipientsLastName);
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
     * @return array ['form' => $formErrors, 'isValid' => $isValid,].
     * To access the form values : array['form']['htmlName']
     */
    private function checkFormPatterns(array $formPatterns): array
    {
        $formErrors = [];
        $isValid    = true;

        foreach ($formPatterns as $fieldName => $pattern) {
            // We add the name of the fields and their value in this array + html tags deletion.
            $formErrors[$fieldName] = html_entity_decode(
                filter_input(INPUT_POST, $fieldName, FILTER_SANITIZE_FULL_SPECIAL_CHARS)
            );
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
