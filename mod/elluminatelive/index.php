<?php // $Id: index.php,v 1.1.2.3 2009/10/22 14:28:23 jfilip Exp $


    require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
    require_once dirname(__FILE__) . '/lib.php';

    global $DB;

    $id = optional_param('id', 0, PARAM_INT);                   // Course id

    if ($id) {
        if (! $course = $DB->get_record('course', array('id'=>$id))) {
            error("Course ID is incorrect");
        }
    } else {
        if (! $course = get_site()) {
            error("Could not find a top-level course!");
        }
    }

    require_course_login($course);

    add_to_log($course->id, "elluminatelive", "view all", "index.php?id=$course->id", "");


/// Get all required strings

    $strelluminatelives = get_string("modulenameplural", "elluminatelive");
    $strelluminatelive  = get_string("modulename", "elluminatelive");


/// Print the header

    if ($course->category) {
        $navigation = "<a href=\"../../course/view.php?id=$course->id\">$course->shortname</a> ->";
    }

/// Print header.
    $navigation = build_navigation($strelluminatelives);
    print_header_simple($strelluminatelives, "", $navigation, "", "", true, '');

/// Get all the appropriate data

    if (! $elluminatelives = get_all_instances_in_course("elluminatelive", $course)) {
        notice("There are no Elluminate Live! meetings ", "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string("name");
    $strweek  = get_string("week");
    $strtopic  = get_string("topic");

    if ($course->format == "weeks") {
        $table->head  = array ($strweek, $strname);
        $table->align = array ("center", "left");
    } else if ($course->format == "topics") {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ("center", "left", "left", "left");
    } else {
        $table->head  = array ($strname);
        $table->align = array ("left", "left", "left");
    }

    foreach ($elluminatelives as $elluminatelive) {
        $elluminatelive->name = stripslashes($elluminatelive->name);

        if (!$elluminatelive->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id=$elluminatelive->coursemodule\">$elluminatelive->name</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id=$elluminatelive->coursemodule\">$elluminatelive->name</a>";
        }

        if ($course->format == "weeks" or $course->format == "topics") {
            $table->data[] = array ($elluminatelive->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo "<br />";

    print_table($table);

/// Finish the page

    print_footer($course);

?>
