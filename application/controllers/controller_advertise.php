<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}
class Controller_Advertise extends Controller
{
    function action_index()
    {
        $this->view->category = $this->model->get_category();
        //        $this->view->h1 = $this->view->agency['company'].' - '.$this->Lang->getTranslate('h1','contact_page');
        $this->view->h1 = "Inzercia";
        $this->view->title = "Inzercia";
        $this->view->description = "Inzercia";
        $this->view->keywords = "Inzercia";

        $this->view->breadcrumbs[] = ["name" => "Domov", "url" => DOMAIN_FULL];
        $this->view->breadcrumbs[] = ["name" => "Inzercia", "url" => ""];
        //        $this->view->breadcrumbs[] = $this->Lang->getTranslate('h1','contact_page');
        $this->view->popular_posts = $this->model->get_popular_posts([], 3);
        $this->view->generate('advertise');
    }
}
