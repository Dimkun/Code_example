<?php
class View
{
    public $elementsCnt;
    public $elements;
    public $contentData;

    public $template = 'main';
    public $page = 'main';
    public $constant = array(
        'status_post' => array(
            0 =>  "off",
            1 =>  "on"
        ),
    );

    function generate($content_view = '', $template_view = null, $data = null, $setting = null)
    {
        if ($template_view == null) $template_view = $this->template;
        if (is_array($setting)) extract($setting);
        if (is_array($data)) extract($data);

        if (file_exists(VIEWSSROOT . 'template/' . $template_view . '_template.php')) {
            require_once VIEWSSROOT . 'template/' . $template_view . '_template.php';
        } else {
            header('Status: 404 Not Found');
            header('HTTP/1.1 404 Not Found');
            echo 'Template not found';
            return;
        }
    }

    function getWindow($window, $data = null, $print = false)
    {
        if (is_array($data)) extract($data);

        if (file_exists(VIEWSSROOT . $this->template . '/window/' . $window . '.php')) {
            ob_start();
            require VIEWSSROOT . $this->template . '/window/' . $window . '.php';
            $content = ob_get_clean();

            if ($print) return $content;
            else echo $content;
        } else {
            if ($print) return 'Template window not found';
            else echo 'Template window not found';
        }
    }
    function getBlock($block, $data = null, $print = false)
    {
        if (is_array($data)) extract($data);

        if (file_exists(VIEWSSROOT . $this->template . '/blocks/' . $block . '.php')) {
            ob_start();
            require_once VIEWSSROOT . $this->template . '/blocks/' . $block . '.php';
            $content = ob_get_clean();

            if ($print) return $content;
            else echo $content;
        } else {
            if ($print) return 'Template block not found';
            else echo 'Template block not found';
        }
    }
    function getPage($page = '', $data = null, $print = false)
    {
        if (is_array($data)) extract($data);

        if ($page != '' && file_exists(VIEWSSROOT . $this->template . '/pages/' . $page . '.php')) {
            ob_start();
            require VIEWSSROOT . $this->template . '/pages/' . $page . '.php';
            $content = ob_get_clean();

            if ($print) return $content;
            else echo $content;
        } else {
            if ($print) return 'Page content not found';
            else echo 'Page content not found';
        }
    }

    public function pageing($cnt = null, $limit = null, $num = null, $lnk = null)
    {
        if (!file_exists(VIEWSSROOT . '/pageing.php')) return -1;
        if (null === $cnt) $cnt = $this->elementsCnt;
        if (null === $limit) $limit = $this->contentData['p_limit'];
        $num = null !== $num ? --$num : $this->contentData['p_num'];
        if ($cnt <= $limit || $cnt <= $limit * $num) return;
        if (null === $lnk) $lnk = str_replace(array('cl_page_cl&', 'cl_page_cl'), array('?', ''), preg_replace('/([\?|\&]cl_page_cl=\d{1,})/ism', 'cl_page_cl', str_replace(VALN_GET_PAGE, 'cl_page_cl', REQUEST_URI)));
        $lnkPage = (false === strpos($lnk, '?') ? '?' : '&') . VALN_GET_PAGE . '=';
        $pageCnt = ceil($cnt / $limit);
        $prev = (bool) $num;
        $next = (bool) ((int) $num < (int) $pageCnt - 1);
        $prevLnk = $prev ? $lnk . (1 == $num ? '' : $lnkPage . $num) : '';
        $nextLnk = $next ? $lnk . $lnkPage . ($num + 2) : '';
        ++$num;

        $pageLinks = array(1 => (1 == $num ? true : $lnk));

        $limitNum = 6; // Лимит элементов
        $limitNum3 = round($limitNum / 3);

        // от начала
        if ($limitNum >= $num + $limitNum3 - 1) $s = $num - round($limitNum / 2);
        // от конца
        elseif ($pageCnt < $num + $limitNum - $limitNum3) $s = $pageCnt - $limitNum;
        // середина && от начала но чуть дальше
        else $s = $num - $limitNum3;

        if (1 > $s) $s = 1;
        $l = $s - 1 + $limitNum;
        if ($l >= $pageCnt - 2) $l = $pageCnt;
        if (4 > $s) $s = 1;
        if (1 == $s) $s = 2;

        if (2 < $s) $pageLinks[$s - 1] = $s - 1 == $num ? true : false;
        for (; $s <= $l; $s++) $pageLinks[$s] = $s == $num ? true : $lnk . $lnkPage . $s;
        if ($l < $pageCnt) {
            ++$l;
            if ($l < $pageCnt) {
                $pageLinks[$l] = false;
                $l = $pageCnt;
            }
            if ($l == $pageCnt) $pageLinks[$pageCnt] = $lnk . $lnkPage . $pageCnt;
        }
        require VIEWSSROOT . '/pageing.php';
    }
}
