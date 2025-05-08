const resize = async (e) => {
  console.log('RESIZE');
  console.log(e.target.dataset.targetId);
  await resizeAccordion(item);
  resizeMain();
};

const resizeAccordion = async (item) => {
  closeOpenAccordion(item.id);
  const target = document.getElementById(`${item.id}-content`);
  item.classList.toggle('is-open');
  if (item.classList.contains('is-open')) {
    // Scrollheight property return the height of
    // an element including padding
    target.style.maxHeight = `${target.scrollHeight}px`;
    item.querySelector('i').classList.replace('fa-plus', 'fa-minus');
  } else {
    target.style.maxHeight = '0px';
    item.querySelector('i').classList.replace('fa-minus', 'fa-plus');
  }
};

const resizeMain = async () => {
  const main = document.getElementsByTagName('main')[0];
  main.style.maxHeight = main.scrollHeight;
};

const closeOpenAccordion = (id) => {
  accordionContent.forEach((item) => {
    if (item.id === id) return;
    const target = document.getElementById(`${item.id}-content`);
    item.classList.remove('is-open');
    item.querySelector('i').classList.replace('fa-minus', 'fa-plus');
    target.style.maxHeight = '0px';
  });
};

const accordionContent = document.querySelectorAll('.accordion');

accordionContent.forEach((item, index) => {
  let header = item.querySelector('header');
  header.addEventListener('click', resize);
});
