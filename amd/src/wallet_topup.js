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

import {handleTopUpClick, showModalWithTopup, calculateReward} from "./repository";

let debounceTimer;
const debounceTime = 300;

const debounce = (callback, time) => {
    window.clearTimeout(debounceTimer);
    debounceTimer = window.setTimeout(callback, time);
};

export const init = (pay, minimum_topup_amount, card_balance_limit, balance) => {

    const paynocchio_wallet_topup_button = document.getElementById('paynocchio_topup_button');

    if (paynocchio_wallet_topup_button) {

        paynocchio_wallet_topup_button.addEventListener('click', () => {
            showModalWithTopup(minimum_topup_amount, card_balance_limit)
                .then(modal => {
                    modal.setTitle('Topup your Wallet');
                    const button = modal.body.find('#topup_button');
                    const input = modal.body.find('#top_up_amount');
                    const message = modal.body.find('#topup_message');
                    const commission_message = modal.body.find('#commission_message');

                    input.on('keyup change', (evt) => {
                        if (parseFloat(evt.target.value) + balance > card_balance_limit) {
                            message.text(`When replenishing the amount ${evt.target.value}, 
                            the balance limit will exceed the set value ${card_balance_limit}`);
                            button.addClass('disabled');
                        } else if (evt.target.value >= minimum_topup_amount) {
                            commission_message.addClass('loading');
                            debounce(() => {
                                window.console.log(evt.target.value);
                                calculateReward(evt.target.value, 'payment_operation_add_money')
                                    .then(rewards => {
                                        window.console.log(rewards);
                                        if(rewards.bonuses_to_get > 0) {
                                            message.text(`You will get ${rewards.bonuses_to_get} bonuses (which equals to 
                                        $${rewards.bonuses_in_dollar})`);
                                            commission_message.text(
                                                `You will receive $${rewards.sum_without_commission}. 
                                                Commission: $${rewards.commission}`
                                            );
                                        } else {
                                            message.text('');
                                            commission_message.text('');
                                        }
                                        commission_message.removeClass('loading');
                                    });
                            }, debounceTime);

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