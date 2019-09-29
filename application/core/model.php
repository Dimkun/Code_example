<?php
class Model
{
    public $time_auth = 2419200; //день
    //public $time_auth = 2419200;// 4 недели 43200; //пол дня
    public $time_cookie = 604800; //неделя
    public $time_cookie_month = 2419200; //4 недели
    public $where_list = [];
    public $show_fields = '';

    function __construct()
    {
        //        $this->view = new View();
    }

    /* USER */
    public function lockUser()
    {
        if (!isset($_COOKIE["auth_user"]) || !$_COOKIE["auth_user"] || !$this->getUser()) {
            _redirect('/');
        } else return (int) vim_decrypt($_COOKIE["auth_user"], ENCRYPTION_KEY);
    }
    public function getUser()
    {
        if (isset($_COOKIE["auth_user"]) && $_COOKIE["auth_user"]) {
            return (int) vim_decrypt($_COOKIE["auth_user"], ENCRYPTION_KEY);
        } else return false;
    }
    public function setUser($id)
    {
        SetCookie("auth_user", vim_encrypt($id, ENCRYPTION_KEY), time() + $this->time_auth, '/', DOMAIN_WWW);
    }
    public function destroyUser()
    {
        SetCookie("auth_user", 0, time() - $this->time_auth, '/', DOMAIN_WWW);
    }

    /* ADMIN */
    public function lockAdmin()
    {
        if (!isset($_COOKIE["admin"]) || !$_COOKIE["admin"] || !$this->getAdmin()) {
            $_SESSION['last_uri'] = REQUEST_URI;
            _redirect('/sign_in/');
        } else return (int) vim_decrypt($_COOKIE["admin"], ENCRYPTION_KEY_ADM);
    }
    public function getAdmin()
    {
        if (isset($_COOKIE["admin"]) && $_COOKIE["admin"]) {
            return (int) vim_decrypt($_COOKIE["admin"], ENCRYPTION_KEY_ADM);
        } else return false;
    }
    public function setAdmin($id)
    {
        SetCookie("admin", vim_encrypt($id, ENCRYPTION_KEY_ADM), time() + $this->time_auth, '/', DOMAIN_WWW);
    }
    public function destroyAdmin()
    {
        SetCookie("admin", 0, time() - $this->time_auth, '/', DOMAIN_WWW);
    }


    public function setViewData($data = array(), $type = 'edit')
    {
        if (!is_array($data) || count($data) == 0) return false;
        if (isset($_COOKIE["ViewData"]) && !empty($_COOKIE["ViewData"])) {
            $data_cookie = $this->getViewData();
            if ($type == 'edit') {
                $new_data = serialize(array_replace_recursive($data_cookie, $data));
                SetCookie("ViewData", $new_data, time() + $this->time_cookie, '/', DOMAIN_WWW);
            } elseif ($type == 'del') {
                $new_mass = $data_cookie;
                foreach ($data as $key => $value) {
                    if (isset($new_mass[$key])) unset($new_mass[$key]);
                }
                $new_data = serialize(array_replace($data_cookie, $new_mass));
                SetCookie("ViewData", $new_data, time() + $this->time_cookie, '/', DOMAIN_WWW);
            }
        } else SetCookie("ViewData", serialize($data), time() + $this->time_cookie, '/', DOMAIN_WWW);
    }
    public function getViewData()
    {
        if (isset($_COOKIE["ViewData"]) && !empty($_COOKIE["ViewData"])) {
            return unserialize($_COOKIE["ViewData"]);
        } else return false;
    }

    public function GetItemsByParam($param = [], $limit = 9, $start = 0, $cnt = true)
    {
        $start *= $limit;

        extract(TrimArray($param));

        $this->gen_sql($param);

        $res = dbSelectAll(
            'SELECT SQL_CALC_FOUND_ROWS ' . $this->show_fields . ' FROM 
                `object` o 
                LEFT JOIN `side` s ON o.`id` = s.`object_id` 
                LEFT JOIN `location` l ON o.`city_id` = l.`id_page` 
                LEFT JOIN `location__' . $lang . '` ll ON ll.`id_page` = l.`id_page` 
                LEFT JOIN `types` t ON o.`type` = t.`id`' . (count($this->where_list) > 0 ? ' WHERE ' . implode(' AND ', $this->where_list) : '')
                . ' ORDER BY ' . $this->ORDER_BY . ' LIMIT ' . $start . ',' . $limit,
            $cnt
        );
        return $res;
    }

