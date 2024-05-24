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
import {exception as displayException} from 'core/notification';
import Templates from 'core/templates';

export const init = (pay) => {
    const paynocchio_wallet_topup_button = document.getElementById('paynocchio_topup_button');

    let need_to_top_up = 0;
    if(pay && document.getElementById('need_to_top_up')) {
        need_to_top_up = parseFloat(document.getElementById('need_to_top_up').innerText);
    }

    if (paynocchio_wallet_topup_button) {

        paynocchio_wallet_topup_button.addEventListener('click', () => {
            showModalWithTopup()
                .then(modal => {
                    modal.setTitle('Topup Paynocchio Wallet');
                    const input = modal.body.find('#top_up_amount');
                    const message = modal.body.find('#topup_message');
                    if(pay && need_to_top_up) {
                        input.val(need_to_top_up);
                        message.text(`You will get ${need_to_top_up * 0.1} bonuses`);
                    }
                    input.on('keyup', (evt) => {
                        message.text(`You will get ${(parseFloat(evt.target.value) * 0.1).toFixed(1)} bonuses`);
                    });
                    const button = modal.body.find('#topup_button');
                    button.click(() => {
                        if (input.val()) {
                            button.addClass('disabled');
                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                            modal.body.find('#topup_message').text('Working...');
                            handleTopUpClick(input.val())
                                .then(data => {
                                    if (data.success) {
                                        modal.body.find('#topup_message').text('Success! Reloading...');
                                        //window.console.log(data);
                                        if(pay) {
                                            window.location.reload();
                                        } else {
                                            modal.body.find('.paynocchio-spinner').toggleClass('active');
                                            setBalance(data.balance);
                                            setBonus(data.bonuses);
                                            window.location.reload();
                                            setTimeout(() => {
                                                modal.hide();
                                                modal.destroy();
                                                Templates.renderForPromise('paygw_paynocchio/wallet_transactions', {
                                                    transactions: JSON.parse(data.transactions),
                                                    hastransactions: data.hastransactions,
                                            })
                                                    .then(({html, js}) => {
                                                        Templates.replaceNodeContents('.paynocchio-transactions', html, js);
                                                    })
                                                    .catch((error) => displayException(error));

                                            }, 1000);
                                        }
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