<?

// use Bitrix\Main\UI\Extension;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Лиза Алерт");
// try {
//     Extension::load('ui.vue3');
// } catch (\Bitrix\Main\LoaderException $e) {
// }

?>
<script src="https://api-maps.yandex.ru/2.1?apikey=fc5ff255-a5e4-41ac-bcc1-cd0490e2a486&lang=ru_RU" type="text/javascript"></script>

<div id="app">
    <div class="singlereport">
        <div class="singlereport__report">
            <div class="singlereport__subject">
                <map-section :places="places"></map-section>
            </div>
        </div>
    </div>

    <overlay-modal :show="showModal" @close="hideModal">
        <about-place-modal v-if="modalType === 'view'" :place="selectedPlace"></about-place-modal>
        <edit-place-modal v-if="modalType === 'edit'" :place="selectedPlace"></edit-place-modal>
    </overlay-modal>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.js"></script>

<script>
    Vue.component('map-section', {
        props: ['places'],
        template: `
        <div class='map'>
          <div class='narrow-block'>
            <div class='row'>
              <div class='col-md-7 map' id='map'></div>
              <div class='col-md-5'>
                <div class="map-list__header-container">
                  <h3 class='map-list__header'>Количество добавленных мною опасных мест: {{ places.length }}</h3>
                  <button class='add-button' @click="$emit('add')">Добавить</button>
                </div>
                <status-block></status-block>
                <ol class='address-list'>
                  <li v-for="place in places" :key="place.ID" class='address-item' :data-coords="place.PROPERTY_COORD_VALUE">
                    <div class="address-item__header">
                      <span class="address-item__info">
                        {{ formatDate(place.DATE_CREATE) }} / {{ place.PROPERTY_TYPE_VALUE }}
                      </span>
                      <span class="address-item__status">{{ place.PROPERTY_STATUS_VALUE }}</span>
                    </div>
                    <p class="address-item__title"><strong>{{ place.NAME }}</strong></p>
                    <span class="address-item__link" @click="$emit('view', place.ID)">Подробнее &nbsp;<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 39 15.12" width="1em" height="1em" fill="currentColor">
                      <g id="Слой_x0020_1">
                        <g id="_2134306010608">
                          <rect class="fil0" x="-0.37" y="6.82" width="36.5" height="2.26" />
                          <polygon class="fil0" points="31.07,15.51 29.81,14.25 36.13,7.93 29.83,1.63 31.07,0.39 38.62,7.95 " />
                        </g>
                      </g>
                    </svg></span>
                  </li>
                </ol>
              </div>
            </div>
          </div>
        </div>
      `,
        mounted() {
            this.initializeMap();
        },
        methods: {
            initializeMap() {
                ymaps.ready(() => {
                    const map = new ymaps.Map('map', {
                        center: [this.places[0].PROPERTY_COORD_VALUE.split(',')[0], this.places[0].PROPERTY_COORD_VALUE.split(',')[1]],
                        zoom: 18
                    });

                    this.places.forEach(place => {
                        const coords = place.PROPERTY_COORD_VALUE.split(',');
                        const placemark = new ymaps.Placemark([coords[0], coords[1]], {
                            hintContent: place.NAME,
                            balloonContent: place.NAME
                        });
                        map.geoObjects.add(placemark);
                    });
                });
            },
            formatDate(date) {
                return new Date(date).toLocaleDateString('ru-RU');
            }
        }
    });

    Vue.component('status-block', {
      template: `
        <div class="status-block">
          <div class="status-block__item">
            <div class="status-submitted"></div>
            <span class="status-text">На рассмотрении</span>
          </div>
          <div class="status-block__item">
            <div class="status-wip"></div>
            <span class="status-text">Ведутся работы</span>
          </div>
          <div class="status-block__item">
            <div class="status-done"></div>
            <span class="status-text">Работы выполнены</span>
          </div>
        </div>
      `
    });

    Vue.component('overlay-modal', {
      props: ['show'],
      template: `
        <div class="overlay" v-show="show" @click="$emit('close')">
          <div @click.stop>
            <slot></slot>
          </div>
        </div>
      `
    });

    Vue.component('about-place-modal', {
      props: ['place'],
      template: `
        <div class="about-place__modal">
          <div class="modal-header">
            <span class="modal-header__info">
              <span class="modal-date">{{ formatDate(place.DATE_CREATE) }}</span> / <span class="modal-type">{{ place.PROPERTY_TYPE_VALUE }}</span>
            </span>
          </div>
          <p class="modal-title">{{ place.NAME }}</p>
          <div class="swiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide" v-for="(image, index) in place.GALLERY" :key="index">
                <img :src="image" :alt="'Изображение ' + (index + 1)">
              </div>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          </div>
          <div class="modal-footer">
            <button class="modal__edit-button" @click="$emit('edit')">Редактировать</button>
          </div>
        </div>
      `,
      mounted() {
        this.initializeSwiper();
      },
      methods: {
        formatDate(date) {
          return new Date(date).toLocaleDateString('ru-RU');
        },
        initializeSwiper() {
          new Swiper('.swiper', {
            slidesPerView: 1,
            direction: 'horizontal',
            loop: true,
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev',
            },
          });
        }
      }
    });

    Vue.component('edit-place-modal', {
      props: ['place'],
      template: `
        <div class="edit-place__modal">
          <div class="modal-header">
            <span class="modal-header__info">
              <span class="modal-date">{{ formatDate(place.DATE_CREATE) }}</span> / <span class="modal-type">{{ place.PROPERTY_TYPE_VALUE }}</span>
            </span>
          </div>
          <p class="modal-title">{{ place.NAME }}</p>
          <div class="swiper">
            <div class="swiper-wrapper">
              <div class="swiper-slide" v-for="(image, index) in place.GALLERY" :key="index">
                <img :src="image" :alt="'Изображение ' + (index + 1)">
              </div>
            </div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
          </div>
          <div class="modal-footer">
            <button class="modal__save-button" @click="$emit('save')">Сохранить</button>
            <button class="modal__delete-button" @click="$emit('delete')">Удалить</button>
          </div>
        </div>
      `,
      mounted() {
        this.initializeSwiper();
      },
      methods: {
        formatDate(date) {
          return new Date(date).toLocaleDateString('ru-RU');
        },
        initializeSwiper() {
          new Swiper('.swiper', {
            slidesPerView: 1,
            direction: 'horizontal',
            loop: true,
            navigation: {
              nextEl: '.swiper-button-next',
              prevEl: '.swiper-button-prev',
            },
          });
        }
      }
    });

    new Vue({
        el: '#app',
        data() {
            return {
                places: [],
                showModal: false,
                modalType: 'view',
                selectedPlace: null
            };
        },
        async mounted() {
            this.places = await this.fetchPoints();
            console.log('places', this.places);
        },
        methods: {
            hideModal() {
                this.showModal = false;
            },
            showViewModal(placeId) {
                this.modalType = 'view';
                this.selectedPlace = this.places.find(place => place.ID === placeId);
                this.showModal = true;
            },
            showEditModal() {
                this.modalType = 'edit';
                this.showModal = true;
            },
            async request(url, data) {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams(data)
                });

                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
            },
            async fetchPoints() {
                try {
                    return await request('./ajax.php', {
                        action: 'fetchPoints'
                    });
                } catch (error) {
                    console.error('There has been a problem with your fetch operation:', error);
                }
            }
        }
    });
