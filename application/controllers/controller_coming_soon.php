<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}
class Controller_Coming_soon extends Controller
{
    function action_index()
    {
        //        header('HTTP/1.x 503 Service Unavailable');
        //        header("Status: 503 Service Unavailable");

        $this->view->keywords = $this->view->description = $this->view->title = $this->view->h1 = 'Čoskoro otvorí | ' . SITE_NAME;
        $this->view->generate('coming_view', 'coming');
    }

    function action_addSubscribe()
    {
        if (isset($_POST['email']) && $_POST['email']) {
            if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                if ($this->model->add_subsrcribe(["email" => $_POST['email'], "time_add" => time(), "ip" => GetRealIp()])) {
                    die(json_encode(array("type" => "success")));
                } else {
                    die(json_encode(array("type" => "error", "msg" => "Chyba, skuste prosim neskor")));
                }
            } else {
                die(json_encode(array("type" => "error", "msg" => "Zadajte prosim spravny e-mail")));
            }
        } else {
            die(json_encode(array("type" => "error", "msg" => "Zadajte prosim Vas e-mail")));
        }
        //$this->view->keywords = $this->view->description = $this->view->title = $this->view->h1 = 'Coming Soon | '.SITE_NAME;
        //$this->view->generate('coming_view','coming');

    }
}
