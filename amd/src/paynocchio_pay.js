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
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';

/**
 * Paynocchio repository module to encapsulate all of the AJAX requests that can be sent for bank.
 *
 * @module     paygw_paynocchio/repository
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const checkPayability = (value, fullAmount, balance = '0', element) => {
    if(parseFloat(value)+parseFloat(balance) < parseFloat(fullAmount)) {
        element.classList.add('disabled');
        document.getElementById('topup_message').innerText = 'Please top up or use your Bonuses.';
    } else {
        element.classList.remove('disabled');
        document.getElementById('topup_message').innerText = '';
    }
};

const changeBonusesValue = (balance, bonuses) => {
    const element = document.getElementById('bonuses_to_get');
    element.innerText = ((balance - bonuses) * 0.1).toFixed(2).slice(0, -1);
};

export const init = (component, paymentArea, itemid, fullAmount, balance) => {

    const paynocchio_pay_button = document.getElementById('paynocchio_pay_button');

    if (paynocchio_pay_button) {

        const range = document.getElementById('bonuses-range');
        const input = document.getElementById('bonuses-value');
        let bonuses = 0;
        if(input) {
            bonuses = parseFloat(input.value);
        }

        if(range && input) {
            checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button);

            input.addEventListener('change', () => {
                range.value = bonuses;
            });
            range.addEventListener('change', () => {
                bonuses = range.value;
                input.value = range.value;
                checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button);
                changeBonusesValue(fullAmount, bonuses);
            });
            range.addEventListener('input', () => {
                bonuses = range.value;
                input.value = range.value;
                checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button);
                changeBonusesValue(fullAmount, bonuses);
            });
        } else {
            checkPayability(0, fullAmount, balance, paynocchio_pay_button);
        }

        paynocchio_pay_button.addEventListener('click', () => {

            const spinner = document.querySelector('.paynocchio-spinner');
            const topup_message = document.getElementById('topup_message');

            if(bonuses + balance < fullAmount) {
                topup_message.innerText = 'Sorry, but no';
                return;
            }

            paynocchio_pay_button.classList.add('disabled');

            spinner.classList.add('active');
            makePayment(component, paymentArea, itemid, fullAmount, bonuses)
                .then(data => {
                    if(data.success) {
                        spinner.classList.remove('active');
                        topup_message.innerText = 'Success';
                        window.location.reload();
                        Templates.renderForPromise('paygw_paynocchio/enrolled_already', [])
                            .then(({html, js}) => {
                                Templates.replaceNodeContents('.paynocchio-profile-block', html, js);
                            })
                            .catch((error) => displayException(error));
                    } else {
                        window.console.log(data);
                        topup_message.innerText = 'There is an Error occurred. Please try again later.';
                        paynocchio_pay_button.classList.remove('disabled');
                    }
                });
        });
    }
};
