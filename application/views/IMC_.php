<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />

        <?php foreach ($css_files as $file): ?>
            <link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />

        <?php endforeach; ?>
        <?php foreach ($js_files as $file): ?>

            <script src="<?php echo $file; ?>"></script>
        <?php endforeach; ?>

        <style type='text/css'>
            body
            {
                font-family: Arial;
                font-size: 14px;
            }
            a {
                color: blue;
                text-decoration: none;
                font-size: 14px;
            }
            a:hover
            {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <!-- Beginning header -->
        <div>
            <a href='<?php echo site_url('') ?>'>หน้าหลัก</a> |
            <a href='<?php echo site_url('IMC/opd_visit') ?>'>ข้อมูลรับบริการผู้ป่วยนอก</a> |
            <a href='<?php echo site_url('IMC/icd10_hn') ?>'>การให้รหัสวินิจฉัยโรคประจำตัวผู้ป่วย</a> |
            <a href='<?php echo site_url('IMC/icd10_opd') ?>'>การให้รหัสวินิจฉัยโรคของผู้ป่วยนอก</a> |
            <a href='<?php echo site_url('IMC/icd10_ipd') ?>'>การให้รหัสวินิจฉัยโรคของผู้ป่วยใน</a>  |
            <a href='<?php echo site_url('IMC/icd10_code') ?>'>การตั้งรหัสวินิจฉัยโรค</a> |
            <a href='<?php echo site_url('IMC/icd10_group') ?>'>การตั้งกลุ่มวินิจฉัยโรค</a>

        </div>
        <!-- End of header-->
        <div style='height:20px;'></div> 
        <div>
            <?php echo $output; ?>

        </div>
        <!-- Beginning footer -->
        <div><?php echo $footer; ?></div>
        <!-- End of Footer -->
    </body>
</html>