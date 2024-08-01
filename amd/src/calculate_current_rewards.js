import {calculateBenefits} from "./repository";

export const init = (amount, element) => {
    calculateBenefits(amount)
        .then(data => window.console.log(data));
    window.console.log(amount);
    window.console.log(element);
};
