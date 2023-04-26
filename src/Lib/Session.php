<?php

namespace App\Lib;

use Exception;

/**
 * Management of the session variable
 */
class Session
{


    /**
     * Start a new session if none exist
     */
    public function __construct()
    {
        if (session_status() !== 2) {
            session_start();
        }

    }//end __construct()


    /**
     * Cleans and destroys the session variable
     *
     * @return void
     */
    public function destroy(): void
    {
        $_SESSION = [];
        session_destroy();

    }//end destroy()


    /**
     * Clears the session key variable
     *
     * @param array $keys Keys to clear in the session array.
     *
     * @return void
     */
    public function clearKeys(array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($_SESSION[$key]) === true) {
                unset($_SESSION[$key]);
            }
        }

    }//end clearKeys()


    /**
     * Get the session value for the key passed in parameter.
     * If the key not defined, the whole $_SESSION is returned.
     *
     * @param string $key Key of the session variable.
     *
     * @return mixed|null
     */
    public function get(string $key=""): mixed
    {
        $sessionValue = null;

        if ($key !== "" && isset($_SESSION[$key]) === true) {
            $sessionValue = filter_var($_SESSION[$key], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        } else if ($key === "" && isset($_SESSION) === true) {
            $sessionValue = filter_var_array($_SESSION, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        return $sessionValue;

    }//end get()


    /**
     * Create a new attribute and assign it with the value passed in parameter
     *
     * @param string $key   Session variable key.
     * @param string $value Session value associated with the name.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $key   = htmlspecialchars($key);
        $value = htmlspecialchars($value);

        $_SESSION[$key] = $value;

    }//end set()


}//end class
