<?php

namespace App\Lib;

class Session
{
    public function __construct()
    {
        if (session_status() !== 2){
            session_start();
        }
    }

    public function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public function setAttribute(string $name, string $value): void
    {
        $name = htmlspecialchars($name);
        $value = htmlspecialchars($value);
        $_SESSION[$name] = $value;
    }
}