<?php
define('DOMAIN_CURRENT', !empty($_SERVER['HTTP_HOST']) ? strtolower($_SERVER['HTTP_HOST']) : 'www.test.net');
$dExp = explode('.', DOMAIN_CURRENT);
if ($dExp[0] == 'www') {
    unset($dExp[0]);
    define('DOMAIN_W3', true);
} else define('DOMAIN_W3', false);

end($dExp);
$dMain = current($dExp);
prev($dExp);
$dMain = current($dExp) . '.' . $dMain;

if (prev($dExp)) {
    $dSub = current($dExp);
    $d = $dSub . '.' . $dMain;
} else {
    $dSub = false;
    $d = $dMain;
}
define('DOMAIN', $d);
define('DOMAIN_WWW', 'www.' . DOMAIN);
define('DOMAIN_COOKIE', '.' . DOMAIN);
define('SITE_URL', (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . DOMAIN_WWW);

define('DOMAIN_MAIN', $dMain);
define('DOMAIN_SUB', $dSub);
define('DOMAIN_PREFIX', $dMain{
0});
define('DOMAIN_FULL', (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'https://') . DOMAIN_CURRENT);
define('APPPATH', '/application');
define('homeRoot', $_SERVER['DOCUMENT_ROOT']);
define('cmsRoot', dirname(dirname(__FILE__)));
define('ADMROOT', cmsRoot . APPPATH);
define('LIBROOT', cmsRoot . '/lib');
define('CLSROOT', cmsRoot . '/classes');
define('COREROOT', cmsRoot . '/core');
define('FILESROOT', homeRoot . 'files');
/**
 * @const Folder for static files /files/static
 */
define('FILESSTATIC', '/files/static');
define('CONTROLERROOT', cmsRoot . '/controllers/');
define('MODELSROOT', cmsRoot . '/models/');
define('VIEWSSROOT', cmsRoot . '/views/');
define('ADMINSROOT', cmsRoot . '/views/admin/');
define('VALN_GET_PAGE', 'page');
define('DB_PREFIX', '');
define('CMS_VERS', '1.1.0002');
define("ENCRYPTION_KEY", "*");
define("ENCRYPTION_KEY_ADM", "");
define('REQUEST_URI', $_SERVER['REQUEST_URI']);
define('CL_CORE', true);

define("COMING_END", "2018/10/23 23:59:59");

date_default_timezone_set('Europe/Bratislava');
setlocale(LC_ALL, 'ru_RU.UTF-8');
setlocale(LC_NUMERIC, 'en_US.UTF-8');

require LIBROOT . '/mysqli.php';
require LIBROOT . '/function.php';
require LIBROOT . '/Mobile_Detect.php';
require LIBROOT . '/log.php';

/*  start log */
if (!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);
if (!defined('E_USER_DEPRECATED')) define('E_USER_DEPRECATED', 16384);

define('En_fatal', E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR);
define('En_die', E_ERROR | E_USER_ERROR | En_fatal);

set_error_handler('sendErrorHandler', E_ALL | E_STRICT);
register_shutdown_function('shutdownFunction');
//$_SERVER['tmp_buf'] = str_repeat('x', 1024 * 200);

if ($_SERVER['_ctrl']) $_SESSION['_ctrl'] = $_SERVER['_ctrl'];
elseif (isset($_SESSION, $_SESSION['_ctrl'])) unset($_SESSION['_ctrl']);
if ($_SERVER['_ctrl'] & 256) log::eventShow('db_query', 'query');
/* end log */

if (file_exists(CLSROOT . '/lang/lang.class.php')) require CLSROOT . '/lang/lang.class.php';
if (file_exists(CLSROOT . '/Phones.php')) include CLSROOT . '/Phones.php';

class Route
{
    static function start()
    {
        if (file_exists(LIBROOT . '/config.php')) require LIBROOT . '/config.php';
        if (isset($DB_CONNECT) && $DB_CONNECT) dbConnect($DB_CONNECT);

        $_red301 = array();

        if (isset($_red301[REQUEST_URI])) _redirect301($_red301[REQUEST_URI]);

        $get_url = explode('?', REQUEST_URI);
        $url = $get_url[0];
        if (
            !strpos($url, '.html')
            && !strpos($url, '.php')
            && !strpos($url, '.txt')
            && !strpos($url, '.xml')
            && !strpos($url, '.txt')
            && !strpos($url, '.jpg')
            && !strpos($url, '.png')
            && !strpos($url, '.js')
            && !strpos($url, '.css')
        ) {
            if (substr($url, -1) != '/') {
                $arr_get = array();
                unset($_GET['_ctrl']);
                unset($_GET['q']);
                foreach ($_GET as $key => $val) {
                    if ($val != '') {
                        $arr_get[$key] = $key . '=' . $val;
                    }
                }
                $re_url = $url . '/' . (count($arr_get) > 0 ? '?' . implode('&', $arr_get) : '');
                _redirect301($re_url);
            }
        }
        $routes = explode('/', $get_url[0]);
        if ('/robots.txt' == REQUEST_URI || '/sitemap.xml' == REQUEST_URI) { } elseif (!in_array(GetRealIp(), $config['debug_ip'])) {
            if (defined("COMING_END") && time() < strtotime(COMING_END)) self::coming_soon('sk', $routes[1]);
        } else { }

        $controller_name = 'Main';
        $action_name = 'index';


        $ctrl = '';
        if ('' != $routes[1]) {
            $category = dbSelect("SELECT id FROM category WHERE alias='$routes[1]'");
            if ($category['id']) {
                $controller_name = "category";
                $action_view_more = $action_name = $routes[1];
            } else {
                $controller_name = $ctrl = $routes[1];
                if (isset($routes[2]) && '' != $routes[2]) {
                    $action_view_more = $action_name = $routes[2];
                }
            }
        }

        $url_param = array();
        $Lang = new Lang;

        if ($ctrl != $Lang->getDefault() && in_array($ctrl, $Lang->list)) {
            $Lang->_init($controller_name);

            $z = 1;
            foreach ($routes as $key => $value)
                if ($key == $z + 1 && !empty($value)) {
                    $controller_name = $value;
                    $action_name = 'index';
                    $url_param[$key - $z] = $value;
                } else {
                    if ($key == $z + 2 && !empty($value)) $action_name = $value;
                    $url_param[$key - $z] = $value;
                }
            if ($controller_name == $ctrl) {
                $controller_name = 'main';
            }
        } else {
            $Lang->_init();
            $url_param = $routes;
        }

        if (preg_match("/%3C|%3E|%27|%22|\(\)/", REQUEST_URI)) {
            self::ErrorPage404($Lang, $url_param);
        }

        if ($controller_name == 'sitemap.xml' && $Lang->type === $Lang->getDefault()) {
            $controller_name = 'Controller_seo';
            $action_name = 'sitemap';
        } elseif ($controller_name == 'robots.txt' && $Lang->type === $Lang->getDefault()) {
            $controller_name = 'Controller_seo';
            $action_name = 'robots';
        } else $controller_name = 'Controller_' . $controller_name;

        $action_name = 'action_' . $action_name;

        $model_name = 'Model_' . $controller_name;
        $model_file = MODELSROOT . strtolower($model_name) . '.php';
        if (file_exists($model_file)) include MODELSROOT . $model_file;

        $controller_path = CONTROLERROOT . strtolower($controller_name) . '.php';

        if (file_exists($controller_path)) {
            include $controller_path;
        } elseif (
            strpos($controller_name, '-l') && preg_match_all('/Controller_([\W\w]*)-l(\d+)/si', $controller_name, $pos, PREG_SET_ORDER, 0)
        ) {
            preg_match_all('/Controller_([\W\w]*)-l(\d+)/si', $controller_name, $pos, PREG_SET_ORDER, 0);
            if (
                $pos[0][1] && $pos[0][2] && is_numeric($pos[0][2]) && is_string($pos[0][1]) &&
                $Location = dbSelect('SELECT l.*,ll.* FROM `location` as l LEFT JOIN `location__' . $Lang->type . '` as ll ON l.`id_page`=ll.`id_page` WHERE l.`id_page` = "' . $pos[0][2] . '" AND l.`external_url_page` = "' . forSQL($pos[0][1]) . '" AND l.`status_page`=1')
            ) {
                $controller_name = 'Controller_Location';
                if ($action_name != 'action_index') { }

                include CONTROLERROOT . strtolower($controller_name) . '.php';
            } else self::ErrorPage404($Lang, $url_param);
        } else self::ErrorPage404($Lang, $url_param);

        $controller = new $controller_name($Lang, $url_param);

        if (isset($Location)) $controller->Location = $Location;

        $action = $action_name;
        if ($controller_name == 'Controller_api') { } else {
            if (method_exists($controller, $action)) {
                $controller->$action();
            } elseif (method_exists($controller, 'action_view_more')) {
                $controller->action_view_more($action_view_more);
            } else {
                self::ErrorPage404($Lang, $url_param);
            }
        }
    }

    static function ErrorPage404($Lang, $url_param = [])
    {
        $controller_name = 'Controller_Error404';

        require CONTROLERROOT . strtolower($controller_name) . '.php';

        $controller = new $controller_name($Lang, $url_param);
        $controller->action_index();

        exit;
    }

    static function coming_soon($type = 'en', $action)
    {
        $controller_name = 'Controller_Coming_soon';
        if ($action == "addSubscribe") {
            $action_name = 'action_addSubscribe';
        } else {
            $action_name = 'action_index';
        }


        $url_param = array();
        $Lang = new Lang;
        $Lang->_init();
        require CONTROLERROOT . strtolower($controller_name) . '.php';

        $controller = new $controller_name($Lang, $url_param);
        $controller->$action_name();

        exit;
    }

    static function _redirect($location)
    {
        header('Location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $location);
        exit();
    }
    static function _redirect301($location)
    {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'https://') . $location);
        exit();
    }
}
