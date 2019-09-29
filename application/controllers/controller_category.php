<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}

class Controller_Category extends Controller
{
    function action_index()
    {
        $this->view->category = $this->model->get_category();
        $param['status'] = 1;
        $this->view->posts = $this->model->get_posts($param, 5);

        $this->view->page = 'index';
        $this->view->generate();
    }

    function action_cats()
    {
        $page_text_title = $this->view->contentData['p_num'] > 0 ? " - stránka " . ($this->view->contentData['p_num'] + 1) : "";
        $this->view->category = $this->model->get_category();
        $db_main_category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 ');
        foreach ($db_main_category as $cat) {
            $main_cat[$cat['id']] = ["name" => $cat['name'], "alias" => $cat['alias'], "title" => $cat['title'], "description" => $cat['description'], "keywords" => $cat['keywords']];
        }
        if (isset($this->url_param[3]) && $this->url_param[3]) {
            if (isset($this->url_param[4]) && $this->url_param[4] != '') {
                Route::ErrorPage404($this->Lang);
            }
            if ($this->view->post = $this->model->get_post(['alias' => $this->url_param[3]])) {
                if ($this->view->post['cat_alias'] != $this->url_param[2]) Route::ErrorPage404($this->Lang);
                if ($this->view->post['post_status'] == 0) Route::ErrorPage404($this->Lang);
                if ($this->url_param[2] == 'plemena_maciek') {
                    $this->view->page = 'one_post_breed';
                } else {
                    $this->view->page = 'one_post';
                }

                $this->view->main_image_url = DOMAIN_FULL . get_img_url($this->view->post['img'], "big");
                $this->view->title = (trim($this->view->post['post_title']) != "" ? $this->view->post['post_title'] : $this->view->post['theme']) . " - " . SITE_NAME;
                $this->view->description = (trim($this->view->post['post_description']) != "" ? $this->view->post['post_description'] : $this->view->post['theme']);
                $this->view->keywords = (trim($this->view->post['post_keywords']) != "" ? $this->view->post['post_keywords'] : $this->view->post['theme']);

                $this->view->breadcrumbs[] = ["name" => "Domov", "url" => DOMAIN_FULL];
                $this->view->breadcrumbs = array_merge($this->view->breadcrumbs, $this->view->post["breadcrumbs"]);
                $this->view->breadcrumbs[] = ["name" => $this->view->post['theme'], "url" => ""];
            } else {
                Route::ErrorPage404($this->Lang);
            }
        } elseif ($this->url_param[2]) {
            if ($this->view->sub_category = $this->model->get_subcategory($this->url_param[2])) {
                $param['status'] = 1;

                $param['id_category'] = $this->view->sub_category['id'];

                $this->view->main_image_url = DOMAIN_FULL . FILESSTATIC . "/img/milaciky_icon_2.png";
                $this->view->title = (trim($this->view->sub_category['title']) != "" ? $this->view->sub_category['title'] : $this->view->sub_category['name']) . " - " . SITE_NAME . $page_text_title;
                $this->view->description = (trim($this->view->sub_category['description']) != "" ? $this->view->sub_category['description'] : $this->view->sub_category['name']);
                $this->view->keywords = (trim($this->view->sub_category['keywords']) != "" ? $this->view->sub_category['keywords'] : $this->view->sub_category['name']);
                $this->view->h1 = $this->view->sub_category['name'];
                //                var_dump($this->view->category);
                $this->view->breadcrumbs[] = ["name" => "Domov", "url" => DOMAIN_FULL];
                $this->view->breadcrumbs[] = ["name" => $main_cat[$this->view->sub_category['parent']]["name"], "url" => makeLangUrl($main_cat[$this->view->sub_category['parent']]["alias"], "sk")];
                $this->view->breadcrumbs[] = ["name" => $main_cat[$this->view->sub_category['id']]["name"] . $page_text_title, "url" => ""];

                $this->view->url_first_page = implode("/", $this->url_param);
                $this->view->posts = $this->model->get_posts($param, $this->view->contentData['p_limit'], $this->view->contentData['p_num']);
                $this->view->elementsCnt = $this->view->posts['cnt'];
                if (count($this->view->posts['res']) > 0) {
                    $this->view->page = 'category';
                } else {
                    $this->view->page = 'category';
                }
            } else {
                Route::ErrorPage404($this->Lang);
            }
        } else {
            $param['status'] = 1;
            $param['parent'] = 1;
            $this->view->posts = $this->model->get_posts($param, $this->view->contentData['p_limit'], $this->view->contentData['p_num']);
            $this->view->elementsCnt = $this->view->posts['cnt'];

            $this->view->main_image_url = DOMAIN_FULL . FILESSTATIC . "/img/milaciky_icon_2.png";
            $this->view->title = (isset($main_cat[1]['title']) && trim($main_cat[1]['title']) != "" ? $main_cat[1]['title'] : $main_cat[1]['name']) . " - " . SITE_NAME . $page_text_title;
            $this->view->description = (isset($main_cat[1]['description']) && trim($main_cat[1]['description']) != "" ? $main_cat[1]['description'] : $main_cat[1]['name']);
            $this->view->keywords = (isset($main_cat[1]['keywords']) && trim($main_cat[1]['keywords']) != "" ? $main_cat[1]['keywords'] : $main_cat[1]['name']);
            $this->view->h1 = $main_cat[1]["name"];

            $this->view->breadcrumbs[] = ["name" => "Domov", "url" => DOMAIN_FULL];
            $this->view->breadcrumbs[] = ["name" => $main_cat[1]["name"] . $page_text_title, "url" => ""];

            $this->view->page = 'category';
        }
        $this->view->popular_posts = $this->model->get_popular_posts([], 3);
        $this->view->generate();
    }
}
