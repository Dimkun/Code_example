<?php
class Controller
{
    public $model;
    public $view;
    public $time;
    public $time_str;

    function __construct($Lang, $url_param = [])
    {
        $this->model = new Model();
        $this->view = new View();
        $this->view->Phones = new Phones();
        $this->time = time();
        $this->time_str = strftime('%k:%M:%S', $this->time);
        if ($Lang) {
            $this->view->Lang = $this->Lang = $Lang;
            $this->view->url_param = $this->url_param = $url_param;
        }

        $this->view->breadcrumbs = [];
        $this->view->mobile = $this->view->full_version = $this->view->aterms = false;

        if (isset($_COOKIE['full_version']) && $_COOKIE['full_version'] == 1) $this->view->full_version = true;
        if (isset($_COOKIE['aterms']) && $_COOKIE['aterms'] == 1) $this->view->aterms = true;

        if (!$this->view->full_version) {
            $mobile = new Mobile_Detect();
            if ($mobile->isMobile() and !$mobile->isTablet()) $this->view->mobile = true;
        }

        $this->view->ViewData = $this->model->getViewData();

        $this->view->contentData['p_num'] = isset($_GET['page']) && is_numeric($_GET['page']) && 0 < $_GET['page'] ? $_GET['page'] - 1 : 0;
        if (!isset($this->view->ViewData['view'])) $this->view->ViewData['view'] = 'grid';
        if (isset($this->view->ViewData['limit']) && $this->view->ViewData['limit'] > 0)
            $this->view->contentData['p_limit'] = $this->view->ViewData['limit'];
        else $this->view->contentData['p_limit'] = 6;

        if ($this->view->mobile && $this->view->contentData['p_limit'] > 6) {
            $this->model->setViewData(['limit' => 6], 'edit');
            $this->view->contentData['p_limit'] = 6;
        }
    }

    function meta_load($id = 0)
    {
        if (!$id) return false;
        $this->view->setting = dbSelect('SELECT h1_' . $this->Lang->type . ' as h1, title_' . $this->Lang->type . ' as title, keywords_' . $this->Lang->type . ' as keywords, description_' . $this->Lang->type . ' as description, text_' . $this->Lang->type . ' as text, bg_img, bg_class FROM ' . DB_PREFIX . 'page WHERE id_page=' . $id);
        $this->view->h1 = $this->view->setting['h1'];
        $this->view->title = $this->view->setting['title'];
        $this->view->description = $this->view->setting['description'];
        $this->view->keywords = $this->view->setting['keywords'];
        $this->view->text = $this->view->setting['text'];
        $this->view->bg_img = $this->view->setting['bg_img'];
        $this->view->bg_class = $this->view->setting['bg_class'];
    }

    function meta_exp()
    {
        if (isset($_GET['cat']) && is_array($_GET['cat']) && $_GET['cat']) {
            $list_cat_name = [];
            foreach ($_GET['cat'] as $key => $status) {
                if ('on' == $status) {
                    if (isset($this->view->categorys[$key])) {
                        $list_cat_name[] = $this->view->categorys[$key]['name_' . $this->Lang->type];
                    } else {
                        foreach ($this->view->categorys as $category) {
                            if (isset($category['sub_category'][$key])) {
                                $list_cat_name[] = $category['sub_category'][$key]['name_' . $this->Lang->type];
                                break;
                            }
                        }
                    }
                }
            }

            if ($list_cat_name) {
                $this->view->h1 .= ' | ' . implode(', ', $list_cat_name);
                $this->view->title .= ' | ' . implode(', ', $list_cat_name);
                $this->view->description .= ' - ' . implode(', ', $list_cat_name);
                $this->view->keywords .= ', ' . implode(', ', $list_cat_name);
            }
        }
        if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 0) {
            $this->view->h1 .= ', ' . $this->lang['page'] . ' ' . $_GET['page'];
            $this->view->title .= ' - ' . $this->lang['page'] . ' ' . $_GET['page'];
            $this->view->description .= ' - ' . $this->lang['page'] . ' ' . $_GET['page'];
            $this->view->keywords .= ', ' . $this->lang['page'] . ' ' . $_GET['page'];
        }
    }
    function categorys_cnt($cnt = 0)
    {
        $categorys = dbSelectAll('SELECT * FROM `' . DB_PREFIX . 'category` ORDER BY `sort` DESC');
        foreach ($categorys as $key => $value) {
            if ($value['sub'] > 0) {
                $this->view->categorys[$value['sub']]['id_sub_category'][$value['id_category']] = $value['id_category'];
                $this->view->categorys[$value['sub']]['sub_category'][$value['id_category']] = $value;
                $this->view->sub_categorys[] = $value;
            } elseif (isset($this->view->categorys[$value['id_category']])) $this->view->categorys[$value['id_category']] += $value;
            else $this->view->categorys[$value['id_category']] = $value;
        }

        if ($cnt) {
            $where = [];
            $where[] = 'p.`status`="1"';

            if ($res = dbSelectAll('SELECT p.id_category, count(p.id_product) as cnt FROM ' . DB_PREFIX . 'product p LEFT JOIN ' . DB_PREFIX . 'category c ON p.id_category=c.id_category WHERE ' . implode(' AND ', $where) . ' GROUP BY p.id_category')) {
                foreach ($res as $key => $value) $this->view->category_cnt[$value['id_category']] = $value['cnt'];
                unset($res);
            }
        }
    }
}
