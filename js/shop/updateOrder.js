const updateOrder = async (body) => {
  console.log('UPDATE ORDER');
  if (!body.checkout_reference) return false;
  if (body.checkout_reference) {
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
      //   return console.log(await res.text());
      return await res.json();
    } catch (err) {
      return console.error(err);
    }
  }
  return console.log(body);
};
