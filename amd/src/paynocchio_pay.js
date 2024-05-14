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

import {makePayment} from "./repository";

/**
 * Paynocchio repository module to encapsulate all of the AJAX requests that can be sent for bank.
 *
 * @module     paygw_paynocchio/repository
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (component, paymentArea, itemId, orderId, fullAmount) => {
    const paynocchio_pay_button = document.getElementById('paynocchio_pay_button');

    if (paynocchio_pay_button) {

        const range = document.getElementById('bonuses-range');
        const input = document.getElementById('bonuses-value');

        input.addEventListener('change', () => {
            range.value = input.value;
        });
        range.addEventListener('change', () => {
            input.value = range.value;
        });
        range.addEventListener('input', () => {
            input.value = range.value;
        });

        paynocchio_pay_button.addEventListener('click', () => {
            paynocchio_pay_button.classList.add('disabled');
            const bonuses = document.getElementById('bonuses-value').value;
            makePayment(component, paymentArea, itemId, orderId, fullAmount, bonuses)
                .then(data => window.console.log(data));
        });
    }
};