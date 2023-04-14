<?php

namespace App\Lib;

use Dotenv\Dotenv;

class Environment
{

    /**
     * @var Dotenv Environment vars
     */
    private Dotenv $env;


    /**
     * Create environment variables with the .env file
     */
    public function __construct()
    {
        // Root folder of the project.
        $this->env = Dotenv::createImmutable(
            dirname(__DIR__, 2)
        );
        $this->env->load();
        $this->checkRequiredEmpty(['DB_HOST', 'DB_NAME', 'DB_USER'], true);
        $this->checkRequiredEmpty(['DB_PASS'], false);

    }//end __construct()


    /**
     * Checking if the required vars are defined (and not empty optional)
     *
     * @param array $requiredVars Vars which need to be defined
     * @param bool  $isEmpty      Checks if it's empty or not
     * @return void
     */
    private function checkRequiredEmpty(array $requiredVars, bool $isEmpty): void
    {
        if ($isEmpty === true) {
            $this->env->required($requiredVars)->notEmpty();
        } else {
            $this->env->required($requiredVars);
        }

    }//end checkRequiredEmpty()


    /**
     * @param string $key Key of the environment variable
     * @return string|array|bool|null Value for the given key
     */
    public function getVar(string $key): string|array|bool|null
    {

        return filter_var($_ENV[$key], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    }//end getVar()


}//end class
