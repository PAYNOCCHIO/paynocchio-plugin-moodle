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

import {handleTopUpClick, showModalWithTopup} from "./repository";

/**
 * Adapt rewarding rules to sum values of the same rules
 * @param {object} data
 * @return {*[]}
 */
const transformRewardingRules = (data) => {
    const result = [];

    data.forEach(item => {
        let existing = result.find(el =>
            el.operation_type === item.operation_type &&
            el.min_amount === item.min_amount &&
            el.max_amount === item.max_amount
        );

        if (existing) {
            existing.value += item.value;
        } else {
            result.push({ ...item });
        }
    });

    return result;
};

/**
 * Find eligible Operations
 * @param {object} obj
 * @param {number} num
 * @param {string} operationType
 * @return {*}
 */
const calculateFinalRule = (obj, num, operationType) => {
    let totalValue = 0;
    let minAmount = Infinity;
    let maxAmount = -Infinity;

    obj.forEach(item => {
        if (item.operation_type === operationType && num > item.min_amount && num < item.max_amount) {
            totalValue += item.value;
            if (item.min_amount < minAmount) {
                minAmount = item.min_amount;
            }
            if (item.max_amount > maxAmount) {
                maxAmount = item.max_amount;
            }
        }
    });

    // Проверка, если ни одно правило не подошло
    if (totalValue === 0) {
        return null;
    }

    return {
        totalValue,
        minAmount,
        maxAmount
    };
};

export const init = (pay, minimum_topup_amount, card_balance_limit, balance, rewarding_rules) => {
    const reducedRules = transformRewardingRules(rewarding_rules);

    window.console.log(reducedRules);

    // Демо работы фильтра
    // Replenishment
    window.console.log(calculateFinalRule(reducedRules, 6, "payment_operation_add_money"));
    window.console.log(calculateFinalRule(reducedRules, 90, "payment_operation_add_money"));
    window.console.log(calculateFinalRule(reducedRules, 103, "payment_operation_add_money"));
    // Payment
    window.console.log(calculateFinalRule(reducedRules, 90, "payment_operation_for_services"));
    window.console.log(calculateFinalRule(reducedRules, 500, "payment_operation_for_services"));

    const paynocchio_wallet_topup_button = document.getElementById('paynocchio_topup_button');

    let need_to_top_up = 0;
    if(pay && document.getElementById('need_to_top_up')) {
        need_to_top_up = parseFloat(document.getElementById('need_to_top_up').innerText);
    } else {
        need_to_top_up = minimum_topup_amount;
    }

    if (paynocchio_wallet_topup_button) {

        paynocchio_wallet_topup_button.addEventListener('click', () => {
            showModalWithTopup(minimum_topup_amount, card_balance_limit)
                .then(modal => {
                    modal.setTitle('Topup your Wallet');
                    const button = modal.body.find('#topup_button');
                    const input = modal.body.find('#top_up_amount');
                    const message = modal.body.find('#topup_message');
                    if(need_to_top_up) {
                        const top_up_default_input = need_to_top_up <= minimum_topup_amount ? minimum_topup_amount: need_to_top_up;
                        input.val(top_up_default_input);

                        if(parseInt(need_to_top_up * 0.1) > 0) {
                            message.text(`You will get ${parseInt(need_to_top_up * 0.1)} bonuses`);
                        }
                    }
                    input.on('keyup change', (evt) => {
                        if (parseFloat(evt.target.value) + balance > card_balance_limit) {
                            message.text(`When replenishing the amount ${evt.target.value}, 
                            the balance limit will exceed the set value ${card_balance_limit}`);
                            button.addClass('disabled');
                        } else if (evt.target.value >= minimum_topup_amount) {
                            message.text(`You will get ${parseInt(parseInt(evt.target.value) * 0.1)} bonuses`);
                            button.removeClass('disabled');
                        } else {
                            message.text('Please enter amount more than minimum replenishment amount.');
                            button.addClass('disabled');
                        }
                    });

                    button.click(() => {
                        if (input.val()) {
                            button.addClass('disabled');
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            modal.body.find('#topup_message').text('Working...');

                            let redirectLink = window.location.href;
                            let regex = new RegExp('[?&](success=)[^&]+');
                            redirectLink = redirectLink.replace( regex , '');
                            if (redirectLink.indexOf('?') != -1) {
                                redirectLink = redirectLink + '&success=1';
                            } else {
                                redirectLink = redirectLink + '?success=1';
                            }

                            handleTopUpClick(input.val(), redirectLink)
                                .then(data => {
                                    if (!data.is_error && data.url) {
                                        modal.body.find('#topup_message').text('OK... Sending to Stripe...');
                                        window.location.replace(data.url);
                                    } else {
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#topup_message')
                                            .text(data.message);
                                        button.toggleClass('disabled');
                                    }
                                });
                        }
                    });
                });
        });
    }
};