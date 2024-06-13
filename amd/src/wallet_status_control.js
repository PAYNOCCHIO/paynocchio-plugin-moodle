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
    showBlockModal, showDeleteModal,
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
                    const paynocchio_suspend_button = modal.body.find('#suspend_wallet_button');
                    const modal_cancel_button = modal.body.find('#modal_cancel_button');
                    const spinner = modal.body.find('.paynocchio-spinner');

                    paynocchio_suspend_button.click(() => {
                        const response = handleStatusButtonClick('SUSPEND');
                        spinner.toggleClass('active');
                        modal.body.find('#suspend_message').text('Working...');
                        paynocchio_suspend_button.toggleClass('disabled');
                        response.then(data => {
                            if(data.success) {
                                spinner.toggleClass('active');
                                paynocchio_suspend_button.toggleClass('disabled');
                                modal.body.find('#suspend_message').text('Success! Reloading...');
                                /*Templates.renderForPromise('paygw_paynocchio/paynocchio_payment_wallet', {
                                    wallet_balance: 0,
                                    wallet_bonuses: 0,
                                })
                                    .then(({html, js}) => {
                                        Templates.replaceNodeContents('.paynocchio-wallet-wrapper', html, js);
                                    })
                                    .catch((error) => displayException(error));*/
                                window.location.reload();
                            } else {
                                paynocchio_suspend_button.toggleClass('disabled');
                                modal.body.find('#suspend_message').text('Something wrong. Please reload page and try again...');
                            }
                        });
                    });
                    modal_cancel_button.click(() => modal.hide());
                });
        });
    }
    const paynocchio_preblock_button = document.getElementById('preblock_wallet_button');
    if(paynocchio_preblock_button) {
        paynocchio_preblock_button.addEventListener('click', () => {
            showBlockModal()
                .then(modal => {
                    const paynocchio_block_button = modal.body.find('#block_wallet_button');
                    const modal_cancel_button = modal.body.find('#modal_cancel_button');
                    const spinner = modal.body.find('.paynocchio-spinner');

                    paynocchio_block_button.click(() => {
                        const response = handleStatusButtonClick('BLOCKED');
                        spinner.toggleClass('active');
                        paynocchio_block_button.toggleClass('disabled');
                        modal.body.find('#block_message').text('Working...');
                        response.then(data => {
                            if(data.success) {
                                spinner.toggleClass('active');
                                modal.body.find('#block_message').text('Success! Reloading...');
                                window.location.reload();
                            } else {
                                paynocchio_block_button.toggleClass('disabled');
                                modal.body.find('#suspend_message').text('Something wrong. Please reload page and try again...');
                            }
                        });
                    });
                    modal_cancel_button.click(() => modal.hide());
                });
        });
    }
    const paynocchio_predelete_button = document.getElementById('predelete_wallet_button');
    if(paynocchio_predelete_button) {
        paynocchio_predelete_button.addEventListener('click', () => {
            showDeleteModal()
                .then(modal => {
                    const paynocchio_delete_button = modal.body.find('#delete_wallet_button');
                    const modal_cancel_button = modal.body.find('#modal_cancel_button');
                    const spinner = modal.body.find('.paynocchio-spinner');

                    paynocchio_delete_button.click(() => {
                        const response = handleDeleteButtonClick(wallet_uuid);
                        spinner.toggleClass('active');
                        paynocchio_delete_button.toggleClass('disabled');
                        modal.body.find('#delete_message').text('Working...');
                        response.then(data => {
                            if(data.success) {
                                spinner.toggleClass('active');
                                modal.body.find('#delete_message').text('Success! Reloading...');
                                window.location.reload();
                            } else {
                                paynocchio_delete_button.toggleClass('disabled');
                                modal.body.find('#suspend_message').text('Something wrong. Please reload page and try again...');
                            }
                        });
                    });
                    modal_cancel_button.click(() => modal.hide());
                });

        });
    }
    const activate_wallet_button = document.getElementById('activate_wallet_button');
    if(activate_wallet_button) {
        activate_wallet_button.addEventListener('click', () => {
            const spinner = document.querySelector('.paynocchio-spinner');
            const message = document.getElementById('wallet_status_message');
            const response = handleStatusButtonClick('ACTIVE');
            spinner.classList.add('active');
            message.innerText = 'Working...';
            activate_wallet_button.classList.add('disabled');
            response.then(data => {
                if(data.success) {
                    spinner.classList.remove('active');
                    message.innerText = 'Success! Reloading...';
                    window.location.reload();
                } else {
                    message.innerText = 'Something wrong. Please reload page and try again...';
                }
            });
        });
    }
};
