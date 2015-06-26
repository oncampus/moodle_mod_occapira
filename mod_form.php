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
 * The main occapira configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod
 * @subpackage occapira
 * @copyright  2015 oncampus
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form
 */
class mod_occapira_mod_form extends moodleform_mod {

    /**
     * Defines forms elements
     */
    public function definition() {

        $mform = $this->_form;

        //-------------------------------------------------------------------------------
        // Adding the "general" fieldset, where all the common settings are showed
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field
        $mform->addElement('text', 'name', get_string('occapiraname', 'occapira'), array('size'=>'64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'occapiraname', 'occapira');

        // Standardeinstellungen (Namen anzeigen, Breite, Höhe, CapiraID, Autoplay)
		$mform->addElement('advcheckbox', 'capirashowname', get_string('capirashowname', 'occapira'));
        $mform->setDefault('capirashowname', 0);
		
		$mform->addElement('text', 'capiraid', get_string('capiraid', 'occapira'), array('size'=>4));
        $mform->setType('capiraid', PARAM_INT);
        $mform->setDefault('capiraid', 0);
		$mform->addRule('capiraid', null, 'required', null, 'client');
		
		$mform->addElement('text', 'capirawidth', get_string('width', 'occapira'), array('size'=>4));
        $mform->setType('capirawidth', PARAM_INT);
        $mform->setDefault('capirawidth', 1280);
		$mform->addRule('capirawidth', null, 'required', null, 'client');
		
		$mform->addElement('text', 'capiraheight', get_string('height', 'occapira'), array('size'=>4));
        $mform->setType('capiraheight', PARAM_INT);
        $mform->setDefault('capiraheight', 720);
		$mform->addRule('capiraheight', null, 'required', null, 'client');
		
        $mform->addElement('advcheckbox', 'capiraautoplay', get_string('capiraautoplay', 'occapira'));
        $mform->setDefault('capiraautoplay', 0);

        //-------------------------------------------------------------------------------
        // add standard elements, common to all modules
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // add standard buttons, common to all modules
        $this->add_action_buttons();
    }
	
	function add_completion_rules() {
		$mform =& $this->_form;

		$group=array();
		$group[] =& $mform->createElement('checkbox', 'completionlayersenabled', ' ', get_string('min_correct_answers','occapira'));
		$group[] =& $mform->createElement('text', 'completionlayers', ' ', array('size'=>3));
		$mform->setType('completionlayers',PARAM_INT);
		$mform->addGroup($group, 'completionlayersgroup', get_string('correct_answers','occapira'), array(' '), false);
		//$mform->setHelpButton('completionlayersgroup', array('completion', get_string('completionlayershelp', 'occapira'), 'occapira'));
		$mform->disabledIf('completionlayers','completionlayersenabled','notchecked');

		return array('completionlayersgroup');
	}
	
	function completion_rule_enabled($data) {
		return (!empty($data['completionlayersenabled']) && $data['completionlayers']!=0);
	}
	
	function get_data() {
		$data = parent::get_data();
		if (!$data) {
			return $data;
		}
		if (!empty($data->completionunlocked)) {
			// Turn off completion settings if the checkboxes aren't ticked
			$autocompletion = !empty($data->completion) && $data->completion==COMPLETION_TRACKING_AUTOMATIC;
			if (empty($data->completionpostsenabled) || !$autocompletion) {
			   $data->completionposts = 0;
			}
		}
		return $data;
	}
	
	function data_preprocessing(&$default_values){
		// [Existing code, not shown]

		// Set up the completion checkboxes which aren't part of standard data.
		// We also make the default value (if you turn on the checkbox) for those
		// numbers to be 1, this will not apply unless checkbox is ticked.
		$default_values['completionlayersenabled']=
			!empty($default_values['completionlayers']) ? 1 : 0;
		if(empty($default_values['completionlayers'])) {
			$default_values['completionlayers']=1;
		}
	}
}
