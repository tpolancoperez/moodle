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
 * @package   theme_aardvard_postit
 * @copyright 2012 Mary Evans
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>

  <div id="profilename">
     <ul>
     <li><a href="<?php echo $CFG->wwwroot.'/user/view.php?id='.$USER->id.'&amp;course='.$COURSE->id;?>"><?php echo $USER->firstname; ?><?php echo $USER->lastname; ?></a></li>
     <li><a id="toggle" href="javascript:toggle5('profilebar', 'toggle');" title="show &#9660;/hide &#9650;">&#9660;</a></li>
 </div>
 </div><!-- END #headerwrap -->

<div id="profilebar-outerwrap">
    <div id="profilebar" style="display: none;">
        <div id="profilebar-innerwrap">

        <div id="pbar-pre">
        <div class="region-content">
               <?php
            if ($haspostitnote1) {
                echo $PAGE->theme->settings->postitnote1;
            } else {
                echo get_string('postitnotetxt', 'theme_aardvark_postit');
            } ?>
        </div>
        </div>

        <div id="profilebar-center" class="profilebar-options">
            <div id="profilebar-account" class="profilebar-options">
                <ul>
                    <li><a href="<?php echo $CFG->wwwroot; ?>/my"><img src="<?php echo $OUTPUT->pix_url('profile/courses', 'theme')?>" /></a>
                    <i><?php echo get_string('mycourses');?></i></li>
                    <li><a href="<?php echo $CFG->wwwroot; ?>/user/profile.php"><img src="<?php echo $OUTPUT->pix_url('profile/profile', 'theme')?>" /></a>
                    <i><?php echo get_string('myprofile');?></i></li>
                    <li><a href="<?php echo $CFG->wwwroot; ?>/user/files.php"><img src="<?php echo $OUTPUT->pix_url('profile/myfiles', 'theme')?>" /></a>
                    <i><?php echo get_string('myfiles');?></i></li>
                </ul>
            </div>
            <div id="profilebar-mystuff" class="profilebar-options">
                <ul>
                    <li><a href="<?php echo $CFG->wwwroot; ?>/calendar/view.php?view=month">
                    <img src="<?php echo $OUTPUT->pix_url('profile/calendar', 'theme')?>" /></a>
                    <i><?php echo get_string('calendar','calendar');?></i></li>

                    <?php if ($hasemailurl) { ?>
                    <li><a href="<?php echo $PAGE->theme->settings->emailurl;?>">
                    <img src="<?php echo $OUTPUT->pix_url('profile/email', 'theme')?>" /></a>       <i><?php echo get_string('email','theme_aardvark_postit');?></i></li>
                    <?php } ?>

                    <li><a href="<?php echo $CFG->wwwroot; ?>/login/logout.php"><img src="<?php echo $OUTPUT->pix_url('profile/logout', 'theme')?>" /></a>
                     <i><?php echo get_string('logout');?></i></li>
                </ul>
            </div>
        </div>

        <div id="pbar-post">
        <div class="region-content">
               <?php
            if ($haspostitnote2) {
                echo $PAGE->theme->settings->postitnote2;
            } else {
                echo get_string('postitnotetxt', 'theme_aardvark_postit');
            } ?>
        </div>
        </div>

        </div><div class="clearfix"></div>
    </div>
</div>
<div class="profilebar-clear"></div>
