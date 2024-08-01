import {calculateBenefits} from "./repository";

export const init = (amount, currency, id) => {
    const element = document.getElementById(id);
    calculateBenefits(amount)
        .then(data => {
            element.innerHTML = data.sale_price + ' ' + currency;
        });
};
