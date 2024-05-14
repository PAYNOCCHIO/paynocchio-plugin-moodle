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

/**
* Call server to validate and capture payment for order.
*
* @param {string} component Name of the component that the itemId belongs to
* @param {string} paymentArea The area of the component that the itemId belongs to
* @param {number} itemId An internal identifier that is used by the component
* @param {string} orderId The order id coming back from Paynocchio
* @returns {*}
*/
export const markTransactionComplete = (component, paymentArea, itemId, orderId) => {
    const request = {
        methodname: 'paygw_paynocchio_create_transaction_complete',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            orderid: orderId,
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

export const handleTopUpClick = async (amount) => {
    const request = {
        methodname: 'paygw_paynocchio_topup_wallet',
        args: {
            amount,
        },
    };

    return await Ajax.call([request])[0];
};

/**
 * Creates and shows a modal that contains a placeholder.
 *
 * @returns {Promise<Modal>}
 */
export const showModalWithTopup = async() => await Modal.create({
    body: await Templates.render('paygw_paynocchio/topup_modal', {}),
    show: true,
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
        body: await Templates.render('paygw_bank/bank_button_placeholder', {})
    });
    modal.show();
};

/**
 * Call server to validate and capture payment for order.
 *
 * @param {string} component Name of the component that the itemId belongs to
 * @param {string} paymentArea The area of the component that the itemId belongs to
 * @param {number} itemId An internal identifier that is used by the component
 * @param {string} orderId The order id coming back from Paynocchio
 * @param {number} fullAmount Full amount of the order
 * @param {number} amount Amount with bonuses to pay for the order
 * @param {number} bonuses Boneses used to pay Paynocchio
 * @returns {*}
 */
export const makePayment = (component, paymentArea, itemId, orderId, fullAmount, bonuses) => {
    const request = {
        methodname: 'paygw_paynocchio_make_payment',
        args: {
            component,
            paymentarea: paymentArea,
            itemid: itemId,
            orderid: orderId,
            fullAmount,
            bonuses
        },
    };

    return Ajax.call([request])[0];
};