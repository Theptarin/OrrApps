<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title><?php echo $orr_['title']; ?></title>
<?php foreach ($view_['css_files'] as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach ($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach ($view_['js_files'] as $file): ?>
        <script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
<?php foreach ($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
    </head>
    <body>
        <!-- Beginning header -->
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="<?php echo(site_url()) ?>"><?php echo $orr_['title']; ?></a>
                </div>
                <ul class="nav navbar-nav">
                    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">...<span class="caret"></span></a>
                        <ul class="dropdown-menu">
<?php foreach ($menu_['my_sys'] as $key=>$value): ?>
                            <li><a href="<?php echo site_url($key) ; ?>"><?php echo $value ; ?></a></li>
<?php endforeach; ?>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="<?php echo $menu_['projects_url']; ?>"><span class="<?php echo $menu_['mark_user_icon']; ?>"></span> <?php echo $menu_['mark_user']; ?> </a></li>
                    <li><a href="<?php echo $menu_['mark_url']; ?>"><span class="<?php echo $menu_['mark_function_icon']; ?>"></span> <?php echo $menu_['mark_function']; ?> </a></li>
                </ul>
            </div>
        </nav>
        <div>
        </div> <div style='height:20px;'></div> 
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
