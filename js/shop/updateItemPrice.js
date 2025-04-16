const updateItemPrice = (e) => {
  e.preventDefault();
  const price = parseFloat(e.target.options[e.target.selectedIndex].dataset.price);
  document.getElementById('itemPrice').innerHTML = price.toFixed(2);
};
