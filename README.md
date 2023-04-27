# Blog

[![Codacy Badge](https://app.codacy.com/project/badge/Grade/b8aaefa3d469448492bd7a56bb1e0af6)](https://app.codacy.com/gh/Nerym492/Blog/dashboard?utm_source=gh&utm_medium=referral&utm_content=&utm_campaign=Badge_grade)

Projet 5 de mon parcours de développeur d'application - PHP/Symfony sur [Openclassrooms](https://openclassrooms.com/).  
Création d'un blog utilisant une architecture MVC.

## Informations

Pour ce projet, j’ai utilisé le moteur de templates Twig ainsi que le thème « [Clean blog](https://startbootstrap.com/previews/clean-blog)  » de Bootstrap.

## Installation

1. Ouvrir un Terminal à la racine du serveur ou localhost. (git bash sur Windows)
2. Exécuter la commande suivante en remplaçant « FolderName » par le nom que l’on veut donner au Projet :
   ```sh
   git clone https://github.com/Nerym492/Blog FolderName 
   ```
3. Créer un fichier .env à partir du fichier .env.example qui se trouve à la racine du projet.
4. Ouvrir le SGBD et créer une nouvelle base de données avec le nom qui lui a été donné dans le fichier .env
5. Exécuter le script [Blog.sql](https://github.com/Nerym492/Blog/Database/Blog.sql) se trouvant dans le dossier /Database en 
sélectionnant au préalable la base de données qui vient d’être créée.
6. Dans le dossier /public renommer le fichier .htaccess.localExample ou .htaccess.serverExample en .htaccess en fonction de la machine utilisée pour le projet.
7. Installer « composer » si il n’est pas installé. => https://getcomposer.org/download/
8. Exécuter la commande suivante :
   ```sh
   composer install --no-dev
   ```
9. Créer un utilisateur sur le site en se rendant sur la page « Sign in »
10. Un mail de confirmation est envoyé sur l’adresse mail utilisée. 
Cliquer sur le lien pour confirmer l’inscription.
11. Pour passer le compte en administrateur se rendre dans la base de données et exécuter la requête suivante en 
remplaçant « adminmail@example.com » par l’adresse mail utilisée : 
    ```sql
    UPDATE user SET user_type_id = 1 WHERE mail = 'adminmail@example.com'.
    ```
12.	Pour se connecter, entrer ses identifiants sur la page « logIn » . 
Une fois la connexion faite, un lien « Administration » apparaît en bas de chaque page.
(uniquement pour le compte Administrateur)

Un exemple de ce site est disponible à l'adresse suivante : https://my-blog.florian-pohu-49.fr/home/  