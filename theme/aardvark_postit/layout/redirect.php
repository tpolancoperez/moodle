<?php
$hasheading = ($PAGE->heading);
$hasnavbar = (empty($PAGE->layout_options['nonavbar']) && $PAGE->has_navbar());
$hasfooter = (empty($PAGE->layout_options['nofooter']));
$hassidepre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-pre', $OUTPUT));
$hassidepost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('side-post', $OUTPUT));
$haspbarpre = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('pbar-pre', $OUTPUT));
$haspbarpost = (empty($PAGE->layout_options['noblocks']) && $PAGE->blocks->region_has_content('pbar-post', $OUTPUT));

$showsidepre = $hassidepre && !$PAGE->blocks->region_completely_docked('side-pre', $OUTPUT);
$showsidepost = $hassidepost && !$PAGE->blocks->region_completely_docked('side-post', $OUTPUT);
$showpbarpre = $haspbarpre && !$PAGE->blocks->region_completely_docked('pbar-pre', $OUTPUT);
$showpbarpost = $haspbarpost && !$PAGE->blocks->region_completely_docked('pbar-post', $OUTPUT);
$haslogininfo = (empty($PAGE->layout_options['nologininfo']));
$custommenu = $OUTPUT->custom_menu();
$hascustommenu = (empty($PAGE->layout_options['nocustommenu']) && !empty($custommenu));
$haslogo = (!empty($PAGE->theme->settings->logo));
$hasfootnote = (!empty($PAGE->theme->settings->footnote));
$hasemailurl = (!empty($PAGE->theme->settings->emailurl));

$bodyclasses = array();
if (($showpbarpre && !$showpbarpost) && ($showsidepre && !$showsidepost)){
    $bodyclasses[] = 'side-pre-only';
} else if (($showpbarpost && !$showpbarpre) && ($showsidepre && !$showsidepost)) {
    $bodyclasses[] = 'side-pre-only';
} else if (($showpbarpre && !$showpbarpost) && ($showsidepost && !$showsidepre)) {
    $bodyclasses[] = 'side-post-only';
} else if (($showpbarpost && !$showpbarpre) && ($showsidepost && !$showsidepre)) {
    $bodyclasses[] = 'side-post-only';
} else if (($showpbarpost && !$showpbarpre) && (!$showsidepre && !$showsidepost)) {
    $bodyclasses[] = 'content-only';
} else if (($showpbarpre && !$showpbarpost) && (!$showsidepre && !$showsidepost)) {
    $bodyclasses[] = 'content-only';
} else if ((!$showpbarpre && !$showpbarpost) && (!$showsidepre && !$showsidepost)) {
    $bodyclasses[] = 'content-only';
}
if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}


if ($hascustommenu) {
    $bodyclasses[] = 'has_custom_menu';
}

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes() ?>>
<head>
    <title><?php echo $PAGE->title ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->pix_url('favicon', 'theme')?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
</head>
<body id="<?php p($PAGE->bodyid) ?>" class="<?php p($PAGE->bodyclasses.' '.join(' ', $bodyclasses)) ?>">
<?php echo $OUTPUT->standard_top_of_body_html() ?>

<div id="page">
<div id="page-header">
    <div class="headermenu"><?php
        if (!empty($PAGE->layout_options['langmenu'])) {
            echo $OUTPUT->lang_menu();
        }
        echo $PAGE->headingmenu
    ?></div>
    <div id="headerwrap">
    <div id="logowrap">
        <?php
    if ($haslogo) {
        echo html_writer::link(new moodle_url('/'), "<img src='".$PAGE->theme->settings->logo."' alt='logo' id='logo' />");
    } else {
        echo '<img src="'.$OUTPUT->pix_url('logo', 'theme').'" id="logo" />';
    } ?>
    </div>

    </div><!-- END #headerwrap -->

    <div id="menuwrap">
        <div id="homeicon">
            <a href="<?php echo $CFG->wwwroot; ?>"><img src="<?php echo $OUTPUT->pix_url('menu/home_icon', 'theme')?>"></a>
        </div>

        <?php if ($hascustommenu) { ?>
        <div id="menuitemswrap">
        <div id="custommenu"><?php echo $custommenu; ?></div>
        </div>
        <?php } ?>
    </div>


</div>

    <!-- start OF moodle CONTENT -->
<div id="page-content">
    <div id="region-main-box">
        <div id="region-post-box">
            <div id="region-main-wrap">
                <div id="region-main">
                    <div class="region-content">
                    <?php echo $OUTPUT->main_content() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- END OF CONTENT -->
     <?php if ($hasfooter) { ?>
        <div id="page-footer">
            <div id="footer-wrapper">
                <div id="footer-inner">
                    <?php echo $OUTPUT->login_info();?>

                       <?php
                    if ($hasfootnote) {
                        echo $PAGE->theme->settings->footnote;
                    } else {
                        echo get_string('footnotetxt', 'theme_aardvark_postit');
                    } ?>

                </div>
            </div>
          <?php echo $OUTPUT->standard_footer_html();?>
        </div>
     <?php } ?>

<div class="clearfix"></div>
</div>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
</body>
</html>
