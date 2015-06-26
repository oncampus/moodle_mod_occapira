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
 * This is called by js if a capira question is answered correctly
 *
 * Setting and updating grades and completion states will be done here
 *
 * @package    mod
 * @subpackage occapira
 * @copyright  2015 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
header('Content-Type: text/html; charset=utf-8'); // sorgt für die korrekte Kodierung
header('Cache-Control: must-revalidate, pre-check=0, no-store, no-cache, max-age=0, post-check=0'); // ist mal wieder wichtig wegen IE
	
	require_once('../../config.php');
	require_login($course);
	global $DB, $USER, $CFG;
	require_once($CFG->libdir.'/completionlib.php');
	
	$occapiraid = optional_param('occapiraid', 0, PARAM_INT);
	$userid = optional_param('userid', 0, PARAM_INT);
	$score = optional_param('score', 0.00000, PARAM_FLOAT);
	$layer = optional_param('layer', 0, PARAM_INT);
	$total = optional_param('total', 0, PARAM_INT);
	
	if ($userid == $USER->id and $score > 0.00000 and $occapiraid > 0 and $layer > 0 and $total > 0) {
		if (!$DB->record_exists('occapira_grades', array('occapira' => $occapiraid, 'layer' => $layer, 'userid' => $userid))) {
			require_once($CFG->dirroot.'/mod/occapira/locallib.php');
			$data = new stdClass();
			$data->occapira = $occapiraid;
			$data->userid = $userid;
			$data->grade = $score;
			$data->timemodified = time();
			$data->layer = $layer;
			$data->total = $total;
			$DB->insert_record('occapira_grades', $data);
			// Update completion state
			$occapira = $DB->get_record('occapira', array('id' => $occapiraid));
			$course = $DB->get_record('course', array('id' => $occapira->course));
			$module = $DB->get_record('modules', array('name' => 'occapira'));
			$cm = $DB->get_record('course_modules', array('course' => $course->id, 'instance' => $occapira->id, 'module' => $module->id));
			
			$completion = new completion_info($course);
			if($completion->is_enabled($cm) && $occapira->completionlayers) {
				$completion->update_state($cm, COMPLETION_COMPLETE);
			}
			
			$section_percentage = round(occapira_get_section_percentage($occapira->course, $cm->section));
			echo $cm->section.','.$section_percentage;
		}
		else {
			if ($record = $DB->get_record('occapira_grades', array('occapira' => $occapiraid, 'layer' => $layer, 'userid' => $userid))) {
				$record->grade = $score;
				$record->timemodified = time();
				$DB->update_record('occapira_grades', $record);
			}
			echo 0;
		}
	}
	else {
		echo 0;
	}
	
?>