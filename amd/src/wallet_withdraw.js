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

import {calculateReward, handleWithdrawClick, showModalWithWithdraw} from "./repository";
import debounce from "./debounce";
let debouncedCalculateReward;

const debounceTime = 500;

const checkAvailability = (inputVal, balance) => {

    if(
        inputVal === '' ||
        parseFloat(inputVal) === 0 ||
        parseFloat(inputVal) > parseFloat(balance)
    ) {
        return false;
    }
    return true;
};

export const init = (balance, card_balance_limit) => {
    const paynocchio_wallet_withdraw_button = document.getElementById('paynocchio_withdraw_button');

    if (paynocchio_wallet_withdraw_button) {

        paynocchio_wallet_withdraw_button.addEventListener('click', () => {
            showModalWithWithdraw()
                .then(modal => {
                    modal.setTitle('Withdraw from the Wallet');
                    const input = modal.body.find('#withdraw_amount');
                    const button = modal.body.find('#withdraw_button');
                    const message = modal.body.find('#withdraw_message');

                    if(!checkAvailability(input.val(), balance)) {
                        button.addClass('disabled');
                        message.text('Operation is not permitted');
                    }

                    input.keyup(evt => {
                        if(!checkAvailability(evt.target.value, balance)) {
                            button.addClass('disabled');
                            message.text('Operation is not permitted');
                            clearTimeout(debouncedCalculateReward);
                        } else {
                            button.removeClass('disabled');
                            debouncedCalculateReward = debounce((inputValue) => {
                                message.addClass('loading');
                                message.addClass('loading');
                                calculateReward(inputValue, 'payment_operation_add_money')
                                    .then((rewards) => {
                                        if (inputValue + balance > card_balance_limit) {
                                            message.text(`When replenishing the amount ${inputValue},
                            the balance limit will exceed the set value ${card_balance_limit}.`);
                                            message.text('');
                                            button.addClass('disabled');
                                        } else if (inputValue <= balance) {
                                            message.addClass('loading');
                                            message.text(
                                                `You will receive $${rewards.sum_without_commission}. 
                    Commission: $${rewards.commission}.`
                                            );
                                            button.removeClass('disabled');
                                        } else {
                                            message.text('Please enter amount more than minimum replenishment amount.');
                                            message.text('');
                                            button.addClass('disabled');
                                        }
                                        message.removeClass('loading');
                                        message.removeClass('loading');
                                    });
                            }, debounceTime); // Adjust the wait time as needed

                            debouncedCalculateReward(evt.target.value);
                        }

                    });

                    button.click(() => {
                        if(!checkAvailability(input.val(), balance)) {
                            modal.body.find('#withdraw_message').text('Yeah... right... ;)');
                        } else {
                            button.addClass('disabled');
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            modal.body.find('#withdraw_message').text('Working...');
                            handleWithdrawClick(input.val())
                                .then(data => {
                                    if (data.success) {
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#withdraw_message').text('Success! Reloading...');
                                        window.location.reload();
                                    } else {
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#withdraw_message')
                                            .text('Something wrong. Please reload page and try again... ' + data.wallet_status);
                                        button.toggleClass('disabled');
                                    }
                                });
                        }
                    });
                });
        });
    }
};