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
import debounce from "./debounce";

const debounceTime = 500;

export const init = (pay, minimum_topup_amount, card_balance_limit, balance, cost, topupamount) => {

    const paynocchio_wallet_topup_button = document.getElementById('paynocchio_topup_button');

    let url = window.location.href;
    let regex = new RegExp('[?&](success=)[^&]+');
    url = url.replace( regex , '');
    window.history.replaceState({}, document.title, window.location.pathname);
    window.history.pushState({}, document.title, url);

    if (paynocchio_wallet_topup_button) {

        paynocchio_wallet_topup_button.addEventListener('click', () => {
            showModalWithTopup(minimum_topup_amount, card_balance_limit, cost, topupamount)
                .then(modal => {
                    modal.setTitle('Topup your Wallet');
                    const button = modal.body.find('#topup_button');
                    const input = modal.body.find('#top_up_amount');
                    const message = modal.body.find('#topup_message');
                    const commission_message = modal.body.find('#commission_message');
                    const debouncedCalculateReward = debounce((inputValue) => {
                        message.addClass('loading');
                        commission_message.addClass('loading');
                            calculateReward(inputValue, 'payment_operation_add_money')
                                .then(rewards => {
                                    if (inputValue + balance > card_balance_limit) {
                                        message.text(`When replenishing the amount ${inputValue},
                            the balance limit will exceed the set value ${card_balance_limit}.`);
                                        commission_message.text('');
                                        button.addClass('disabled');
                                    } else if (inputValue >= minimum_topup_amount &&  inputValue < card_balance_limit) {
                                        message.addClass('loading');
                                        if (rewards.bonuses_to_get > 0) {
                                            message.text(`You will get ${rewards.bonuses_to_get} bonuses.`);
                                        } else {
                                            message.text('');
                                            message.addClass('loading');
                                        }
                                        commission_message.text(
                                            `You will receive $${rewards.sum_without_commission}. 
                    Commission: $${rewards.commission}.`
                                        );
                                        button.removeClass('disabled');
                                    } else {
                                        message.text('Please enter amount more than minimum replenishment amount.');
                                        commission_message.text('');
                                        button.addClass('disabled');
                                    }
                                    message.removeClass('loading');
                                    commission_message.removeClass('loading');
                                });

                    }, debounceTime); // Adjust the wait time as needed

                    debouncedCalculateReward(minimum_topup_amount);

                    input.on('keyup paste', (evt) => {
                        if(!isNaN(parseFloat(evt.target.value)) && parseFloat(evt.target.value) > 0) {
                            debouncedCalculateReward(parseFloat(evt.target.value));
                        } else {
                            message.text('Please enter amount.');
                            commission_message.text('');
                            button.addClass('disabled');
                        }
                    });

                    button.click(() => {
                        if (input.val()) {
                            button.addClass('disabled');
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            modal.body.find('#topup_message').text('Working...');
                            modal.body.find('#commission_message').text('');
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
                                        modal.body.find('#commission_message').text('');
                                        window.location.replace(data.url);
                                    } else {
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#commission_message').text('');
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