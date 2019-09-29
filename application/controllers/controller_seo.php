<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
}
class Controller_Seo extends Controller
{
    public $robots = '';
    public function action_robots()
    {
        //        if(DOMAIN_SUB) _redirect301(DOMAIN_CURRENT.REQUEST_URI);
        header("Content-Type: text/plain");

        $this->robots .= "User-Agent: *" . PHP_EOL;
        $this->robots .= "Disallow: /" . PHP_EOL;
        $this->robots .= "Host: https://" . DOMAIN_CURRENT . PHP_EOL;
        $this->robots .= "Sitemap: https://" . DOMAIN_CURRENT . "/sitemap.xml" . PHP_EOL;

        echo $this->robots;
        //        file_put_contents(homeRoot.'/robots.txt', $this->robots);
    }

    function action_sitemap()
    {
        header("Content-Type:text/xml");
        set_time_limit(21600);
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';
        $xml .=
            "<url>
                <loc>" . DOMAIN_FULL . "</loc>
                <lastmod>" . date("c", mktime(0, 0, 1, date("m"), date("d"), date("Y"))) . "</lastmod>
                <changefreq>daily</changefreq>
            </url>" . PHP_EOL;


        // Categories
        $db_main_category = dbSelectAll('SELECT * FROM `category` WHERE `status`=1 order by parent'); //and `parent`=0
        $main_categories = [];
        foreach ($db_main_category as $cat) {
            $main_categories[$cat['id']] = ["name" => $cat['name'], "alias" => $cat['alias']];
            if ($cat['parent']) $main_categories[$cat['id']]["url"] = $main_categories[$cat['parent']]['alias'] . "/" . $cat['alias'];
            else $main_categories[$cat['id']]["url"] = $cat['alias'];
        }
        //        var_dump($main_cat);
        foreach ($main_categories as $m_category) {
            $xml .=
                "<url>
                    <loc>" . makeLangUrl($m_category['url'], "sk") . "</loc>
                    <lastmod>" . date("c", mktime(0, 0, 1, date("m"), date("d"), date("Y"))) . "</lastmod>
                    <changefreq>daily</changefreq>
                </url>" . PHP_EOL;
        }

        // Posts
        if ($posts = dbSelectAll('SELECT *,category.alias as cat_alias,posts.alias as post_alias,posts.id as id_post,posts.time_update FROM `posts` LEFT JOIN category ON (posts.id_category=category.id) WHERE `posts`.`status`=1 ORDER BY `posts`.id')) {
            //            var_dump($posts);
            foreach ($posts as $key => $post) {
                $posts[$key]['url'] = $main_categories[$post['parent']]["alias"] . "/" . $post['cat_alias'] . "/" . $post['post_alias'];
            }
            //        if($posts = dbSelectAll('SELECT * FROM '.DB_PREFIX.'posts WHERE status="1"')){
            foreach ($posts as $post) {
                //                foreach ($this->Lang->list as $l) {
                $xml .=
                    "<url>
                            <loc>" . makeLangUrl($post['url'], "sk") . "</loc>
                            <lastmod>" . date("c", ($post['time_update'] > 0 ? $post['time_update'] : $post['time_add'])) . "</lastmod>
                            <changefreq>daily</changefreq>
                            <priority>0,5</priority>
                        </url>" . PHP_EOL;
                //                }
            }
        }


        $xml .= '</urlset>';

        echo $xml;
        //        file_put_contents(homeRoot.'/sitemap.xml', $xml);
    }
}
