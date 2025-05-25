const processPayment = async (type, body) => {
  const popOver = document.getElementById('sumup-card');
  const target = document.getElementById('paymentResponse');
  const defeat = document.getElementById('processing-order');
  const res = await updateOrder(body);
  if (!res) return;
  defeat.style.display = 'flex';
  if (res.status != 'success') {
    const output = await getResponseScreen(res.status);
    target.innerHTML = output;
    popOver.style.display = 'none';
    defeat.style.display = 'none';
    return;
  }

  window.location.href = '/shop/success';
};
