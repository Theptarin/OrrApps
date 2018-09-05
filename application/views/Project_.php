<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title><?php echo "TEST"; ?></title>
<?php foreach ($css_files as $file): ?>
        <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach ($js_files as $file): ?>
        <script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
    </head>
    <body>
        <!-- Beginning header -->
        <div>
            <a href='<?php echo site_url('') ?>'>หน้าหลัก</a> |
            <a href='<?php echo site_url('Project/my_sys') ?>'>โปรแกรม</a> | 
            <a href='<?php echo site_url('Project/my_user') ?>'>ผู้ใช้งาน</a> |
            <a href='<?php echo site_url('Project/my_datafield') ?>'>คำจำกัดความข้อมูล</a> |
            <a href='<?php echo site_url('Project/my_activity') ?>'>กิจกรรรมของโครงการ</a>
        </div>
        <!-- End of header-->
        <div style='height:20px;'></div> 
        <div>
            <?php echo $output; ?>
        </div>
        <!-- Beginning footer -->
        <div>Footer</div>
        <!-- End of Footer -->
    </body>
</html>