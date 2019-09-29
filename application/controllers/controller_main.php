<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}

class Controller_Main extends Controller
{
    function action_index()
    {
        $this->view->title = $this->view->description = $this->view->keywords = $this->Lang->getTranslate('title', 'home_page') . '';
        $this->view->h1 = $this->Lang->getTranslate('h1', 'home_page');

        $this->view->main_image_url = DOMAIN_FULL . FILESSTATIC . "/img/milaciky_icon_2.png";
        $this->view->category = $this->model->get_category();
        $param['status'] = 1;
        $this->view->posts = $this->model->get_posts($param, $this->view->contentData['p_limit'], $this->view->contentData['p_num']);
        $this->view->elementsCnt = $this->view->posts['cnt'];
        $this->view->popular_posts = $this->model->get_popular_posts($param, 3);
        $this->view->page = 'index';
        $this->view->generate();
    }

    function action_view_more($more)
    {
        $this->view->page = 'index';
        $this->view->generate();
    }
}
