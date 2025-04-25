const showHideBillingAddress = (e) => {
  if (e.target.checked) {
    document.getElementById('billingAddress').classList.add('hidden');
  } else {
    document.getElementById('billingAddress').classList.remove('hidden');
  }
};
