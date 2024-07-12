/**
 * Debounce
 * @param {function} func
 * @param {number} wait
 * @return {(function(...[*]=): void)|*}
 */
export default function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}