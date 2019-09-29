<?
if (file_exists(CLSROOT . '/vimapi/vimapi.class.php')) require_once CLSROOT . '/vimapi/vimapi.class.php';
class Controller_Api extends VIMApi
{
    protected function accept_terms()
    {
        SetCookie("aterms", 1, time() + 604800, '/', DOMAIN_WWW);
        $this->answerType = 'success';
    }
    protected function subscribe()
    {
        $this->POST($this->url, $_POST);
        if (isset($this->last_response->answer)) $this->answer = $this->last_response->answer;
        if (isset($this->last_response->length)) $this->answerLength = $this->last_response->length;
        if (isset($this->last_response->type)) $this->answerType = $this->last_response->type;
        if (isset($this->last_response->msg)) $this->answerMsg = $this->last_response->msg;
    }
    protected function VIMGet()
    {
        $this->POST($this->url, $_POST);
        if (isset($this->last_response->answer)) $this->answer = $this->last_response->answer;
        if (isset($this->last_response->length)) $this->answerLength = $this->last_response->length;
        if (isset($this->last_response->type)) $this->answerType = $this->last_response->type;
        if (isset($this->last_response->msg)) $this->answerMsg = $this->last_response->msg;
    }

    function update_post()
    {
        if (is_array($_POST)) extract(TrimArray($_POST));
        $data = array();
        if ($id > 0) {
            $data["id_category"] = $id_category;
            $data["theme"] = $theme;
            $data["content"] = $content;
            $data["img"] = $img;
            $data["alias"] = $alias;
            $data["status"] = $status;
            $data["time_update"] = $this->time;
            if (dbUpdate("posts", $data, "id='" . $id . "'")) {
                $this->answerType = 'success';
                $this->answerMsg = 'Post updated';
            } else {
                $this->answerType = 'error';
                $this->answerMsg = 'Wrong update';
            }
        } else {
            $this->answerType = 'error';
            $this->answerMsg = 'Wrong ID';
        }
    }

    function auth()
    {
        $data = array();
        if (isset($_POST['email']) && isset($_POST['password'])) {
            $data['email'] = trim($_POST['email']);
            $data['password'] = trim($_POST['password']);
            if ($res = dbSel('users', 'email="' . forSQL($data['email']) . '" && password="' . forSQL(md5($data['password'])) . '"')) {
                dbUpdate("users", ['last_login' => time()], "id='" . $res['id'] . "'");
                SetCookie("admin_users", $res['id'], time() + 43200, '/', DOMAIN_MAIN);
                $this->answerType = 'success';
                $this->answer = 'admin_users';
            } else {
                $this->answerType = 'error';
                $this->answerMsg = 'Not Found';
            }
        } else {
            $this->answerType = 'error';
            $this->answerMsg = 'bad_data';
        }
    }

    function auth_check()
    {
        if (isset($_COOKIE["auth_users"]) && !empty($_COOKIE["auth_users"])) {
            $data['who'] = 'users';
        } elseif (isset($_COOKIE["auth_user"]) && !empty($_COOKIE["auth_user"])) {
            $data['who'] = 'user';
        } else {
            $data['who'] = 'no_user';
        }
        die(json_encode($data));
    }

    function logout()
    {
        SetCookie("auth_users", '', time() - 43200, '/', DOMAIN_MAIN);
        SetCookie("auth_userpr", '', time() - 43200, '/', DOMAIN_MAIN);
        die(json_encode($data = array('out' => true)));
    }
}
