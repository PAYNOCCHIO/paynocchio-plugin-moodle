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

import Ajax from 'core/ajax';
import Templates from 'core/templates';
import Modal from 'core/modal';

/**
 * Return the Paynocchio JavaScript SDK URL.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @returns {Promise<{clientid: string, brandname: string, cost: number, currency: string}>}
 */
export const getConfigForJs = (component, paymentArea, itemId) => {
    const request = {
        methodname: 'paygw_paynocchio_get_config_for_js',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
        },
    };

    return Ajax.call([request])[0];
};

export const handleWalletActivationClick = async (user_id) => {
    const request = {
        methodname: 'paygw_paynocchio_activate_wallet',
        args: {
            userId: user_id,
        },
    };

    return await Ajax.call([request])[0];
};

export const handleTopUpClick = async (amount, redirect_url) => {
    const request = {
        methodname: 'paygw_paynocchio_topup_wallet',
        args: {
            amount,
            redirect_url: redirect_url,
        },
    };

    return await Ajax.call([request])[0];
};

export const handleWithdrawClick = async (amount) => {
    const request = {
        methodname: 'paygw_paynocchio_withdraw_from_wallet',
        args: {
            amount,
        },
    };

    return await Ajax.call([request])[0];
};

/**
 * Suspend wallet
 * @param {text} $status
 * @return {Promise<*>}
 */
export const handleStatusButtonClick = async ($status) => {
    const request = {
        methodname: 'paygw_paynocchio_update_wallet_status',
        args: {
            status: $status,
        },
    };

    return await Ajax.call([request])[0];
};

/**
 * Delete wallet
 * @param {string} wallet_uuid
 * @return {Promise<*>}
 */
export const handleDeleteButtonClick = async (wallet_uuid) => {
    const request = {
        methodname: 'paygw_paynocchio_delete_wallet',
        args: {
            wallet_uuid: wallet_uuid,
        },
    };

    return await Ajax.call([request])[0];
};

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @param {string} minimum_topup_amount
 * @param {string} card_balance_limit
 * @param {number} cost
 * @param {number} topupamount
 * @returns {Promise<Modal>}
 */
export const showModalWithTopup = async(minimum_topup_amount, card_balance_limit, cost, topupamount) => await Modal.create({
    body: await Templates.render('paygw_paynocchio/topup_modal', {minimum_topup_amount, card_balance_limit, cost, topupamount }),
    show: true,
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a withdrawal form.
 *
 * @param {number} maximum_for_withdrawal
 * @returns {Promise<Modal>}
 */
export const showModalWithWithdraw = async(maximum_for_withdrawal) => await Modal.create({
    body: await Templates.render('paygw_paynocchio/withdraw_modal', {maximum_for_withdrawal}),
    show: true,
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a withdrawal form.
 * @returns {Promise<Modal>}
 */
export const showModalWithTerms = async() => await Modal.create({
    show: true,
    removeOnClose: true,
    large: true,
});

/**
 * Creates and shows a modal that contains a withdrawal form.
 * @returns {Promise<Modal>}
 */
export const showModalWithPrivacy = async() => await Modal.create({
    show: true,
    removeOnClose: true,
    large: true,
});

/**
 * Creates and shows a modal that contains a Suspension form.
 *
 * @returns {Promise<Modal>}
 */
export const showSuspendModal = async() => await Modal.create({
    title: 'Suspend your Wallet',
    body: await Templates.render('paygw_paynocchio/suspend_modal', {}),
    show: true,
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a Suspension form.
 *
 * @returns {Promise<Modal>}
 */
export const showBlockModal = async() => await Modal.create({
    title: 'Block your Wallet',
    body: await Templates.render('paygw_paynocchio/block_modal', {}),
    show: true,
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a Suspension form.
 *
 * @returns {Promise<Modal>}
 */
export const showDeleteModal = async() => await Modal.create({
    title: 'Delete your Wallet',
    body: await Templates.render('paygw_paynocchio/delete_modal', {}),
    show: true,
    type: 'Delete',
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
export const showModalWallet = async() => await Modal.create({
    body: await Templates.render('paygw_paynocchio/paynocchio_wallet', {}),
    show: true,
    removeOnClose: true,
});

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
export const showModalWithPlaceholder = async() => {
    const modal = await Modal.create({
        title: 'Redirecting...',
        body: await Templates.render('paygw_paynocchio/paynocchio_placeholder', {})
    });
    modal.show();
};

/**
 * Call server to validate and capture payment for order.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {string} description The area of the component that the itemId belongs to
 * @param {number} itemid An internal identifier that is used by the component
 * @param {number} fullAmount Full amount of the order
 * @param {number} bonuses Boneses used to pay Paynocchio
 * @returns {*}
 */
export const makePayment = (component, paymentArea, description, itemid, fullAmount, bonuses) => {
    const request = {
        methodname: 'paygw_paynocchio_make_payment',
        args: {
            component,
            paymentarea: paymentArea,
            description,
            itemid,
            fullAmount,
            bonuses
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Call server to check if payment was confirmed.
 *
 * @param {number} paymentid id of the Payment to check
 * @returns {*}
 */
export const checkPaymentConfirmation = (paymentid) => {
    const request = {
        methodname: 'paygw_paynocchio_check_payment_confirmation',
        args: {
            paymentid
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Get calculated rules from Server according to sum
 *
 * @param {number} amount id of the Payment to check
 * @param {string} operationType type for Rule
 * @returns {*}
 */
export const calculateReward = (amount, operationType) => {
    const request = {
        methodname: 'paygw_paynocchio_calculate_current_rewards',
        args: {
            amount,
            operationType,
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Get calculated rules from Server according to sum
 *
 * @param {number} amount id of the Payment to check
 * @returns {*}
 */
export const calculateBenefits = (amount) => {
    const request = {
        methodname: 'paygw_paynocchio_calculate_benefits',
        args: {
            amount,
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Check if Withdrawal is OK and calculate Commission
 *
 * @param {number} amount id of the Payment to check
 * @returns {*}
 */
export const checkWithdrawal = (amount) => {
    const request = {
        methodname: 'paygw_paynocchio_check_withdrawal',
        args: {
            amount,
        },
    };

    return Ajax.call([request])[0];
};

/**
 * Call server to get conf.
 *
 * @param {string} conf_name Name of the conf
 * @returns {*}
 */
export const getConf = (conf_name) => {
    const request = {
        methodname: 'paygw_paynocchio_get_conf',
        args: {
            conf_name,
        },
    };

    return Ajax.call([request])[0];
};