    /**
     * Get Posts
     * @param array $filter_params
     * @param int $limit
     * @param int $start
     * @return array posts
     */
    public function get_posts($filter_params = [], $limit = 0, $start = 0)
    {
        $where = [];
        $start *= $limit;
        if (isset($filter_params['status'])) {
            $where[] = "`posts`.`status`='" . $filter_params['status'] . "'";
        }
        if (isset($filter_params['id_category'])) {
            $where[] = "`posts`.`id_category`='" . $filter_params['id_category'] . "'";
        }
        if (isset($filter_params['parent'])) {
            $where[] = "`category`.`parent`='" . $filter_params['parent'] . "'";
        }
        if (count($where)) {
            $where_str = 'WHERE ' . implode(' and ', $where);
        } else {
            $where_str = "";
        }
        $posts = dbSelectAll('SELECT SQL_CALC_FOUND_ROWS *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post, posts.status as post_status FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) ' . $where_str . ' ORDER BY `posts`.id DESC LIMIT ' . $start . ',' . $limit, 1);
        $db_main_category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 '); //and `parent`=0
        $main_cat = [];
        foreach ($db_main_category as $cat) {
            $main_cat[$cat['id']] = ["name" => $cat['name'], "alias" => $cat['alias']];
        }
        foreach ($posts['res'] as $key => $post) {
            $posts['res'][$key]['url'] = makeLangUrl($main_cat[$post['parent']]["alias"] . "/" . $post['cat_alias'] . "/" . $post['post_alias'], "sk");
            $posts['res'][$key]['categories'] = [
                ["name" => $main_cat[$post['parent']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"], "sk")],
                ["name" => $main_cat[$post['id_category']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"] . "/" . $post['cat_alias'], "sk")]
            ];
        }
        //        var_dump($posts);
        return $posts;
    }

    /**
     * Get popular Posts
     * @param array $filter_params
     * @param int $limit
     * @param int $start
     * @return array posts
     */

    public function get_popular_posts($filter_params = [], $limit = 0, $start = 0)
    {
        $where = [];
        $start *= $limit;
        $filter_params['status'] = 1;
        if (isset($filter_params['status'])) {
            $where[] = "`posts`.`status`='" . $filter_params['status'] . "'";
        }
        if (count($where)) {
            $where_str = 'WHERE ' . implode(',', $where);
        } else {
            $where_str = "";
        }
        $posts = dbSelectAll('SELECT SQL_CALC_FOUND_ROWS *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) ' . $where_str . ' ORDER BY `posts`.count_view DESC LIMIT ' . $start . ',' . $limit, 1);
        $db_main_category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 '); //and `parent`=0
        $main_cat = [];
        foreach ($db_main_category as $cat) {
            $main_cat[$cat['id']] = ["name" => $cat['name'], "alias" => $cat['alias']];
        }
        foreach ($posts['res'] as $key => $post) {
            $posts['res'][$key]['url'] = makeLangUrl($main_cat[$post['parent']]["alias"] . "/" . $post['cat_alias'] . "/" . $post['post_alias'], "sk");
            $posts['res'][$key]['categories'] = [
                ["name" => $main_cat[$post['parent']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"], "sk")],
                ["name" => $main_cat[$post['id_category']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"] . "/" . $post['cat_alias'], "sk")]
            ];
        }
        return $posts;
    }

    public function get_category()
    {
        $category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 order by parent,id');
        $category_list = array();
        foreach ($category as $cat_one) {
            if ($cat_one["parent"] == 0) {
                $category_list[$cat_one["id"]] = ["name" => $cat_one["name"], "url" => $cat_one["alias"]];
            } else {
                $category_list[$cat_one["parent"]]["sub_cat"][] = ["name" => $cat_one["name"], "url" => $cat_one["alias"]];
            }
        }
        return $category_list;
    }
    public function get_category_admin()
    {
        $category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 order by parent,id');
        //        $category_list=array();
        //        foreach ($category as $cat_one){
        //            if($cat_one["parent"]==0){
        //                $category_list[$cat_one["id"]]=["name"=>$cat_one["name"],"url"=>$cat_one["alias"]];
        //            }else{
        //                $category_list[$cat_one["parent"]]["sub_cat"][]=["name"=>$cat_one["name"],"url"=>$cat_one["alias"]];
        //            }
        //        }
        return $category;
    }
    public function get_subcategory($subcategory)
    {
        if ($subcategory) {
            $sub_category = dbSelect('SELECT * FROM `category` WHERE `alias`="' . $subcategory . '" and `status`=1');
        } else {
            $sub_category = FALSE;
        }
        return $sub_category;
    }

    public function get_post($filter_params = [])
    {
        $where = [];
        if (isset($filter_params['alias']) && $filter_params['alias']) {
            $where[] = "posts.alias='" . $filter_params['alias'] . "'";
            dbQuery("UPDATE posts SET count_view=count_view+1 WHERE `alias`='" . $filter_params['alias'] . "'");
        }
        if (isset($filter_params['id_post']) && $filter_params['id_post']) {
            $where[] = "posts.id='" . $filter_params['id_post'] . "'";
        }
        if (count($where)) {
            $post = dbSelect('SELECT *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post, posts.status as post_status, posts.title as post_title, posts.description as post_description, posts.keywords as post_keywords FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) WHERE ' . implode(',', $where) . '');
            if (isset($post['id'])) {

                $db_main_category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 '); //and `parent`=0
                $main_cat = [];
                foreach ($db_main_category as $cat) {
                    $main_cat[$cat['id']] = ["name" => $cat['name'], "alias" => $cat['alias']];
                }
                $post['breadcrumbs'] = [
                    ["name" => $main_cat[$post['parent']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"], "sk")],
                    ["name" => $main_cat[$post['id_category']]["name"], "url" => makeLangUrl($main_cat[$post['parent']]["alias"] . "/" . $post['cat_alias'], "sk")]
                ];

                $post['next_post'] = dbSelect('SELECT *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) WHERE posts.id>' . $post['id_post'] . ' and posts.id_category=' . $post['id_category'] . ' and posts.status=1 order by posts.id ');
                if ($post['next_post']) {
                    $post['next_post']['url'] = makeLangUrl($main_cat[$post['next_post']['parent']]["alias"] . "/" . $post['next_post']['cat_alias'] . "/" . $post['next_post']['post_alias'], "sk");
                }
                $post['prev_post'] = dbSelect('SELECT *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) WHERE posts.id<' . $post['id_post'] . ' and posts.id_category=' . $post['id_category'] . ' and posts.status=1 order by posts.id DESC');
                if ($post['prev_post']) {
                    $post['prev_post']['url'] = makeLangUrl($main_cat[$post['prev_post']['parent']]["alias"] . "/" . $post['prev_post']['cat_alias'] . "/" . $post['prev_post']['post_alias'], "sk");
                }
            }
        } else {
            $post = FALSE;
        }
        return $post;
    }
    public function get_post_by_id($filter_params = [])
    {
        $where = [];

        if (isset($filter_params['id_post']) && $filter_params['id_post']) {
            $where[] = "id='" . $filter_params['id_post'] . "'";
        }
        if (count($where)) {
            $post = dbSelect('SELECT * FROM `posts` WHERE ' . implode(',', $where) . '');
            if (isset($post['id'])) { }
        } else {
            $post = FALSE;
        }
        return $post;
    }
    public function get_around_post($filter_params = [])
    {
        $where = [];
        if (isset($filter_params['id_post']) && $filter_params['id_post']) {
            $where[] = "`id`='" . $filter_params['id_post'] . "'";
        }
        if (count($where)) {
            $next_post = dbSelect('SELECT * FROM `posts` WHERE ' . implode(',', $where) . '');
            if (isset($post['id'])) { }
        } else {
            $post = FALSE;
        }
        return $post;
    }

    public function add_subsrcribe($data)
    {
        if ($data) {
            return dbInsert('subscribe', $data);
        } else {
            return false;
        }
    }
}
