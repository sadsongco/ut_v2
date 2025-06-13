const resize = async (e) => {
  const item = document.getElementById(e.target.dataset.targetid);
  if (item.id === 'hero') {
    if (item.classList.contains('is-open')) setTimeout(stopCarousel, 500);
    else startCarousel();
  }
  await resizeAccordion(item);
};

const resizeHTMX = async (e) => {
  const item = e.detail.target;
  if (item.id !== 'blog-content') return;
  item.style.maxHeight = `${item.scrollHeight}px`;
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
    target.style.padding = '0px';
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
    target.style.transition = 'max-height 0.5s ease-in-out, padding 0.5s ease-in-out';
    target.style.maxHeight = '0px';
    target.style.padding = '0px';
  });
};

const accordionContent = document.querySelectorAll('.accordion');

accordionContent.forEach((item) => {
  let header = item.querySelector('header');
  header.addEventListener('click', resize);
});

document.body.addEventListener('htmx:afterSettle', resizeHTMX);
window.onload = () => {
  if (accordionContent.length === 0) return;
  startCarousel();
  resizeAccordion(accordionContent[0]);
};
