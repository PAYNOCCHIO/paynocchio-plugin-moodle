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

import {makePayment, calculateReward} from "./repository";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';
import debounce from "./debounce";

/**
 * Paynocchio repository module to encapsulate all of the AJAX requests that can be sent for bank.
 *
 * @module     paygw_paynocchio/repository
 * @copyright  2024 Paynocchio <ceo@paynocchio.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
const debounceTime = 500;

const checkPayability = (bonuses, fullAmount, balance = '0', element, bonuses_conversion_rate) => {
    if(parseFloat(bonuses) * parseFloat(bonuses_conversion_rate) + parseFloat(balance) < parseFloat(fullAmount)) {
        element.classList.add('disabled');
        document.getElementById('topup_message').innerText = 'Please top up or use your Bonuses.';
        document.getElementById('topup_message').classList.remove('paynocchio-hidden');
    } else {
        element.classList.remove('disabled');
        document.getElementById('topup_message').innerText = '';
        document.getElementById('topup_message').classList.add('paynocchio-hidden');
        document.getElementById('paynocchio_gaining_bonuses').classList.remove('paynocchio-hidden');
    }
};

const changePayButtonValues = (fullAmount, bonuses, bonuses_conversion_rate) => {
    const current_amount = document.getElementById('current_amount');
    const old_amount = document.getElementById('old_amount');
    const discount = document.getElementById('discount');
    const money_equivalent = document.getElementById('money_equivalent');
    let old_amount_value = fullAmount;
    if (bonuses > 0) {
        if(bonuses_conversion_rate !== 1) {
            money_equivalent.innerHTML = `<span>$${bonuses * bonuses_conversion_rate}</span>`;
        }
        current_amount.innerText = (old_amount_value - bonuses * bonuses_conversion_rate).toFixed(2);
        old_amount.innerText = "$" + old_amount_value.toFixed(2);
        discount.innerText = '—' + ((bonuses * bonuses_conversion_rate * 100) / old_amount_value).toFixed(2) + '%';
        old_amount.classList.remove('paynocchio-hidden');
        discount.classList.remove('paynocchio-hidden');
    } else {
        if(money_equivalent) {
            money_equivalent.innerHTML = '';
        }
        current_amount.innerText = old_amount_value;
        old_amount.classList.add('paynocchio-hidden');
        discount.classList.add('paynocchio-hidden');
    }
};

const changeBonusesValue = (fullAmount, bonuses) => {
    const element = document.getElementById('bonuses_to_get');
    const input = fullAmount - bonuses;
    element.classList.add('loading');
    calculateReward(input, "payment_operation_for_services")
        .then(rules => {
            let bonuses_to_get_value = parseFloat(rules.bonuses_to_get);

            const paynocchio_gaining_bonuses = document.getElementById('paynocchio_gaining_bonuses');
            if (bonuses_to_get_value > 0) {
                paynocchio_gaining_bonuses.classList.remove('paynocchio-hidden');
                element.innerText = bonuses_to_get_value;
            } else {
                paynocchio_gaining_bonuses.classList.add('paynocchio-hidden');
            }

            element.classList.remove('loading');
        });
};

const debouncedChangeBonusesValue = debounce((fullAmount, bonuses) => changeBonusesValue(fullAmount, bonuses), debounceTime);

export const init = (component,
                     paymentArea,
                     description,
                     itemid,
                     fullAmount,
                     balance,
                     bonuses_conversion_rate) => {
    const paynocchio_pay_button = document.getElementById('paynocchio_pay_button');

    if (paynocchio_pay_button) {

        const range = document.getElementById('bonuses-range');
        const input = document.getElementById('bonuses-value');

        changePayButtonValues(fullAmount, range?.value ?? 0, bonuses_conversion_rate);

        let bonuses = 0;

        if(input) {
            bonuses = parseFloat(input.value);
        }

        if(range && input) {
            checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button, bonuses_conversion_rate);

            input.addEventListener('change', () => {
                range.value = bonuses;
            });
            range.addEventListener('change', () => {
                bonuses = range.value;
                input.value = range.value;
                checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button, bonuses_conversion_rate);
                changePayButtonValues(fullAmount, bonuses, bonuses_conversion_rate);
                debouncedChangeBonusesValue(fullAmount, bonuses * bonuses_conversion_rate);
            });
            range.addEventListener('input', () => {
                bonuses = range.value;
                input.value = range.value;
                checkPayability(bonuses, fullAmount, balance, paynocchio_pay_button, bonuses_conversion_rate);
                debouncedChangeBonusesValue(fullAmount, bonuses * bonuses_conversion_rate);
                changePayButtonValues(fullAmount, bonuses, bonuses_conversion_rate);
            });
        } else {
            checkPayability(0, fullAmount, balance, paynocchio_pay_button, bonuses_conversion_rate);
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
            makePayment(component, paymentArea, description, itemid, fullAmount, bonuses)
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
                        topup_message.innerText = 'There is an Error occurred. Please try again later.';
                        paynocchio_pay_button.classList.remove('disabled');
                    }
                });
        });
    }
};
