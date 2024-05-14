<?php
// This file is part of the bank paymnts module for Moodle - http://moodle.org/
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
 * Contains form to apply for Paynocchio
 *
 * File         file.php
 * Encoding     UTF-8
 *
 * @package paygw_paynocchio
 *
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace paygw_paynocchio;

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';


class pay_form extends \moodleform
{

    /**
     * form definition
     */
    public function definition()
    {
        $mform = $this->_form;
        $mform->setDisableShortforms(true);
        $mform->addElement('float', 'bonuses', 'How many bonuses spend');
        $mform->setType('bonuses', PARAM_FLOAT);

        $mform->addElement('hidden', 'confirm');
        $mform->setDefault('confirm', 1);
        $mform->setType('confirm', PARAM_INT);
        $mform->addElement('hidden', 'component');
        $mform->setType('component', PARAM_TEXT);

        $mform->addElement('hidden', 'paymentarea');
        $mform->setType('paymentarea', PARAM_TEXT);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);

        $mform->addElement('hidden', 'description');
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('submit', 'submitbutton', get_string('pay', 'paygw_paynocchio'));
    }
    public function validation($data, $files)
    {
        global $DB;
        $errors = parent::validation($data, $files);
        return $errors;
    }
}
