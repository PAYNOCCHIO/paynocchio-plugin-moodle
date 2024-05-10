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

import Ajax from 'core/ajax';

export const init = (user_id) => {
    const paynocchio_activation_button = document.getElementById('paynocchio_activation_button');

    const handleWalletActivationClick = async () => {
        const request = {
            methodname: 'paygw_paynocchio_activate_wallet',
            args: {
                userId: user_id,
            },
        };

        const response = await Ajax.call([request])[0];
        window.console.log(response);
    };

    paynocchio_activation_button.addEventListener('click', handleWalletActivationClick);
};
