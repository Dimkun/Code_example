<?php if (!defined('CL_CORE')) {
    header('location: ' . (443 == $_SERVER['SERVER_PORT'] ? 'https://' : 'http://') . $_SERVER['HTTP_HOST']);
    exit;
} ?>

<!DOCTYPE HTML>
<html lang="<?= $this->Lang->type ?>">

<head><?= sanitize_output($this->getBlock('head', [], 1)); ?></head>

<body>
    <?= sanitize_output($this->getBlock('header', [], 1)); ?>
    <? //=sanitize_output($this->getBlock('breadcrumbs',[],1));
    ?>
    <? $this->getPage($content_view ? $content_view : $this->page); ?>
    <?= sanitize_output($this->getBlock('footer', [], 1)); ?>
    <?= sanitize_output($this->getBlock('scripts', [], 1)); ?>
</body>

</html>