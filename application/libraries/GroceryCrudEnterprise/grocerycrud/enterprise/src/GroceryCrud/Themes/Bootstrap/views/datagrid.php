<?php

    $minify_or_not = $config['environment'] === 'production' ? '.min' : '';

    // CSS
    if ($load_bootstrap) {
        if ($skin === 'bootstrap-v4') {
            $this->setCssFile($this->_assetsFolder.'css/bootstrap/v4/bootstrap' . $minify_or_not . '.css');
        } else {
            $this->setCssFile($this->_assetsFolder.'css/bootstrap/bootstrap' . $minify_or_not . '.css');
        }
    }
    if ($load_jquery_ui) {
        $this->setCssFile($this->_assetsFolder.'css/jquery-ui/jquery-ui' . $minify_or_not . '.css');
    }

    if ($load_select2) {
        $this->setCssFile($this->_assetsFolder.'css/libraries/select2' . $minify_or_not . '.css');
    }

    $this->setCssFile($this->_assetsFolder.'css/grocery-crud-v2.6.2.2a58790.css');

    // JavaScript
    if ($load_jquery) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/jquery/jquery' . $minify_or_not .  '.js');
    }

    if ($load_jquery_ui) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/jquery-ui' . $minify_or_not . '.js');
    }

    if ($load_select2) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/select2' . $minify_or_not . '.js');
    }

    if ($load_react) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/react/react.v16.2.0' . $minify_or_not . '.js');
    }

    if ($load_texteditor) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/ckeditor/ckeditor.js');
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/ckeditor/ckeditor.adapter-jquery.js');
    }

    if ($load_modernizr) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/libraries/modernizr-custom.js');
    }


    if ($autoload_javascript) {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/build/grocery-crud-v2.6.2.2a58790' . $minify_or_not . '.js');
    } else {
        $this->setJavaScriptFile($this->_assetsFolder . 'js/build/grocery-crud-no-autoload-v2.6.2.2a58790' . $minify_or_not . '.js');
    }


?>
<div class="gc-container" data-url="<?php echo $this->getApiUrl(); ?>" data-unique-id="<?php echo $this->getUniqueId(); ?>">
    <?php include(__DIR__ . '/build/main.html'); ?>
</div>