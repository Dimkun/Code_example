<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}
class Controller_Error404 extends Controller
{
    function action_index()
    {
        header('Status: 404 Not Found');
        header('HTTP/1.1 404 Not Found');

        $code = 404;
        $this->view->category = $this->model->get_category();
        $this->view->h1 = $this->Lang->getTranslate('h1', $code . '_page_setting');
        $this->view->title = $this->Lang->getTranslate('page_' . $code . '_title');
        $this->view->description = $this->Lang->getTranslate('h1', $code . '_page_setting');
        $this->view->keywords = $this->Lang->getTranslate('h1', $code . '_page_setting');

        $this->view->generate('error/404_view');
    }
}
