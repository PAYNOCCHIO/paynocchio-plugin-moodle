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
//import {exception as displayException} from 'core/notification';
//import Templates from 'core/templates';

export const init = (pay) => {
    const paynocchio_wallet_topup_button = document.getElementById('paynocchio_topup_button');

    if (paynocchio_wallet_topup_button) {

        paynocchio_wallet_topup_button.addEventListener('click', () => {
            showModalWithTopup()
                .then(modal => {
                    modal.setTitle('Topup Paynocchio Wallet');
                    const input = modal.body.find('#top_up_amount');
                    const button = modal.body.find('#topup_button');
                    button.click(() => {
                        if (input.val()) {
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            handleTopUpClick(input.val())
                                .then(data => {
                                    if (data.success) {
                                        //window.console.log(data);
                                        if(pay) {
                                            window.location.reload();
                                        }
                                        modal.body.find('.paynocchio-spinner').toggleClass('active');
                                        modal.body.find('#topup_message').text('Success!');
                                        setBalance(data.balance);
                                        setBonus(data.bonuses);
                                        setTimeout(() => {
                                            modal.hide();
                                            modal.destroy();

                                            /*if(pay) {
                                                Templates.renderForPromise('paygw_paynocchio/test', [])
                                                    // It returns a promise that needs to be resoved.
                                                    .then(({html, js}) => {
                                                        Templates.appendNodeContents('.paynocchio-profile-block', html, js);
                                                    })

                                                    .catch((error) => displayException(error));
                                            }*/

                                        }, 1000);
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