let currentPoint = {}

async function request(url, data) {
  const response = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  });

  if (response.ok) {
    return response.json();
  } else {
    throw new Error(`HTTP error! status: ${response.status}`);
  }
}

async function fetchPoints() {
  try {
    return await request('./ajax.php', { action: 'fetchPoints' });
  } catch (error) {
    console.error('There has been a problem with your fetch operation:', error);
  }
}

// Map
ymaps.ready(() => {
  const items = document.querySelectorAll('.address-item');
  const map = new ymaps.Map('viewMap', {
    center: [items[0].dataset.coords.split(',')[0], items[0].dataset.coords.split(',')[1]],
    zoom: 18
  });

  document.querySelectorAll('.address-item').forEach(el => {
    const coords = el.dataset.coords.split(',');
    const placemark = new ymaps.Placemark([coords[0], coords[1]], {
      hintContent: el.textContent,
      balloonContent: el.textContent
    });
    map.geoObjects.add(placemark);

    el.addEventListener('click', () => {
      map.setCenter([coords[0], coords[1]]);
    });
  });
});

// Swiper
let swiper;

// Modal просмотр
const overlay = document.querySelector('.overlay');
const modal = document.querySelector('.about-place__modal');
const moreLinks = document.querySelectorAll('.address-item__link');

overlay.addEventListener('click', hideModal);

moreLinks.forEach(el => {
  el.addEventListener('click', () => {
    const id = el.dataset.id;
    modal.style.display = 'flex';
    overlay.style.display = 'flex';

    getPlaceInfo(id);

    swiper = new Swiper('.swiper', {
      slidesPerView: 1,
      direction: 'horizontal',
      loop: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  });

});

async function getPlaceInfo(id) {
  try {
    const data = await request('./ajax.php', { action: 'getElementById', id });

    if (data.error) {
      throw new Error('Server error: ' + data.error);
    }

    currentPoint = data;

    updateModal('.about-place__modal', data);

  } catch (error) {
    console.error('There has been a problem with your fetch operation:', error);
  }
};

function hideModal() {
  overlay.style.display = 'none';
  modal.style.display = 'none';
  editModal.style.display = 'none';

  const swiperSlide = document.querySelector('.swiper-slide');
  swiperSlide.innerHTML = '';

  swiper = null;
  currentPoint = {};
}

document.addEventListener('keydown', function (event) {
  if (event.key === "Escape" && modal.style.display === 'flex' || event.key === "Escape" && editModal.style.display === 'flex') {
    hideModal();
  }
});

async function updateModal(modalClass, data) {
  const modalElement = document.querySelector(modalClass);
  modalElement.addEventListener('click', (e) => e.stopPropagation());

  const modalDate = modalElement.querySelector('.modal-date');
  const modatType = modalElement.querySelector('.modal-type');
  const modalTitle = modalElement.querySelector('.modal-title');
  const swiperSlide = modalElement.querySelector('.swiper-slide');
  const modalFooter = modalElement.querySelector('.modal-footer');

  if (modalClass === '.edit-place__modal' && data) {
    modalDate.innerText = formatDate(data.DATE_CREATE);
    modatType.innerText = data.TYPE;
    modalTitle.innerText = data.NAME;

    modalFooter.innerHTML = `
      <button class="modal__save-button">Сохранить</button>
      <button class="modal__delete-button">Удалить</button>
    `;
    modalFooter.style.justifyContent = 'space-between';
  }

  if (modalClass === '.edit-place__modal' && !data) {
    const pointTypesSelect = document.querySelector('#pointType');
    const pointTypes = await getPointTypes();
    pointTypes.forEach(pointType => {
      const option = document.createElement('option');
      option.value = pointType.ID;
      option.innerText = pointType.VALUE;
      pointTypesSelect.append(option);
    })
    console.log('Types:', pointTypes);

    modalFooter.innerHTML = `
      <button class="modal__edit-button" type="submit">Добавить</button>
    `;
    modalFooter.style.justifyContent = 'center';
  }

  if (data) {
    modalDate.innerText = formatDate(data.DATE_CREATE);
    modatType.innerText = data.TYPE;
    modalTitle.innerText = data.NAME;

    if (data.GALLERY) {
      data.GALLERY.forEach((image, index) => {
        const swiperImage = document.createElement('img');
        swiperImage.setAttribute('src', image);
        swiperImage.setAttribute('alt', `Изображение ${index + 1}`);
        swiperSlide.append(swiperImage);
      })
    } else {
      swiperSlide.innerHTML = 'Изображения отсутствуют';
    }
  } else {
    modalDate.innerText = formatDate(new Date());
    modatType.innerText = 'Опасное место';
    modalTitle.innerText = 'Новое место';

  }
}

function formatDate(dateStr) {
  return new Date(Date(dateStr)).toLocaleString('ru-RU', { year: 'numeric', month: 'numeric', day: 'numeric' });
}

// Modal редактирование

const editButton = document.querySelector('.modal__edit-button');
editModal = document.querySelector('.edit-place__modal');

editButton.addEventListener('click', () => {
  overlay.style.display = 'flex';
  modal.style.display = 'none';
  editModal.style.display = 'flex';
  updateModal('.edit-place__modal', currentPoint);

  ymaps.ready(() => {
    const coords = currentPoint.COORDS.split(',');
    const map = new ymaps.Map('map', {
      center: [coords[0], coords[1]],
      zoom: 18
    });
    const placemark = new ymaps.Placemark([coords[0], coords[1]], {
      hintContent: currentPoint.NAME,
      balloonContent: currentPoint.NAME
    });
    map.geoObjects.add(placemark);
  });
});

const addButton = document.querySelector('.add-button');
addButton.addEventListener('click', () => {
  overlay.style.display = 'flex';
  editModal.style.display = 'flex';
  updateModal('.edit-place__modal', null);
})

async function getPointTypes() {
  try {
    const data = await request('./ajax.php', { action: 'getPointTypes' });
    return data;
  } catch (error) {
    console.error('There has been a problem with your fetch operation:', error);
  }
}
