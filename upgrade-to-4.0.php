#!/usr/bin/php
<?php
require __DIR__.'/util/globrecursive.php';
require __DIR__.'/util/deleteCacheFolder.php';

if (is_file('vkwf_branch') || is_file('kwf_branch')) {
    die("This script will update from 3.11, update to 3.11 first.\n");
}
if (!is_file('composer.json')) {
    die("composer.json not found.\n");
}

$changed = false;
$c = json_decode(file_get_contents('composer.json'));
foreach ($c->require as $packageName=>$packageVersion) {
    if (substr($packageVersion, 0, 5) == "3.11.") {
        $c->require->$packageName = '4.0.x-dev';
        $changed = true;
    }
    if ($packageName == 'koala-framework/kwf-owlcarousel' && (int)substr($packageVersion, 0, 1) < 2) {
        $c->require->$packageName = '2.0.x-dev';
    }
}
if (!$changed) {
    die("This script will update from 3.11, update to 3.11 first.\n");
}

if (!isset($c->extra)) {
    $c->extra = (object)array();
}
if (!isset($c->extra->{'require-bower'})) {
    $c->extra->{'require-bower'} = (object)array();
}
$c->extra->{'require-bower'}->susy = "vivid-planet/susy#18ad7cba4101e85bf4ed0ecfa45e6c2d59489a76";
$c->extra->{'require-bower'}->jquery = "1.11.3";
echo "Added susyone and jquery to require-bower\n";
file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

