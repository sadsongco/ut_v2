import '../lib/cleave-zen.index.js';

/**
 * Validates a credit card number using the Luhn algorithm.
 *
 * This function removes all non-numeric characters from the input card number
 * and checks if its length is between 13 and 19 digits, as most credit card
 * numbers fall within this range. It then applies the Luhn algorithm to
 * determine if the card number is valid. The algorithm involves iterating
 * through the digits of the card number, doubling every second digit from the
 * right, and adjusting any results greater than nine by subtracting nine. If
 * the total sum is divisible by 10, the card number is considered valid.
 *
 * @param {string} n - The credit card number as a string, which may contain spaces or hyphens.
 * @returns {boolean} - True if the card number is valid, false otherwise.
 */
const validCard = (n) => {
  // Remove all non-num characters (e.g., spaces or hyphens)
  const s = n.replace(/[^0-9]/g, '');

  // Check if the input length is valid (most cards have between 13 and 19 nums)
  if (s.length < 13 || s.length > 19) {
    return false;
  }

  let sum = 0;
  let sd = false;

  // Loop through the card number nums, starting from the last num
  for (let i = s.length - 1; i >= 0; i--) {
    let num = parseInt(s[i], 10);

    if (sd) {
      num *= 2;
      if (num > 9) {
        num -= 9; // If the result is a two-num number, subtract 9
      }
    }

    sum += num;
    sd = !sd; // Toggle the doubling
  }

  // If the total sum is divisible by 10, it's a valid card number
  return sum % 10 === 0;
};

/**
 * Validates the credit card number input by the user.
 *
 * This function adds an 'invalid' class to the card input element and disables
 * the submit button by default. It checks if the card number is valid by
 * utilizing the `validCard` function with the unformatted card number.
 * If valid, it removes the 'invalid' class from the input, sets focus to the
 * expiration month input, and calls the `activateSubmit` function to
 * potentially enable the submit button.
 *
 * @param {Event} e - The event triggered by the input change.
 */
const validateCardNumber = (e) => {
  ccInput.classList.add('invalid');
  confirm.setAttribute('disabled', true);
  if (validCard(unformatCreditCard(e.target.value))) {
    ccType.value = getCreditCardType(e.target.value);
    ccInput.classList.remove('invalid');
    expM.focus();
    activateSubmit();
  }
};

/**
 * Enables the submit button if all credit card inputs are valid.
 *
 * Checks all credit card input elements for the 'invalid' class. If any of them
 * contain the class, the submit button is not enabled. Otherwise, the submit
 * button is enabled.
 */
const activateSubmit = () => {
  if (ccInput.classList.contains('invalid')) return;
  if (expM.classList.contains('invalid')) return;
  if (expY.classList.contains('invalid')) return;
  if (cvv.classList.contains('invalid')) return;
  confirm.removeAttribute('disabled');
};

/**
 * Checks if a given string is numeric.
 *
 * This function checks if a string is numeric by first ensuring that the input
 * is a string and then using type coercion to parse the entirety of the string.
 * It also makes sure that strings containing only whitespace are not considered
 * numeric.
 *
 * @param {String} str - The string to check.
 *
 * @returns {Boolean} - True if the string is numeric, false otherwise.
 */
const isNumeric = (str) => {
  if (typeof str != 'string') return false; // we only process strings!
  return (
    !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
    !isNaN(parseFloat(str))
  ); // ...and ensure strings of whitespace fail
};

const name = document.getElementById('customerName');
console.log(name);
const ccName = document.getElementById('cc-name');
console.log(ccName);
const ccInput = document.getElementById('cc-in');
const confirm = document.getElementById('confirmSubmit');
const expM = document.getElementById('exp-m');
const expY = document.getElementById('exp-y');
const cvv = document.getElementById('cvv');
const ccType = document.getElementById('ccType');

name.addEventListener('keyup', () => {
  console.log('NAME');
  ccName.value = name.value;
});
ccInput.addEventListener('keyup', validateCardNumber);
expM.addEventListener('keyup', () => {
  expM.classList.add('invalid');
  confirm.setAttribute('disabled', true);
  if (expM.value.length == 2 && expM.value <= 12 && expM.value >= 1) {
    expM.classList.remove('invalid');
    activateSubmit();
    expY.focus();
  }
});
expM.addEventListener('change', () => expY.focus());
expY.addEventListener('keyup', () => {
  expY.classList.add('invalid');
  confirm.setAttribute('disabled', true);
  const twoDigitYear = parseInt(new Date().getFullYear().toString().slice(-2));
  if (expY.value > twoDigitYear || (expY.value == twoDigitYear && expM.value >= new Date().getMonth())) {
    expY.classList.remove('invalid');
    activateSubmit();
    cvv.focus();
  }
});

cvv.addEventListener('keyup', () => {
  cvv.classList.add('invalid');
  confirm.setAttribute('disabled', true);
  if (isNumeric(cvv.value) && cvv.value.length == 3) {
    cvv.classList.remove('invalid');
    activateSubmit();
  }
});
