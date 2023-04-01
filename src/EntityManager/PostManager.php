<?php

namespace App\EntityManager;

use App\Entity\Post;
use App\Lib\DatabaseConnection;

class PostManager
{

    /**
     * @param int $pageNum Page currently being read in the pagination
     * @param int $postLimit Number of rows per page
     * @return array postData, numberOfPosts before being limited, currentPage
     * @throws \Exception
     */
    public function getPosts(int $pageNum, int $postLimit): array
    {
        $postsRowsData = [];
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT COUNT(p.post_id) as 'nbPosts'
                     FROM blog.post p"
        );

        $statement->execute();
        $postsRowsCount = $statement->fetch()['nbPosts'];

        if ($postsRowsCount > 0) {
            $startToPost = ($postLimit * $pageNum) - $postLimit;
            /* The pagination will display the previous page with 0 line if the current page is empty
               Example 4 rows total but 2 pages with 4 rows per page (page 2 empty)
               So we need one less page and to start at the beginning of this one*/
            if ($startToPost !== 0 && $startToPost % $postsRowsCount === 0){
                $pageNum--;
                $startToPost -= $postLimit;
            }
            $postsRowsData = $connexion->execQueryWithLimit($postsRowsCount, $postLimit, $startToPost);
        }

        $posts = [];

        foreach ($postsRowsData as $row) {
            $post = new Post();
            $this->setPostWithRow($post, $row);
            $posts[$post->getPostId()] = ['post' => $post, 'pseudoUser' => $row['pseudo']];
        }

        return ['data' => $posts, 'nbLines' => $postsRowsCount, 'currentPage' => $pageNum];
    }

    public function getPost(int $postId): ?Post
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, p.creation_date
            FROM blog.post p
            WHERE p.post_id = :postId"
        );

        $statement->execute([':postId' => $postId]);
        $row = $statement->fetch();


        // On vérifie si on récupère bien le post
        if ($row) {
            $post = new Post();
            $this->setPostWithRow($post, $row);
        } else {
            $post = null;
        }

        $statement->closeCursor();

        return $post;
    }

    public function createPost(array $form): bool
    {
        $dateNow = new \DateTime('now', new \DateTimeZone($_ENV['TIMEZONE']));
        $dateNow = $dateNow->format('Y-m-d H:i:s');
        $connexion = new DatabaseConnection();

        try {
            $statement = $connexion->getConnection()->prepare(
                "INSERT INTO blog.post
                   (user_id, title, excerpt, content, last_update_date, creation_date)
                   VALUES(:user_id, :title, :excerpt, :content, :last_update_date, :creation_date);"
            );

            $statement->execute([
                ':user_id' => $_SESSION['user_id'],
                ':title' => $form['title'],
                ':excerpt' => $form['excerpt'],
                ':content' => $form['content'],
                ':last_update_date' => $dateNow,
                ':creation_date' => $dateNow
            ]);
            $isCreated = $statement->rowCount() == 1;
        } catch (\Throwable $e) {
            if (!isset($_SESSION['message'])) {
                $_SESSION['message'] = "An error occurred while creating the post";
                $_SESSION['messageClass'] = "danger";
            }
            $isCreated = false;
        }


        return $isCreated;
    }

    /**
     * Update title, excerpt and content field in the database
     * @param Post $post Post object
     * @return bool True if updated successfully else false
     */
    public function updatePost(Post $post): bool
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "UPDATE post
                   SET title=:title, excerpt=:excerpt, content=:content
                   WHERE post_id=:post_id"
        );
        $statement->execute([
            ':title' => $post->getTitle(),
            ':excerpt' => $post->getExcerpt(),
            ':content' => $post->getContent(),
            ':post_id' => $post->getPostId()
        ]);

        return $statement->rowCount() == 1;
    }

    /**
     * @param int $postId
     * @return bool True if deleted else false
     */
    public function deletePost(int $postId): bool
    {
        $connextion = new DatabaseConnection();
        $statement = $connextion->getConnection()->prepare(
            "DELETE FROM post
                   WHERE post_id=:postId"
        );

        $statement->execute([
            ':postId' => $postId
        ]);

        return $statement->rowCount() == 1;
    }

    /**
     * @param Post $post
     * @param $row
     * @return void
     * @throws \Exception
     */
    private function setPostWithRow(Post $post, $row): void
    {
        $post->setUserId($row['user_id']);
        $post->setPostId($row['post_id']);
        $post->setExcerpt($row['excerpt']);
        $post->setTitle($row['title']);
        $post->setContent($row['content']);
        $post->setLastUpdateDate(new \DateTime($row['last_update_date']));
        $post->setCreationDate(new \DateTime($row['creation_date']));
    }
}