</script>

<!-- <script>
    BX.Vue3.BitrixVue.createApp({
        data() {
            return {
                currentPoint: {},
                swiper: null,
                points: null
            }
        },
        mounted() {
            this.fetchPoints().then(points => {
                this.$Bitrix.Data.set('points', points);
                console.log('points', this.$Bitrix.Data.get('points'));
            });
        },
        methods: {
            async request(url, data) {
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
            },
            showModal() {
                document.querySelector('.overlay').style.display = 'flex';
                document.querySelector('.about-place__modal').style.display = 'flex';
            },
            hideModal() {
                document.querySelector('.overlay').style.display = 'none';
                document.querySelector('.about-place__modal').style.display = 'none';
            },
            editModal() {
                document.querySelector('.overlay').style.display = 'flex';
                document.querySelector('.edit-place__modal').style.display = 'flex';
            },
            async fetchPoints() {
                try {
                    return await this.request('./ajax.php', {action: 'fetchPoints'});
                } catch (error) {
                    console.error('There has been a problem with your fetch operation:', error);
                }
            }
        }
    }).mount('#app');
</script> -->

<!-- Swiper -->
<!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />-->
<!--<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>-->
<!-- Styles & scripts -->
<script src="script.js"></script>
<link rel="stylesheet" href="style.css">
