<?php

namespace App\EntityManager;

use App\Entity\Post;
use App\Lib\DatabaseConnection;
use App\Lib\Session;

class PostManager extends Manager
{

    /**
     * @param int $pageNum   Page currently being read in the pagination
     * @param int $postLimit Number of rows per page
     * @return array postData, numberOfPosts before being limited, currentPage
     * @throws \Exception
     */
    public function getPosts(int $pageNum, int $postLimit): array
    {
        $postsRowsData = [];
        $connexion = new DatabaseConnection();

        $statement = $this->connection->getConnection()->prepare(
            "SELECT COUNT(p.post_id) as 'nbPosts'
                     FROM blog.post p"
        );

        $statement->execute();
        $postsRowsCount = $statement->fetch()['nbPosts'];

        if ($postsRowsCount > 0) {
            $pageDelimitation = $this->calcPageAndOffset($postLimit, $pageNum, $postsRowsCount, "DESC");

            $selectQuery = "SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, 
                                   p.creation_date, u.pseudo
                            FROM blog.post p
                            LEFT OUTER JOIN blog.user u on p.user_id = u.user_id";
            $postsRowsData = $connexion->execQueryWithLimit(
                $pageDelimitation['rowsLimit'],
                $pageDelimitation['offset'],
                $selectQuery,
                "post_id",
                "DESC"
            );
        } else {
            $pageDelimitation['pageNum'] = $pageNum;
        }

        $posts = [];

        foreach ($postsRowsData as $row) {
            $post = $this->createPostWithRow($row);
            $posts[$post->getPostId()] = [
                                          'post'       => $post,
                                          'pseudoUser' => $row['pseudo'],
                                         ];
        }

        return [
                'data'        => $posts,
                'nbLines'     => $postsRowsCount,
                'currentPage' => $pageDelimitation['pageNum'],
               ];
    }//end getPosts()


    public function getPost(int $postId): ?Post
    {
        $statement = $this->connection->getConnection()->prepare(
            "SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, p.creation_date
            FROM blog.post p
            WHERE p.post_id = :postId"
        );

        $statement->execute([':postId' => $postId]);
        $row = $statement->fetch();

        // On vérifie si on récupère bien le post
        if ($row) {
            $post = $this->createPostWithRow($row);
        } else {
            $post = null;
        }

        $statement->closeCursor();

        return $post;
    }//end getPost()


    public function createPost(array $form): bool
    {
        $dateNow = new \DateTime('now', new \DateTimeZone($_ENV['TIMEZONE']));
        $dateNow = $dateNow->format('Y-m-d H:i:s');

        try {
            $statement = $this->connection->getConnection()->prepare(
                "INSERT INTO blog.post
                   (user_id, title, excerpt, content, last_update_date, creation_date)
                   VALUES(:user_id, :title, :excerpt, :content, :last_update_date, :creation_date);"
            );

            $statement->execute(
                [
                 ':user_id'          => $_SESSION['user_id'],
                 ':title'            => $form['title'],
                 ':excerpt'          => $form['excerpt'],
                 ':content'          => $form['content'],
                 ':last_update_date' => $dateNow,
                 ':creation_date'    => $dateNow,
                ]
            );
            $isCreated = $statement->rowCount() == 1;
        } catch (\Throwable $e) {
            if (!isset($_SESSION['message'])) {
                $_SESSION['message'] = "An error occurred while creating the post";
                $_SESSION['messageClass'] = "danger";
            }
            $isCreated = false;
        }//end try

        return $isCreated;
    }//end createPost()


    /**
     * Update title, excerpt and content field in the database
     * @param Post $post Post object
     * @return bool True if updated successfully else false
     */
    public function updatePost(Post $post, Session $session): void
    {
        $statement = $this->connection->getConnection()->prepare(
            "UPDATE post
                   SET title=:title, excerpt=:excerpt, content=:content
                   WHERE post_id=:post_id"
        );
        $statement->execute(
            [
             ':title'   => $post->getTitle(),
             ':excerpt' => $post->getExcerpt(),
             ':content' => $post->getContent(),
             ':post_id' => $post->getPostId(),
            ]
        );

        $updatedRows = $statement->rowCount();

        if ($updatedRows === 1) {
            $session->set('message', 'The post has been successfully modified !');
            $session->set('messageClass', 'success');
        }

    }//end updatePost()


    /**
     * @param int $postId
     * @return bool True if deleted else false
     */
    public function deletePost(int $postId): bool
    {
        $statement = $this->connection->getConnection()->prepare(
            "DELETE FROM post
                   WHERE post_id=:postId"
        );

        $statement->execute(
            [':postId' => $postId]
        );

        return $statement->rowCount() == 1;
    }//end deletePost()


    /**
     * Create a post object with a post row from the database.
     *
     * @param array $row Row from the post table in the Database
     * @return Post|null
     */
    private function createPostWithRow(array $row): ?Post
    {
        $post = new Post();

        $post->setUserId($row['user_id']);
        $post->setPostId($row['post_id']);
        $post->setExcerpt($row['excerpt']);
        $post->setTitle($row['title']);
        $post->setContent($row['content']);
        $post->setLastUpdateDate($row['last_update_date']);
        $post->setCreationDate($row['creation_date']);

        return $post;
    }//end createPostWithRow()

    /**
     * Compare two post and tells if they are identical
     *
     * @param Post $basePost     Post without any modification
     * @param Post $modifiedPost Post which has been modified
     *
     * @return bool
     */
    public function checkIdenticalPost(Post $basePost, Post $modifiedPost): bool
    {
        $identicalPosts = true;
        foreach ($basePost as $fieldName => $fieldValue) {
            if ($fieldValue !== $modifiedPost[$fieldName]) {
                $identicalPosts = false;
                break;
            }
        }

        return $identicalPosts;
    }//end checkIdenticalPost()


}//end class

