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
                    <a class="navbar-brand" href='<?php echo site_url('') ?>'><?php echo $orr_['title'] ?></a>
                </div>
                <ul class="nav navbar-nav">
                    <li ><?php echo anchor(site_url('Project'), 'ตั้งค่าระบบงาน', ['title' => 'การกำหนดค่าต่างๆ ที่เกี่ยวกับการเข้าใช้งาน และข้อมูล']) ?></li>
                    <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">...<span class="caret"></span></a>
                        <ul class="dropdown-menu">
                            <li><a href='<?php echo site_url('Project/my_sys') ?>'>โปรแกรม</a></li>
                            <li><a href='<?php echo site_url('Project/my_user') ?>'>ผู้ใช้งาน</a></li>
                            <li><a href='<?php echo site_url('Project/my_datafield') ?>'>คำจำกัดความข้อมูล</a></li>
                            <li><a href='<?php echo site_url('Project/my_activity') ?>'>กิจกรรรมของโครงการ</a></li>
                        </ul>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#"><span class="glyphicon glyphicon-user"></span> Sign Up</a></li>
                    <li><a href="#"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
                </ul>
            </div>
        </nav>
        <!-- End of header-->
        <div>
            <?php echo $output; ?>
        </div>
        <!-- Beginning footer -->
        <div><?php echo $view_['project'] . " " . $view_['project_title'] . " " . $view_['form_description']; ?></div>
        <!-- End of Footer -->
    </body>
</html>