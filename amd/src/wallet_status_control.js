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

import {
    handleDeleteButtonClick,
    handleStatusButtonClick,
    showBlockModal,
    showSuspendModal
} from "./repository";
//import {exception as displayException} from 'core/notification';
//import Templates from 'core/templates';

export const init = (wallet_uuid) => {
    const paynocchio_presuspend_button = document.getElementById('presuspend_wallet_button');
    if(paynocchio_presuspend_button) {
        paynocchio_presuspend_button.addEventListener('click', () => {
            showSuspendModal()
                .then(modal => {
                    const paynocchio_suspend_button = document.getElementById('suspend_wallet_button');
                    const modal_cancel_button = document.getElementById('modal_cancel_button');
                    const spinner = modal.body.find('.paynocchio-spinner');

                    paynocchio_suspend_button.addEventListener('click', () => {
                        const response = handleStatusButtonClick('SUSPEND');
                        spinner.toggleClass('active');
                        response.then(data => {
                            if(data.success) {
                                spinner.toggleClass('active');
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
                    modal_cancel_button.addEventListener('click', () => modal.hide());
                });
        });
    }
    const paynocchio_preblock_button = document.getElementById('preblock_wallet_button');
    if(paynocchio_preblock_button) {
        paynocchio_preblock_button.addEventListener('click', () => {
            showBlockModal()
                .then(modal => {
                    const paynocchio_block_button = document.getElementById('block_wallet_button');
                    const modal_cancel_button = document.getElementById('modal_cancel_button');
                    const spinner = modal.body.find('.paynocchio-spinner');

                    paynocchio_block_button.addEventListener('click', () => {
                        const response = handleStatusButtonClick('BLOCKED');
                        spinner.toggleClass('active');
                        modal.body.find('#block_message').text('Working...');
                        response.then(data => {
                            if(data.success) {
                                spinner.toggleClass('active');
                                modal.body.find('#block_message').text('Success! Reloading...');
                                window.location.reload();
                            }
                        });
                    });
                    modal_cancel_button.addEventListener('click', () => modal.hide());
                });
        });
    }
    const paynocchio_predelete_button = document.getElementById('predelete_wallet_button');
    if(paynocchio_predelete_button) {
        paynocchio_predelete_button.addEventListener('click', () => {
            const spinner = document.querySelector('.paynocchio-spinner');
            const response = handleDeleteButtonClick(wallet_uuid);
            spinner.classList.add('active');
            response.then(data => {
                if(data.success) {
                    spinner.classList.remove('active');
                    window.location.reload();
                }
            });
        });
    }
    const activate_wallet_button = document.getElementById('activate_wallet_button');
    if(activate_wallet_button) {
        activate_wallet_button.addEventListener('click', () => {
            const spinner = document.querySelector('.paynocchio-spinner');
            const response = handleStatusButtonClick('ACTIVE');
            spinner.classList.add('active');
            response.then(data => {
                if(data.success) {
                    spinner.classList.remove('active');
                    window.location.reload();
                }
            });
        });
    }
};
