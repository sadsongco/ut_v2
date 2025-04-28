const showHideBillingAddress = (e) => {
  if (e.target.checked) {
    document.getElementById('billingAddress').classList.add('hidden');
  } else {
    document.getElementById('billingAddress').classList.remove('hidden');
  }
};

const mirrorDeliveryAddress = (e) => {
  const arr = e.target.id.split('-');
  const target = document.getElementById(`billing-${arr[1]}`);
  target.value = e.target.value;
};

const updateShippingMethods = (e) => {
  console.log(e.target.value);
};