$files = array_merge(
    glob_recursive('*.php'),
    glob_recursive('*.tpl'),
    glob_recursive('*.twig'),
    glob_recursive('*.css'),
    glob_recursive('*.scss'),
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('webStandard', 'kwfUp-webStandard', $c);
    $c = str_replace('webForm', 'kwfUp-webForm', $c);
    $c = str_replace('webListNone', 'kwfUp-webListNone', $c);
    $c = str_replace('webMenu', 'kwfUp-webMenu', $c);
    $c = str_replace('kwcFormError', 'kwfUp-kwcFormError', $c);
    $c = str_replace('printHidden', 'kwfUp-printHidden', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.css'),
    glob_recursive('*.scss'),
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('.frontend', '.kwfUp-frontend', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.tpl'),
    glob_recursive('*.twig')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('class="clear"', 'class="kwfUp-clear"', $c);
    $c = str_replace('class="left"', 'class="kwfUp-left"', $c);
    $c = str_replace('class="right"', 'class="kwfUp-right"', $c);
    if ($c != $origC) {
        echo "added kwfUp- class prefix in $file\n";
        file_put_contents($file, $c);
    }
}


$files = array_merge(
    glob_recursive('Component.js'),
    glob_recursive('Component.defer.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    if (strpos($c, 'Kwf.Utils.ResponsiveEl') !== false) {
        $c = "var responsiveEl = require('kwf/responsive-el');\n".$c;
        $c = str_replace('Kwf.Utils.ResponsiveEl', 'responsiveEl', $c);
    }
    if (strpos($c, 'Kwf.onJElementReady') !== false || strpos($c, 'Kwf.onJElementShow') !== false || strpos($c, 'Kwf.onJElementHide') !== false || strpos($c, 'Kwf.onJElementWidthChange') !== false) {
        $c = "var $ = require('jQuery');\n".$c;
        $c = "var onReady = require('kwf/on-ready');\n".$c;
        $c = str_replace('Kwf.onJElementReady', 'onReady.onRender', $c);
        $c = str_replace('Kwf.onJElementShow', 'onReady.onShow', $c);
        $c = str_replace('Kwf.onJElementHide', 'onReady.onHide', $c);
        $c = str_replace('Kwf.onJElementWidthChange', 'onReady.onResize', $c);
    }
    if (strpos($c, 'Kwf.onElementReady') !== false || strpos($c, 'Kwf.onElementShow') !== false || strpos($c, 'Kwf.onElementHide') !== false || strpos($c, 'Kwf.onElementWidthChange') !== false) {
        $c = "var onReady = require('kwf/on-ready-ext2');\n".$c;
        $c = str_replace('Kwf.onElementReady', 'onReady.onRender', $c);
        $c = str_replace('Kwf.onElementShow', 'onReady.onShow', $c);
        $c = str_replace('Kwf.onElementHide', 'onReady.onHide', $c);
        $c = str_replace('Kwf.onElementWidthChange', 'onReady.onResize', $c);
    }
    if (strpos($c, 'Kwf.onContentReady') !== false || strpos($c, 'Kwf.callOnContentReady') !== false) {
        $c = "var onReady = require('kwf/on-ready');\n".$c;
        $c = str_replace('Kwf.onContentReady', 'onReady.onContentReady', $c);
        $c = str_replace('Kwf.callOnContentReady', 'onReady.callOnContentReady', $c);
    }
    if (strpos($c, 'Kwf.onComponentEvent') !== false || strpos($c, 'Kwf.fireComponentEvent') !== false) {
        $c = "var componentEvent = require('kwf/component-event');\n".$c;
        $c = str_replace('Kwf.onComponentEvent', 'componentEvent.on', $c);
        $c = str_replace('Kwf.fireComponentEvent', 'componentEvent.trigger', $c);
    }
    if (strpos($c, 'Kwf.getKwcRenderUrl') !== false) {
        $c = "var getKwcRenderUrl = require('kwf/get-kwc-render-url');\n".$c;
        $c = str_replace('Kwf.getKwcRenderUrl', 'getKwcRenderUrl', $c);
    }
    if (strpos($c, 'Kwc.Form.findForm(') !== false) {
        $c = "var findForm = require('kwf/frontend-form/find-form');\n".$c;
        $c = str_replace('Kwc.Form.findForm(', 'findForm(', $c);
    }
    if (strpos($c, 'Kwf.log(') !== false) {
        $c = "var kwfLog = require('kwf/log');\n".$c;
        $c = str_replace('Kwf.log(', 'kwfLog(', $c);
    }
    if (strpos($c, 'Ext2.namespace(') !== false || strpos($c, 'Ext2.ns(') !== false) {
        $c = "var kwfNs = require('kwf/namespace');\n".$c;
        $c = str_replace('Ext2.namespace(', 'kwfNs(', $c);
        $c = str_replace('Ext2.ns(', 'kwfNs(', $c);
    }
    if (strpos($c, 'Ext2.extend(') !== false) {
        $c = "var kwfExtend = require('kwf/extend');\n".$c;
        $c = str_replace('Ext2.extend(', 'kwfExtend(', $c);
    }
    if (strpos($c, 'Kwf.GoogleMap.Map') !== false) {
        $c = "var GoogleMap = require('kwf/google-map/map');\n".$c;
        $c = str_replace('Kwf.GoogleMap.Map', 'GoogleMap', $c);
    }

    if ($c != $origC) {
        echo "Adapted to commonjs require: $file\n";
        file_put_contents($file, $c);
    }
}



if (!is_dir('cache/commonjs')) {
    mkdir('cache/commonjs');
    file_put_contents('cache/commonjs/.gitignore', "*\n!.gitignore\n");
    system("git add cache/commonjs/.gitignore");
    echo "folder \"cache/commonjs\" created\n";
}
if (!is_dir('cache/componentassets')) {
    mkdir('cache/componentassets');
    file_put_contents('cache/componentassets/.gitignore', "*\n!.gitignore\n");
    system("git add cache/componentassets/.gitignore");
    echo "folder \"cache/componentassets\" created\n";
}


$files = array_merge(
    glob_recursive('*.css')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    if (strpos($c, 'var(') !== false) {
        $scssFile = substr($file, 0, -4).'.scss';
        if (file_exists($scssFile)) {
            file_put_contents($scssFile, "\n".$c, FILE_APPEND);
            unlink($file);
        } else {
            rename($file, $scssFile);
        }
        echo "Renamed to scss to support assetVariables: $file\n";
    }
}


$files = array_merge(
    glob_recursive('Web.css'),
    glob_recursive('Master.css')
);
foreach ($files as $file) {
    $scssFile = substr($file, 0, -4).'.scss';
    if (!file_exists($scssFile)) {
        rename($file, $scssFile);
    }
    echo "Renamed to scss: $file\n";
}

$files = array_merge(
    glob_recursive('*.scss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    if (strpos($c, 'var(') !== false) {
        $c = str_replace('.$cssClass', '.cssClass', $c);
        $c = "@import \"config/colors\";\n".$c;
        $c = preg_replace('#var\(([^\)]+)\)#', '$\1', $c);
        echo "Converted var() to scss: $file\n";
        file_put_contents($file, $c);
    }
}


$assetVariables = array(
    'mainColor' => '#314659',
    'secColor' => '#1E3040',
    'highlightedText' => '#c90000',
    'contentBg' => '#f4f4f4',
    'typo' => '#414742',
    'dark' => '#000',
    'light' => '#fff',
    'lightGrey' => '#707070',
    'errorBg' => '#d11313',
    'errorBorder' => '#bb1d1d',
    'errorText' => '#fff',
    'successBg' => '#7db800',
    'successBorder' => '#1e7638',
    'successText' => '#fff',
);
if (file_exists('assetVariables.ini')) {
    $ini = parse_ini_file('assetVariables.ini');
    foreach ($ini as $k=>$i) {
        $assetVariables[$k] = $i;
    }
    unlink('assetVariables.ini');
}
if (file_exists('config.ini')) {
    $ini = parse_ini_file('config.ini');
    foreach ($ini as $k=>$i) {
        if (substr($k, 0, 15) == 'assetVariables.') {
            $assetVariables[substr($k, 15)] = $i;
        }
    }
    $c = file_get_contents('config.ini');
    $c = preg_replace('#assetVariables\..*\n#', '', $c);
    file_put_contents('config.ini', $c);
}
if (file_exists('themes/Theme/config.ini')) {
    $ini = parse_ini_file('themes/Theme/config.ini');
    foreach ($ini as $k=>$i) {
        if (substr($k, 0, 15) == 'assetVariables.') {
            $assetVariables[substr($k, 15)] = $i;
        }
    }
    $c = file_get_contents('themes/Theme/config.ini');
    $c = preg_replace("#assetVariables\..*\n#", '', $c);
    file_put_contents('themes/Theme/config.ini', $c);
}
$c = '';
foreach ($assetVariables as $k=>$i) {
    $c .= "\$$k: $i;\n";
}
file_put_contents('scss/config/_colors.scss', $c);
echo "generated scss/config/_colors.scss\n";




$files = array_merge(
    glob_recursive('*.css')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('.$cssClass', '.cssClass', $c);
    if ($c != $origC) {
        echo "Converted .\$cssClass to .cssClass: $file\n";
        file_put_contents($file, $c);
    }
}



$files = array_merge(
    glob_recursive('*.css'),
    glob_recursive('*.scss'),
    glob_recursive('*.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('.cssClass', '.kwcClass', $c);
    if ($c != $origC) {
        echo "Converted .cssClass to .kwcClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.tpl')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('<?=$this->cssClass?>', '<?=$this->rootElementClass?>', $c);
    $c = str_replace('<?=$this->cssClass;?>', '<?=$this->rootElementClass?>', $c);
    if ($c != $origC) {
        echo "Converted cssClass to rootElementClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('*.twig')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('{{ cssClass }}', '{{ rootElementClass }}', $c);
    if ($c != $origC) {
        echo "Converted cssClass to rootElementClass: $file\n";
        file_put_contents($file, $c);
    }
}

$files = array_merge(
    glob_recursive('Component.php')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('[\'cssClass\']', '[\'rootElementClass\']', $c);
    if ($c != $origC) {
        echo "Converted ['cssClass'] to ['rootElementClass']: $file\n";
        file_put_contents($file, $c);
    }
}




$files = array_merge(
    glob_recursive('Component.printcss'),
    glob_recursive('Master.printcss'),
    glob_recursive('Web.printcss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    unlink($file);
    $c = "\n@media print {\n$c\n}\n";
    if (file_exists(substr($file, 0, -8).'scss')) {
        $filename = substr($file, 0, -8).'scss';
    } else if (file_exists(substr($file, 0, -8).'css')) {
        $filename = substr($file, 0, -8).'css';
    } else {
        $filename = substr($file, 0, -8).'scss';
    }
    file_put_contents($filename, $c, FILE_APPEND);
    echo "Converted to media query: $file\n";
}


$files = array_merge(
    glob_recursive('Component.php')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('ModernizrTouch', 'ModernizrTouchevents', $c);
    if ($c != $origC) {
        echo " [modernizr v3] touch modernizr test to touchevents: $file\n";
        file_put_contents($file, $c);
    }
}
$files = array_merge(
    glob_recursive('Component.css'),
    glob_recursive('Component.scss'),
    glob_recursive('Master.scss'),
    glob_recursive('Web.scss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('html.touch', 'html.touchevents', $c);
    $c = str_replace('html.no-touch', 'html.no-touchevents', $c);
    if ($c != $origC) {
        echo " [modernizr v3] touch modernizr test to touchevents: $file\n";
        file_put_contents($file, $c);
    }
}
$files = array_merge(
    glob_recursive('Component.js'),
    glob_recursive('Master.js'),
    glob_recursive('Web.js')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace("'touch'", "'touchevents'", $c);
    $c = str_replace("'no-touch'", "'no-touchevents'", $c);
    $c = str_replace("Modernizr.touch", "Modernizr.touchevents", $c);
    if ($c != $origC) {
        echo " [modernizr v3] touch modernizr test to touchevents: $file\n";
        file_put_contents($file, $c);
    }
}

$files = glob_recursive('Component.php');
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;
    $c = str_replace('Kwc_Legacy_List_Fade_', 'LegacyListFade_Kwc_List_', $c);
    $c = str_replace('Kwc_Composite_Fade_', 'LegacyListFade_Kwc_List_CompositeFade_', $c);
    if ($c != $origC) {
        echo "renamed to Kwc_LegacyListFade: $file\n";
        file_put_contents($file, $c);

        $c = json_decode(file_get_contents('composer.json'));
        $c->require->{'koala-framework/kwc-legacy-list-fade'} = "1.0.x-dev";
        echo "Added koala-framework/kwc-legacy-list-fade to require composer.json\n";
        file_put_contents('composer.json', json_encode($c, (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0) + (defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) ));

    }
}

// Rename components from packages to new package-naming-convention
$files = glob_recursive('*.php');
$files = array_merge($files, glob_recursive('config.ini'));
foreach ($files as $file) {
    $c = file_get_contents($file);
    $origC = $c;

    // Owlcarousel: https://github.com/koala-framework/kwf-owlcarousel
    $c = str_replace('Kwf_Owlcarousel_Kwc_Carousel_', 'Owlcarousel_Kwc_Carousel_', $c);
    $c = str_replace('Kwf_Owlcarousel_Kwc_Thumbnails_', 'Owlcarousel_Kwc_Thumbnails_', $c);

    // Parallax: https://github.com/koala-framework/kwc-parallax
    $c = str_replace('Kwc_Parallax_ParallaxImage_', 'Parallax_Kwc_ParallaxImage_', $c);

    // TextImageTeaser: https://github.com/koala-framework/kwc-text-image-teaser
    $c = str_replace('Kwc_TextImageTeaser_', 'TextImageTeaser_Kwc_Teaser_', $c);
    if ($c != $origC) {
        echo "renamed $origC to $c: $file\n";
        file_put_contents($file, $c);
    }
}


$files = array_merge(
    glob_recursive('Web.scss')
);
foreach ($files as $file) {
    $c = file_get_contents($file);
    $c .= "\n.kwfUp-webForm {\n".
        "    @include form-default-styles;\n".
        "}\n";
    $c = "@import \"form/default-styles\";\n$c";
    file_put_contents($file, $c);
    echo "added form-default-styles to $file\n";
}


echo "\n";
echo "run now 'composer update' to update dependencies\n";

