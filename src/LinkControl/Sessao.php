<?php

namespace LinkControl;

use \ConnCrud\TableCrud;

class Sessao
{
    private $logged = false;
    private $mensagem;

    public function __construct()
    {
        if(!session_start())
            session_start();

        $this->checkSession();
    }

    private function checkSession()
    {
        if (class_exists('\SessionControl\Login'))
            $this->checkLoginToken();
    }

    private function checkLoginToken()
    {
        if ($this->isLoggedIn())
            $this->checkSessionWrong();

        if (!$this->isLoggedIn())
            $this->checkCookieInfo();
        else
            $this->logged = true;

        define("LOGGED", $this->logged);
    }

    /**
     * @return bool
     */
    public function isLogged(): bool
    {
        return $this->logged;
    }

    /**
     * @return mixed
     */
    public function getMensagem()
    {
        return $this->mensagem;
    }

    private function isLoggedIn()
    {
        return isset($_SESSION['userlogin']);
    }

    private function checkSessionWrong()
    {
        if (!isset($_SESSION['userlogin']['email']))
            unset($_SESSION['userlogin']);
    }

    private function unsetCookie()
    {
        $token = new TableCrud("usuarios");
        $token->load("token", $_COOKIE['token']);
        if ($token->exist()) {
            $token->token = null;
            $token->token_expira = null;
            $token->save();
        }

        setcookie("token", 0, time() - 1, "/");
    }

    public function deslogar()
    {
        $this->unsetCookie();
        unset($_SESSION['userlogin']);
    }

    private function checkCookieInfo()
    {
        if (isset($_COOKIE['token'])) {

            $token = new TableCrud("usuarios");
            $token->load("token", $_COOKIE['token']);
            $beforeDate = date('Y-m-d H:i:s', strtotime("-2 months", strtotime(date("Y-m-d H:i:s"))));
            if ($token->exist() && $token->status === 1 && $token->token_expira > $beforeDate) {
                $_SESSION['userlogin'] = $token->getDados();
                $token->token_expira = date("Y-m-d H:i:s");
                $token->save();
                $this->logged = true;
                setcookie("token", $token, time() + (86400 * 30 * 3), "/"); // 2 meses de cookie
                header("Location: " . HOME . "dashboard");

            } else {
                $this->unsetCookie();
            }
        }
    }

}