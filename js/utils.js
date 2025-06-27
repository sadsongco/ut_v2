const disableButton = (e) => {
  e.preventDefault();
  e.stopPropagation();
  e.target.disabled = true;
  e.target.classList.add('disabled');
};
