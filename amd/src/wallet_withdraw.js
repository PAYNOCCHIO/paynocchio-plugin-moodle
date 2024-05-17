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

import {handleWithdrawClick, showModalWithWithdraw} from "./repository";
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';

export const init = () => {
    const paynocchio_wallet_withdraw_button = document.getElementById('paynocchio_withdraw_button');

    if (paynocchio_wallet_withdraw_button) {

        paynocchio_wallet_withdraw_button.addEventListener('click', () => {
            showModalWithWithdraw()
                .then(modal => {
                    modal.setTitle('Withdraw from Paynocchio Wallet');
                    const input = document.getElementById('withdraw_amount');
                    const button = document.getElementById('withdraw_button');
                    button.addEventListener('click', () => {
                        if (input.value) {
                            button.classList.add('disabled');
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            handleWithdrawClick(input.value)
                                .then(data => {
                                    if (data.success) {
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#topup_message').text('Success!');
                                        setBalance(data.balance);
                                        setBonus(data.bonuses);
                                        modal.hide();
                                        modal.destroy();
                                        window.location.reload();
                                        Templates.renderForPromise('paygw_paynocchio/wallet_transactions', {
                                            transactions: JSON.parse(data.transactions),
                                            hastransactions: data.hastransactions,
                                        })
                                            .then(({html, js}) => {
                                                Templates.replaceNodeContents('.paynocchio-transactions', html, js);
                                            })
                                            .catch((error) => displayException(error));

                                        // Refresh Card
                                        Templates.renderForPromise('paygw_paynocchio/paynocchio_wallet_actions_buttons', {
                                            wallet_balance: data.balance,
                                            wallet_bonuses: data.bonuses,
                                        })
                                            .then(({html, js}) => {
                                                Templates.replaceNodeContents('#paynocchio_wallet_actions_buttons', html, js);
                                            })
                                            .catch((error) => displayException(error));
                                    }
                                });
                        }
                    });
                });
        });
    }

    /**
     * Setting the Card balance value
     * @param {number} value
     */
    function setBalance(value) {
        const paynocchio_card_balance_value = document.querySelector('.paynocchio-balance-value');
        paynocchio_card_balance_value.innerText = value;
    }

    /**
     * Setting the Card bonuses value
     * @param {number} value
     */
    function setBonus(value) {
        const paynocchio_card_bonus_value = document.querySelector('.paynocchio-bonus-value');
        paynocchio_card_bonus_value.innerText = value;
    }
};