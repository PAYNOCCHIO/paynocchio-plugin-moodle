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

const env_input = document.getElementById('id_s_paygw_paynocchio_environmentuuid');
const secret_input = document.getElementById('id_s_paygw_paynocchio_paynocchiosecret');
const integration = document.getElementById('admin-paynocchiointegrated');
const surcharge = document.getElementById('admin-surcharge');
surcharge.remove();
integration.remove();

export const init = () => {
    checkButtonVisibility(env_input.value, secret_input.value);

    env_input.addEventListener('keyup', (evt) => checkButtonVisibility(evt.target.value, secret_input.value));
    secret_input.addEventListener('keyup', (evt) => checkButtonVisibility(env_input.value, evt.target.value));
};

/**
 * Functoin to check if Env and secret are inputed
 * @param {string} env
 * @param {string} secret
 */
function checkButtonVisibility(env, secret) {
    const submit_button = document.querySelector('.settingsform button[type="submit"]');

    if(env.trim() === '' || secret.trim() === '') {
        submit_button.classList.add('disabled');
        removeErrorMessage();
        addMessage(submit_button);
    } else {
        submit_button.classList.remove('disabled');
        removeErrorMessage();
    }

    if(env.trim() === '') {
        env_input.classList.add('is-invalid');
    }else {
        if(env_input.classList.contains('is-invalid')) {
            env_input.classList.remove('is-invalid');
        }
    }

    if(secret.trim() === '') {
        secret_input.classList.add('is-invalid');
    } else {
        if(secret_input.classList.contains('is-invalid')) {
            secret_input.classList.remove('is-invalid');
        }
    }
}

/**
 * Adds error message
 * @param {Element} element
 */
function addMessage(element) {
    const p = document.createElement('p');
    p.id = 'submit_error';
    p.innerText = 'Please enter Environment ID and Secret key';
    element.after(p);
}

/**
 * Removes error message
 */
function removeErrorMessage(){
    const p = document.getElementById('submit_error');
    if(p) {
        p.remove();
    }
}