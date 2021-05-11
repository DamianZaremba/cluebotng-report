<!DOCTYPE html>
<html>
<head>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8'>
    <link type='text/css' rel='stylesheet' href='index.css'>
    <link type='text/css' rel='stylesheet' href='diff.css'>
    <title>ClueBot NG Report Interface</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<div id="top">
    <div class="floatleft"><h1 id="title">ClueBot NG Report Interface</h1></div>
    <div class="floatright"><h1 id="subtitle">// <?PHP $page->writeHeader(); ?></h1></div>
</div>

<div class="hline"></div>

<div class="floatleft">
    <div id="left">
        <h2>Navigation</h2>
        <div id="menu">
            <?PHP $page->writeNavigation(); ?>
        </div>
    </div>
</div>
<div id="content">
    <?PHP $page->writeContent(); ?>
</div>
</body>
</html>
