const spoofResponse = {
  id: '9f4b22bd-d95a-4c2a-a076-4bd753fc2f1e',
  description: 'Test Account',
  merchant_name: 'undefined',
  merchant_code: 'MCCHLZ27',
  status: 'SUCCESS',
  amount: '20.75',
  currency: 'GBP',
  checkout_reference: '20250516-290',
  transaction_code: 'TAAAUUY3UXH',
  transaction_id: 'f2fdcf85-e0b8-4a20-90f5-58fe2e757606',
};

const updateOrder = async (body) => {
  body = spoofResponse;
  if (!body.checkout_reference) return false;
  const postBody = new FormData();
  for (const [key, value] of Object.entries(body)) {
    postBody.append(key, value);
  }
  const apiURL = '/functions/interface/shop/update_order.php';
  try {
    const res = await fetch(apiURL, {
      method: 'POST',
      body: postBody,
    });

    return await res.text();
    // return await res.json();
  } catch (err) {
    console.log(err.message);
    return false;
  }
};
