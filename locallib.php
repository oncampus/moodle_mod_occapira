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
 * Internal library of functions for module occapira
 *
 * All the occapira specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage occapira
 * @copyright  2015 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function occapira_get_percentage($occapiraid, $userid) {
	global $DB;
	$result = array();
	$total = 0;
	$success = 0;
	$layers = $DB->get_records('occapira_grades', array('occapira' => $occapiraid, 'userid' => $userid));
	foreach ($layers as $layer) {
		$total = $layer->total;
		if ($layer->grade > 0) {
			$success++;
		}
	}
	$result['total'] = $total;
	$result['success'] = $success;
	//$result['percentage'] = $occapiraid;
	//return $result;
	if ($total > 0) {
		$result['percentage'] = 100 / $total * $success;
	}
	else {
		$result['percentage'] = 0;
	}
	//echo $result['total'].' '.$result['percentage'].'<br />';
	return $result;
}

function occapira_get_section_percentage($courseid, $sectionid) {
	global $USER, $DB;
	$mods = $DB->get_records_sql("SELECT cm.*, m.name as modname
                                   FROM {modules} m, {course_modules} cm
                                  WHERE cm.course = ? 
								    AND cm.section= ? 
									AND cm.completion !=0 
									AND cm.module = m.id 
									AND m.visible = 1", 
								array($courseid, $sectionid)); // no disabled mods
	//$mods = get_course_section_mods($courseid, $sectionid);
	$modules = array();
	foreach ($mods as $m) {
		if ($m->modname == 'occapira') {
			$modules[] = $m;
		}
	}
	$count = count($modules);
	$percentage = 0;
	foreach ($modules as $modu) {
		$result = occapira_get_percentage($modu->instance, $USER->id);
		$percentage += $result['percentage'] / $count;
	}
	return $percentage;
}
