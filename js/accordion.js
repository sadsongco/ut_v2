const resize = async (e) => {
  const item = document.getElementById(e.target.dataset.targetid);
  await resizeAccordion(item);
};

const resizeAccordion = async (item) => {
  closeOpenAccordion(item.id);
  const target = document.getElementById(`${item.id}-content`);
  item.classList.toggle('is-open');
  if (item.classList.contains('is-open')) {
    // Scrollheight property return the height of
    // an element including padding
    target.style.transition = 'max-height 0.5s ease-in-out, padding 0s linear';
    target.style.padding = 'var(--stdPaddingSmall)';
    target.style.maxHeight = `${target.scrollHeight}px`;
    item.querySelector('i').classList.replace('fa-plus', 'fa-minus');
  } else {
    target.style.transition = 'max-height 0.5s ease-in-out, padding 0.5s ease-in-out';
    target.style.padding = '0';
    target.style.maxHeight = '0px';
    item.querySelector('i').classList.replace('fa-minus', 'fa-plus');
  }
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

accordionContent.forEach((item) => {
  let header = item.querySelector('header');
  header.addEventListener('click', resize);
});
