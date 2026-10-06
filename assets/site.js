
const menuBtn = document.querySelector('.menu-button');
const nav = document.querySelector('.nav');
if (menuBtn && nav) {
  menuBtn.addEventListener('click', () => {
    const open = nav.classList.toggle('open');
    menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
}
document.querySelectorAll('.nav a').forEach(a => a.addEventListener('click', () => nav?.classList.remove('open')));

const estimateForm = document.querySelector('#estimateForm');
if (estimateForm) {
  const status = document.querySelector('#formStatus');
  const button = estimateForm.querySelector('button[type="submit"]');
  const email = estimateForm.elements.email;
  const updateEmailRequirement = () => { email.required = estimateForm.elements.contact.value === 'Email'; };
  estimateForm.elements.contact.addEventListener('change', updateEmailRequirement);
  updateEmailRequirement();
  let sending = false;
  estimateForm.addEventListener('submit', async function(e){
    e.preventDefault();
    if (sending || !this.reportValidity()) return;
    const data = new FormData(this);
    sending = true;
    button.disabled = true;
    button.textContent = 'Sending…';
    this.setAttribute('aria-busy', 'true');
    status.textContent = 'Sending your estimate request…';
    status.dataset.state = '';
    try {
      const response = await fetch(this.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error('Submission failed');
      this.reset();
      this.hidden = true;
      document.querySelector('#estimateHeading').hidden = true;
      const success = document.querySelector('#estimateSuccess');
      success.hidden = false;
      success.focus({ preventScroll: true });
      success.scrollIntoView({ behavior: 'auto', block: 'center' });
    } catch {
      status.textContent = 'We could not confirm your request was sent. Please try again in a few minutes, or call 289-799-9644 or email sales@sm-construction.com. Your details are still here.';
      status.dataset.state = 'error';
      status.focus();
    } finally {
      sending = false;
      button.disabled = false;
      button.textContent = 'Send Estimate Request';
      this.removeAttribute('aria-busy');
    }
  });
}


// Project gallery filters
document.querySelectorAll('.gallery-filter').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.gallery-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('.project-photo').forEach(item => {
      item.classList.toggle('is-hidden', filter !== 'all' && item.dataset.category !== filter);
    });
  });
});

// Image lightbox
const lightboxItems = document.querySelectorAll('[data-lightbox]');
if (lightboxItems.length) {
  const box = document.createElement('div');
  box.className = 'lightbox';
  box.innerHTML = '<div class="lightbox-inner"><button class="lightbox-close" aria-label="Close image">×</button><img alt=""><div class="lightbox-caption"></div></div>';
  document.body.appendChild(box);
  const boxImg = box.querySelector('img');
  const boxCap = box.querySelector('.lightbox-caption');
  const closeBox = () => box.classList.remove('open');
  lightboxItems.forEach(el => el.addEventListener('click', e => {
    e.preventDefault();
    const img = el.querySelector('img') || el;
    boxImg.src = el.getAttribute('href') || img.src;
    boxImg.alt = img.alt || '';
    boxCap.textContent = el.dataset.caption || img.alt || '';
    box.classList.add('open');
  }));
  box.querySelector('.lightbox-close').addEventListener('click', closeBox);
  box.addEventListener('click', e => { if (e.target === box) closeBox(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeBox(); });
}
