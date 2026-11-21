document.body.classList.add('has-js');

const destinationForm = document.querySelector('#destination-form');
if (destinationForm) {
  destinationForm.addEventListener('submit', function (event) {
    if (document.querySelector('#destination_name').value === '' ||
        document.querySelector('#geographical_category').value === '' ||
        document.querySelector('#administrative_region').value === '' ||
        document.querySelector('#short_description').value === '' ||
        document.querySelector('#image_path').value === '' ||
        document.querySelector('#image_alt').value === '') {
      event.preventDefault();
      alert('يرجى تعبئة جميع الحقول المطلوبة.');
    }
  });
}

const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#main-navigation');
const accountMenu = navigation.querySelector('.account-menu');
let menuIsOpen = false;

function closeMenu() {
  navigation.classList.remove('menu-open');
  menuButton.setAttribute('aria-expanded', 'false');
  if (accountMenu) {
    accountMenu.open = false;
  }
  menuIsOpen = false;
}

menuButton.addEventListener('click', function () {
  if (menuIsOpen) {
    closeMenu();
  } else {
    navigation.classList.add('menu-open');
    menuButton.setAttribute('aria-expanded', 'true');
    menuIsOpen = true;
  }
});

const navigationLinks = navigation.querySelectorAll('a');
for (let i = 0; i < navigationLinks.length; i++) {
  navigationLinks[i].addEventListener('click', closeMenu);
}

document.addEventListener('keydown', function (event) {
  if (event.key === 'Escape' && accountMenu && accountMenu.open) {
    accountMenu.open = false;
    accountMenu.querySelector('summary').focus();
  } else if (event.key === 'Escape' && menuIsOpen) {
    closeMenu();
    menuButton.focus();
  }
});

function setupToggle(button, content, openLabel, closeLabel) {
  if (!button || !content) {
    return;
  }
  content.classList.add('is-hidden');
  let isExpanded = false;
  button.textContent = openLabel;

  button.addEventListener('click', function () {
    if (isExpanded) {
      content.classList.add('is-hidden');
      isExpanded = false;
      button.textContent = openLabel;
    } else {
      content.classList.remove('is-hidden');
      isExpanded = true;
      button.textContent = closeLabel;
    }
  });
}

setupToggle(document.querySelector('#why-toggle'), document.querySelector('#why-more'), 'تعرّف أكثر  ←', 'عرض أقل  −');

const filterButtons = document.querySelectorAll('.gallery-filter');
const galleryCards = document.querySelectorAll('.gallery-card');
const resultCount = document.querySelector('#gallery-count');

for (let i = 0; i < filterButtons.length; i++) {
  const button = filterButtons[i];
  button.addEventListener('click', function () {
    const region = button.dataset.filter;
    let visibleCount = 0;

    for (let j = 0; j < filterButtons.length; j++) {
      filterButtons[j].classList.remove('filter-selected');
    }
    button.classList.add('filter-selected');

    for (let j = 0; j < galleryCards.length; j++) {
      const card = galleryCards[j];
      if (region === 'all' || card.dataset.region === region) {
        card.classList.remove('is-hidden');
        visibleCount++;
      } else {
        card.classList.add('is-hidden');
      }
    }
    resultCount.textContent = 'الوجهات المعروضة: ' + visibleCount + ' من ' + galleryCards.length;
  });
}
