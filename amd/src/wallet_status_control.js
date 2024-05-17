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

import {handleStatusButtonClick, showSuspendModal} from "./repository";
//import {exception as displayException} from 'core/notification';
//import Templates from 'core/templates';

export const init = () => {
    const paynocchio_presuspend_button = document.getElementById('presuspend_wallet_button');
    if(paynocchio_presuspend_button) {
        paynocchio_presuspend_button.addEventListener('click', () => {
            showSuspendModal()
                .then((modal) => {
                    modal.setTitle('Suspend your Wallet');
                    const paynocchio_suspend_button = modal.body.getElementById('suspend_wallet_button');
                    const spinner = modal.body.querySelector('.paynocchio-spinner');

                    paynocchio_suspend_button.addEventListener('click', () => {
                        const response = handleStatusButtonClick('SUSPEND');
                        spinner.classList.add('active');
                        response.then(data => {
                            if(data.success) {
                                spinner.classList.remove('active');
                                /*Templates.renderForPromise('paygw_paynocchio/paynocchio_payment_wallet', {
                                    wallet_balance: 0,
                                    wallet_bonuses: 0,
                                })
                                    .then(({html, js}) => {
                                        Templates.replaceNodeContents('.paynocchio-wallet-wrapper', html, js);
                                    })
                                    .catch((error) => displayException(error));*/
                                window.location.reload();
                            }
                        });
                    });
                });
        });
    }
};
