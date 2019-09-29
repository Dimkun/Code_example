<?php

/**
 * Sing in controller
 */
class Controller_SignIn extends Controller
{
    public function action_index()
    {
        $get_url = explode('?', $_SERVER['REQUEST_URI']);
        $routes = explode('/', $get_url[0]);
        $this->view->template = "admin";
        $setting = array(
            'title' => 'Sign In',
            'description' => 'Sign In',
            'keywords' => 'Sign In'
        );
        $data = array();
        $this->view->generate('sign_in_view.php', 'admin_auth', $data, $setting);
    }
}
