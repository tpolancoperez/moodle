<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * zebra theme report layout
 *
 * @package    theme_zebra
 * @copyright  2011 Danny Wahl
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));
$showsidepre = ($hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT));
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$bodyclasses = array();
if (!$showsidepre) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}
if (!empty($PAGE->theme->settings->headeralt)) {
    $headeralt = $PAGE->theme->settings->headeralt;
} else {
    $headeralt = $PAGE->heading;
}
if (!empty($PAGE->theme->settings->branding)) {
    $branding = $PAGE->theme->settings->branding;
} else {
    $branding = 0;
}
if (!empty($PAGE->theme->settings->enablezoom)) {
    $enablezoom = $PAGE->theme->settings->branding;
    if ($enablezoom == 1) {
         $zoomenabled = 'yes';
    } else {
         $zoomenabled = 'no';
    }
} else {
    $zoomenabled = 'no';
}
echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $PAGE->title; ?></title>
    <!-- Mobile viewport optimization -->
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="480"/>
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <!--iOS web app -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <!-- Mobile IE: activate ClearType -->
    <meta http-equiv="cleartype" content="on">
    <!-- Default Favicons: png and ico -->
    <link rel="shortcut icon" type="image/png" href="<?php echo $OUTPUT->pix_url('favicon/favicon', 'theme'); ?>" />
    <link rel="icon" href="<?php echo $OUTPUT->pix_url('favicon/favicon', 'theme'); ?>" />
    <!-- For iPhone 4 with high-resolution Retina display: -->
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $OUTPUT->pix_url('favicon/h/apple-touch-icon-precomposed', 'theme'); ?>">
    <!-- For first-generation iPad: -->
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $OUTPUT->pix_url('favicon/m/apple-touch-icon-precomposed', 'theme'); ?>">
    <!-- For non-Retina iPhone, iPod Touch, and Android 2.1+ devices: -->
    <link rel="apple-touch-icon-precomposed" href="<?php echo $OUTPUT->pix_url('favicon/l/apple-touch-icon-precomposed', 'theme'); ?>">
    <!-- For nokia devices: -->
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon/l/apple-touch-icon-precomposed', 'theme'); ?>">
    <?php echo $OUTPUT->standard_head_html(); ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
    <?php echo $OUTPUT->standard_top_of_body_html(); ?>
    <div id="page">
        <div id="page-inner-wrapper">
            <div id="page-header-wrapper">
                <div id="page-header" class="clearfix">
                    <h1 class="headermain"><?php echo $headeralt; ?></h1>
                    <div id="profileblock">
                        <?php
                        if (isloggedin()) {
                            if ($haslogininfo) {
                                echo html_writer::tag('div', $OUTPUT->user_picture($USER, array('size'=>80)), array('id'=>'user-pic'));
                                echo $OUTPUT->login_info();
                            }
                            if (!empty($PAGE->layout_options['langmenu'])) {
                                echo $OUTPUT->lang_menu();
                            }
                            echo $PAGE->headingmenu;
                        } else {
                            echo $OUTPUT->login_info();
                        }
                        ?>  
                    </div>
                </div>
                <div id="page-border-wrapper">
                    <?php if ($hascustommenu) { ?>
                        <div id="custommenu-wrapper">
                            <div id="custommenu"><?php echo $custommenu; ?></div>
                        </div>
                    <?php } ?>
                    <?php if ($hasnavbar) { ?>
                        <div id="navbar-wrapper">
                            <div class="navbar clearfix">
                                <div class="breadcrumb"><?php echo $OUTPUT->navbar(); ?></div>
                                <div class="navbutton"> <?php echo $PAGE->button; ?></div>
                            </div>
                        </div>
                    <?php } ?>
                    <div id="page-content-wrapper">
                        <div id="page-content" class="clearfix">
                            <div id="report-main-content">
                                <div class="region-content">
                                    <?php echo core_renderer::MAIN_CONTENT_TOKEN ?>
                                </div>
                            </div>
                            <?php if ($hassidepre) { ?>
                                <div id="report-region-wrap">
                                    <div id="report-region-pre" class="block-region">
                                        <div class="region-content">
                                            <?php echo $OUTPUT->blocks_for_region('side-pre') ?>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($hasfooter) { ?>
                <div id="page-footer-wrapper">
                    <div id="page-footer">
                        <p class="helplink">
                            <?php echo page_doc_link(get_string('moodledocslink')); ?>
                        </p>
                        <?php
                        echo $OUTPUT->login_info();
                        echo "<br />";
                        echo $OUTPUT->standard_footer_html();
                        if ($branding == 0) {
                        echo '<div id="branding">';
                        echo '<a href="http://ldichina.com"><img src="'.$OUTPUT->pix_url('footer/LDi', 'theme').'" alt="LDi China"></a>';
                        echo '<a href="http://teachwithisc.com"><img src="'.$OUTPUT->pix_url('footer/iSC', 'theme').'" alt="International Schools of China"></a>';
                        echo '<a href="http://tiseagles.com"><img src="'.$OUTPUT->pix_url('footer/TIS', 'theme').'" alt="Tianjin International School"></a>';
                        echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php echo $OUTPUT->standard_end_of_body_html(); ?>
</body>
</html>