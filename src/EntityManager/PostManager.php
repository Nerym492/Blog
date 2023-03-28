<?php

namespace App\EntityManager;

use App\Entity\Post;
use App\Lib\DatabaseConnection;

class PostManager
{

    public function getPosts(int $pageNum): array
    {
        $connexion = new DatabaseConnection();

        try {
            $statement = $connexion->getConnection()->prepare(
                "SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, p.creation_date,
                    u.pseudo
            FROM blog.post p
            LEFT OUTER JOIN blog.user u on p.user_id = u.user_id
            ORDER BY p.post_id DESC"
            );
            $statement->execute();
            $result = $statement->fetchAll();
            $statement->closeCursor();
        } catch (\Throwable $e) {
            if (!isset($_SESSION['message'])) {
                $_SESSION['message'] = "An error occurred while getting Posts";
                $_SESSION['messageClass'] = "danger";
            }
            $result = [];
        }

        $posts = [];

        foreach ($result as $row) {
            $post = new Post();
            $this->setPostWithRow($post, $row);
            $posts[$post->getPostId()] = ['post' => $post, 'pseudoUser' => $row['pseudo']];
        }

        return $posts;

    }

    public function getPost(int $postId): Post
    {
        $connexion = new DatabaseConnection();

        $statement = $connexion->getConnection()->prepare(
            "SELECT p.post_id, p.user_id, p.title, p.excerpt, p.content, p.last_update_date, p.creation_date
            FROM blog.post p
            WHERE p.post_id = :postId"
        );

        $statement->execute([':postId' => $postId]);
        $row = $statement->fetch();

        $post = new Post();
        // On vérifie si on récupère bien le post
        if ($row) {
            $this->setPostWithRow($post, $row);
        } else {
            echo "Erreur page 404";
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