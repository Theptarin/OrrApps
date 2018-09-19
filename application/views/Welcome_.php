<!DOCTYPE html>
<!--
Orr projects Home
-->
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title><?php echo $page_value['title']; ?></title>
        <?php foreach ($css_files as $file): ?>
            <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
        <?php endforeach; ?>
        <?php foreach ($js_files as $file): ?>
            <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>
    </head>
    <body>
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="<?php echo(site_url()) ?>"><?php echo $page_value['title']; ?></a>
                </div>
                <ul class="nav navbar-nav">
                    <li ><?php echo anchor(site_url('IMC'), 'สารสนเทศเวชสถิติ', ['title' => '']) ?></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li ><?php echo $page_value['mnu_setting'] ?></li>
                    <li ><?php echo $page_value['mnu_sign'] ?></li>
                </ul>
            </div>
        </nav> 
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown">Dropdown Example
                <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="#">HTML</a></li>
                <li><a href="#">CSS</a></li>
                <li><a href="#">JavaScript</a></li>
            </ul>
        </div>
        <?php
        // put your code here
        ?>
        <div>
        </div> <div style='height:20px;'></div>  
        <div>
            <?php echo $page_value['topic']; ?>
        </div>
        <div class="jumbotron text-center">
            <h1>หน้าหลักของ อ๋อโปรเจค</h1>
            <p>ปรับขนาดหน้าเพจเพื่อดูผลการจัดหน้าจอ!</p> 
        </div>

        <div class="container">
            <div class="row">
                <div class="col-sm-4">
                    <h3>Column 1</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
                    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
                </div>
                <div class="col-sm-4">
                    <h3>Column 2</h3>
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
                    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
                </div>
                <div class="col-sm-4">
                    <h3>Column 3</h3>        
                    <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit...</p>
                    <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris...</p>
                </div>
            </div>
        </div>

    </body>
</html>
