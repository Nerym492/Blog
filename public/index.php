<?php

require '../vendor/autoload.php';

use App\Controllers\ErrorController;
use App\Controllers\HomeController;
use App\Controllers\PostController;
use App\Controllers\FormController;
use App\Controllers\UserController;
use App\Controllers\AdminController;
use Bramus\Router\Router;

$router = new Router();

$formController = new FormController();
$userController = new UserController();
$adminController = new AdminController();

$router->set404(
    function () {
        header('HTTP/1.1 404 Not Found');
        $errorController = new ErrorController();
        $errorController->showPage404();
    }
);


$router->mount(
    '/home',
    function () use ($router, $formController, $userController) {
        $homeController = new HomeController();

        // The page is displayed without sending the form.
        $router->get(
            '/',
            function () use ($homeController) {
                $homeController->showHome();
            }
        );

        // The visitor has sent the form.
        $router->post(
            '/',
            function () use ($formController) {
                $formController->checkContactForm();
            }
        );

        $router->get(
            '/loggedOut',
            function () use ($userController) {
                $userController->logOut();
            }
        );
    }
);

$router->mount(
    '/posts',
    function () use ($router, $formController) {
        $postController = new PostController();

        // Displays all the posts.
        $router->get(
            '/',
            function () use ($postController) {
                $postController->showPostsPage();
            }
        );
        // Posts reload with Ajax.
        $router->get(
            '/posts-page-(\d+)',
            function ($pageNum) use ($postController) {
                $postController->reloadPostsList($pageNum);
            }
        );

        // Displays a single post.
        $router->get(
            '/(\d+)',
            function ($postId) use ($postController) {
                $postController->showPost($postId);
            }
        );

        // The comment form has been submitted.
        $router->post(
            '/(\d+)',
            function ($postId) use ($formController) {
                $formController->checkCommentForm($postId);
            }
        );

        // Enable post form if the user is an admin.
        if ($formController->getSession()->get('isAdmin') === "1") {
            // Displays the post form.
            $router->get(
                '/create',
                function () use ($formController) {
                    $formController->showPostForm();
                }
            );

            // The post form has been submitted.
            $router->post(
                '/create',
                function () use ($formController) {
                    $formController->checkPostForm();
                }
            );

            // Displays the form with the post values.
            $router->get(
                '/edit/(\d+)',
                function ($postNum) use ($formController) {
                    $formController->showPostForm($postNum);
                }
            );

            // The edited post has been submitted.
            $router->post(
                '/edit/(\d+)',
                function ($postNum) use ($formController) {
                    $formController->checkPostForm($postNum);
                }
            );
        }//end if
    }
);


$router->mount(
    '/register',
    function () use ($router, $formController) {
        // Displays the register form.
        $router->get(
            '/',
            function () use ($formController) {
                $formController->showRegisterForm();
            }
        );

        // The visitor has sent the register form.
        $router->post(
            '/',
            function () use ($formController) {
                $formController->checkRegisterForm();
            }
        );
    }
);

$router->mount(
    '/logIn',
    function () use ($router, $formController, $userController) {
        // Displays the login form.
        $router->get(
            '/',
            function () use ($formController) {
                $formController->showLogInForm();
            }
        );

        // The user clicked on the link he received by mail.
        $router->get(
            '/mail/([^/<>]+)/verificationCode/(\w+)',
            function ($mail, $verificationCode) use ($userController) {
                $userController->confirmMailAddress($mail, $verificationCode);
            }
        );

        // The user has submitted the login form.
        $router->post(
            '/',
            function () use ($formController) {
                $formController->checkLogInForm();
            }
        );
    }
);

if ($adminController->getSession()->get('isAdmin') === "1") {
    $router->mount(
        '/administration',
        function () use ($router, $adminController) {
            // Loads the entire page.
            $router->get(
                '/',
                function () use ($adminController) {
                    $adminController->showAdminPanel();
                }
            );
            // The posts list is reloaded.
            $router->get(
                '/posts-page-(\d+)',
                function ($pageNum) use ($adminController) {
                    $adminController->reloadPostsList($pageNum);
                }
            );
            // A post is deleted.
            $router->get(
                '/delete/post-(\d+)-page-(\d+)',
                function ($postId, $pageNum) use ($adminController) {
                    $adminController->deletePost($pageNum, $postId);
                }
            );
            // The comment list is reloaded.
            $router->get(
                '/comments-page-(\d+)',
                function ($pageNum) use ($adminController) {
                    $adminController->reloadCommentsList($pageNum);
                }
            );
            // The comment is deleted.
            $router->get(
                '/delete/comment-(\d+)-page-(\d+)',
                function ($commentId, $pageNum) use ($adminController) {
                    $adminController->deleteComment($pageNum, $commentId);
                }
            );
            // The comment status is set to validated.
            $router->get(
                '/validate/comment-(\d+)-page-(\d+)',
                function ($commentId, $pageNum) use ($adminController) {
                    $adminController->validateComment($pageNum, $commentId);
                }
            );
            // The comment status is reset to Pending.
            $router->get(
                '/validate/comment-(\d+)-page-(\d+)/cancel',
                function ($commentId, $pageNum) use ($adminController) {
                    $adminController->validateComment($pageNum, $commentId, true);
                }
            );
        }
    );
}//end if


$router->run();
