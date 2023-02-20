<?php

namespace App\Repository;

use App\Entity\Post;
use App\Lib\Database\DatabaseConnection;

class PostRepository
{
    public DatabaseConnection $connexion;

    public function getPosts(): array
    {
        $statement = $this->connexion->getConnection()->prepare(
            "SELECT post_id, user_id, title, excerpt, content, last_update_date, creation_date
            FROM blog.post"
        );

        $statement->execute();
        $result = $statement->fetchAll();
        $posts = [];

        foreach ($result as $row){
            $post = new Post();
            $post->setUserId($row['userId']);
            $post->setPostId($row['postId']);
            $post->setExcerpt($row['exercpt']);
            $post->setTitle($row['title']);
            $post->setContent($row['content']);
            $post->setLastUpdateDate($row['last_update_date']);
            $post->setCreationDate($row['creation_date']);
            $posts[$post->getPostId()] = $post;
        }

        $statement->closeCursor();

        return $posts;

    }

    public function getPost(int $postId) : Post
    {
        $statement = $this->connexion->getConnection()->prepare(
            "SELECT post_id, user_id, title, excerpt, content, last_update_date, creation_date
            FROM blog.post p
            WHERE p.postId = :postId"
        );

        $statement->execute(['postId' => $postId]);
        $row = $statement->fetch();

        // On vérifie si on récupère bien le post
        if ($row) {
            $post = new Post();
            $post->setUserId($row['userId']);
            $post->setPostId($row['postId']);
            $post->setExcerpt($row['exercpt']);
            $post->setTitle($row['title']);
            $post->setContent($row['content']);
            $post->setLastUpdateDate($row['last_update_date']);
            $post->setCreationDate($row['creation_date']);
        }
        else{
            echo "Erreur page 404";
        }

        $statement->closeCursor();

        return $post;
    }
}