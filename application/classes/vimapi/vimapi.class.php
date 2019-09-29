<?php
class VIMApi extends Controller
{
    public $limit;
    public $url;
    protected $urlExt;
    protected $tr;
    protected $answerCode = 200;
    protected $last_response;
    private $params;
    private $out;
    private $REMOTE_IP;
    private $token = '';
    private $access_token = [
        'JKHFSDF786DSFSF6OISDUFJ3' => [
            'name' => 'TEST',
            'status' => 1,
            'limit' => 50,
            'ip' => [
                '192.168.0.100',
            ],
            'method' => [
                'right_panel'
            ]
        ],
    ];
    public function __construct($Lang, $url_param)
    {
        $this->REMOTE_IP = GetRealIp();

        $this->model = new Model();
        $this->view = new View();

        $this->time = time();
        $this->time_str = strftime('%k:%M:%S', $this->time);
        $this->view->time_str = strftime('%d.%m.%Y %k:%M', $this->time);

        if (preg_match("/%3C|%3E|%27|%22|\(\)/", $_SERVER['REQUEST_URI'])) {
            $this->answerMsg = '400 Bad Request | Invalid Request';
            $this->answerCode = 400;
            return;
        }

        $this->url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if ($Lang && $url_param) {
            $this->Lang = $Lang;
            $this->url_param = $url_param;
        } else {
            $this->Lang = new Lang;
            $routes = explode('/', $this->url);
            if ($routes[1] != $this->Lang->getDefault() && in_array($routes[1], $this->Lang->list)) {
                $this->Lang->_init($routes[1]);

                $z = 1;
                foreach ($routes as $key => $value) {
                    if ($key == $z + 1 && !empty($value)) $this->url_param[$key - $z] = $value;
                    else $this->url_param[$key - $z] = $value;
                }
            } else {
                $this->Lang->_init();
                $this->url_param = $routes;
            }

            $this->view->Lang = $this->Lang;
            $this->view->url_param = $this->url_param;
        }

        $this->urlExp = $this->url_param + array(null, null, null, null, null, null);

        if (!$this->access()) return;

        if (empty($this->urlExp[2]) || !in_array($this->urlExp[2], array('json', 'xml', 'html', 'text'))) {
            $this->answerMsg = 'Type of response "' . $this->urlExp[2] . '" is not supported';
            $this->answerCode = 404;
            return;
        }

        $this->tr = $this->urlExp[2];

        if (empty($this->urlExp[3])) {
            $this->answerMsg = 'Request type not defined';
            $this->answerCode = 404;
            unset($this->tr);
            return;
        } elseif (!method_exists($this, $this->urlExp[3])) {
            $this->answerMsg = 'Method "' . $this->urlExp[3] . '" is not supported';
            $this->answerCode = 404;
            unset($this->tr);
            return;
        }

        $this->limit = !empty($_GET['limit']) && is_numeric($_GET['limit']) ? $_GET['limit'] : 10;

        $method = $this->urlExp[3];
        $this->$method($this->urlExp[4], $this->urlExp[5]);

        $this->out();
    }
    public function __destruct()
    {
        if (!$this->out) $this->out();
    }
    public function out()
    {
        $this->out = true;
        header('Content-Type: text/plain; charset=utf-8');

        if (!isset($this->answerType) || 'error' == $this->answerType) {
            file_put_contents(
                $this->log_file_auth,
                "ERROR: " . REQUEST_URI . " | IP: " . $this->REMOTE_IP . " | Time: " . $this->time_str . PHP_EOL . (isset($this->answer) && $this->answer ? "Response: " .  print_r($this->answer, TRUE) . PHP_EOL : '') . (isset($this->answerMsg) ? "ResponseMsg: " . $this->answerMsg . PHP_EOL : ''),
                FILE_APPEND
            );
        }

        if (isset($this->tr) && 'xml' == $this->tr) {
            header("Content-Type:text/xml;");
            $xml = '<?xml version="1.0" encoding="utf-8"?><dataset xmlns:xsi="//www.w3.org/2001/XMLSchema-instance" xmlns:xsd="//www.w3.org/2001/XMLSchema">';
            $xml .= "<type>" . $this->answerType . "</type>";
            if (isset($this->answerLength) && $this->answerLength > 0) $xml .= "<length>" . $this->answerLength . "</length>";
            if (isset($this->answerMsg) && $this->answerMsg != '') $xml .= "<msg>" . $this->answerMsg . "</msg>";

            if ($this->answerType == 'success') {
                $xml .= "<answer>";
                if (is_array($this->answer)) $xml .= arrayToXML($this->answer);
                else $xml .= $this->answer;
                $xml .= "</answer>";
            }

            $xml .= '</dataset>';
            $xml = iconv("UTF-8", "UTF-8//IGNORE", $xml);
            echo $xml;
        } elseif (isset($this->tr) && 'html' == $this->tr) {
            header('Content-Type: text/html; charset=utf-8');
            if (isset($this->answer) && !is_array($this->answer))
                echo $this->answer;
            elseif (isset($this->answer)) {
                echo '<p>Can\'t convert object to string: <p>';
                print_pre($this->answer);
            }
        } elseif (isset($this->tr) && 'json' == $this->tr) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'answer' => isset($this->answer) ? $this->answer : null,
                'type' => isset($this->answerType) ? $this->answerType : null,
                'msg' => isset($this->answerMsg) ? $this->answerMsg : null,
            ), JSON_UNESCAPED_UNICODE);
        } elseif (isset($this->tr) && 'text' == $this->tr) {
            echo '<pre>';
            echo 'answerType:';
            var_dump(isset($this->answerType) ? $this->answerType : null);
            echo '<br>';
            echo 'answerMsg:';
            var_dump(isset($this->answerMsg) ? $this->answerMsg : null);
            echo '<br>';
            echo 'answer:';
            print_r(isset($this->answer) ? $this->answer : null);
            echo '<br>';
            echo 'answerLength:';
            var_dump(isset($this->answerLength) ? $this->answerLength : null);
            echo '</pre>';
        } else {
            if (404 == $this->answerCode) {
                header('Status: 404 Not Found');
                header('HTTP/1.1 404 Not Found');
            } elseif (403 == $this->answerCode) {
                header('Status: 403 Forbidden');
                header('HTTP/1.1 403 Forbidden');
            } elseif (400 == $this->answerCode) {
                header('Status: 400 Bad Request');
                header('HTTP/1.1 400 Bad Request');
            }

            echo $this->answerMsg;
        }
    }
    private function access()
    {
        $access_key = '';
        if (
            isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
        ) {
            $this->folder_log = FILESROOT . '/log_api/web/' . date('Y') . "/" . date('m') . "/" . date('d');
            if (!file_exists($this->folder_log)) mkdir($this->folder_log, 0777, true);
            $this->log_file_auth = $this->folder_log . "/log_auth_" . date('d_m_Y') . ".txt";

            if (
                'user_auth_register' != $this->urlExp[3] &&
                'user_pass_recovery' != $this->urlExp[3]
            ) {
                file_put_contents(
                    $this->log_file_auth,
                    "WEB" .
                        " | IP: " . $this->REMOTE_IP .
                        " | Time: " . $this->time_str .
                        " | URI: " . REQUEST_URI .
                        " | POST: " . print_r($_POST, 1) . PHP_EOL,
                    FILE_APPEND
                );
            }

            return true;
        } elseif ('' != $access_key && isset($this->access_token[$access_key]) && $this->client = $this->access_token[$access_key]) {
            $this->client_token = $access_key;

            $this->folder_log = FILESROOT . "/log_api/" . $this->client['name'] . '/' . date('Y') . "/" . date('m') . "/" . date('d');
            if (!file_exists($this->folder_log)) mkdir($this->folder_log, 0777, true);

            $this->log_file_auth = $this->folder_log . "/log_auth_" . date('d_m_Y') . ".txt";

            file_put_contents(
                $this->log_file_auth,
                $this->client['name'] .
                    " | IP: " . $this->REMOTE_IP .
                    " | Time: " . $this->time_str .
                    " | URI: " . REQUEST_URI . PHP_EOL . ($_POST ? "POST: " . print_r($_POST, 1) . PHP_EOL : ''),
                FILE_APPEND
            );

            if (isset($this->client['ip']) && $this->client['ip'] && !in_array($this->REMOTE_IP, $this->client['ip'])) {
                $this->answerMsg = 'Access denied for this IP "' . $this->REMOTE_IP . '".';
                //                file_put_contents($this->log_file_auth, $this->client['name'] .' | Error IP.'.PHP_EOL, FILE_APPEND);
                $this->answerCode = 403;
                return false;
            } elseif (isset($this->client['method'], $this->urlExp[3]) && $this->client['method'] && !in_array($this->urlExp[3], $this->client['method'])) {
                $this->answerMsg = 'Forbidden Method "' . $this->urlExp[3] . '".';
                //                file_put_contents($this->log_file_auth, $this->client['name'] .' | Forbidden Method "'.$this->urlExp[3].'".'.PHP_EOL, FILE_APPEND);
                $this->answerCode = 403;
                return false;
            }

            return true;
        } else {
            $this->folder_log = FILESROOT . "/log_api/not_identity/" . date('Y') . "/" . date('m') . "/" . date('d');
            if (!file_exists($this->folder_log)) mkdir($this->folder_log, 0777, true);
            $this->log_file_auth = $this->folder_log . "/log_auth_" . date('d_m_Y') . ".txt";

            $this->answerMsg = 'Signature verification failed';
            $this->answerCode = 403;
            file_put_contents(
                $this->log_file_auth,
                "Error Verification" .
                    " | IP: " . $this->REMOTE_IP .
                    " | Time: " . $this->time_str .
                    " | URI: " . REQUEST_URI .
                    " | AGENT: " . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL . ($_POST ? " POST: " . print_r($_POST, 1) . PHP_EOL : '') . ($access_key ? $access_key . PHP_EOL : ''),
                FILE_APPEND
            );
            return false;
        }
    }
}
