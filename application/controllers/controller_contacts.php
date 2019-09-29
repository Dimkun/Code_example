<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}
class Controller_Contacts extends Controller
{
    function action_index()
    {
        $this->view->category = $this->model->get_category();
        $this->view->h1 = "Kontakty";
        $this->view->title = "Kontakty" . " - " . SITE_NAME;
        $this->view->description = "Kontakty";
        $this->view->keywords = "Kontakty";

        $this->view->breadcrumbs[] = ["name" => "Domov", "url" => DOMAIN_FULL];
        $this->view->breadcrumbs[] = ["name" => "Kontakty", "url" => ""];
        $this->view->popular_posts = $this->model->get_popular_posts([], 3);
        $this->view->generate('contacts');
    }
}
