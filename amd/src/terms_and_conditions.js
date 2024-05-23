// This file is part of the Paynocchio payments module for Moodle - http://moodle.org/
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
 * Paynocchio repository module to encapsulate all of the AJAX requests that can be sent for bank.
 *
 * @module     paygw_paynocchio/repository
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getConf, showModalWithPrivacy, showModalWithTerms} from "./repository";

export const init = () => {
    const terms_button = document.getElementById('terms_trigger');
    const privacy_button = document.getElementById('privacy_trigger');

    if (terms_button) {
        terms_button.addEventListener('click', () => {
            getConf('terms')
                .then((data) => {
                    showModalWithTerms()
                        .then(modal => {
                            modal.setTitle('Terms and conditions');
                            modal.setBody(data.text);
                        });
                });

        });
    }
    if (privacy_button) {
        privacy_button.addEventListener('click', () => {
            getConf('privacy')
                .then((data) => {
                    showModalWithPrivacy()
                        .then(modal => {
                            modal.setTitle('Privacy Policy');
                            modal.setBody(data.text);
                        });
                });
        });
    }
};