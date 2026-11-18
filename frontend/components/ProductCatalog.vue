<template>
  <section class="catalog">
    <h1>Каталог товаров</h1>

    <div v-if="error" class="alert alert-error">
      {{ error }}
    </div>

    <form class="filters" @submit.prevent="applyFilters">
      <label class="field">
        <span>Поиск</span>
        <input v-model.trim="filters.q" type="search" placeholder="Название">
      </label>

      <label class="field">
        <span>Бренд</span>
        <input v-model.trim="filters.brand" type="text" placeholder="Bosch">
      </label>

      <label class="field">
        <span>Цена от</span>
        <input v-model.trim="filters.price_from" type="number" min="0" step="0.01">
      </label>

      <label class="field">
        <span>Цена до</span>
        <input v-model.trim="filters.price_to" type="number" min="0" step="0.01">
      </label>

      <label class="field">
        <span>Наличие</span>
        <select v-model="filters.in_stock">
          <option value="">Все</option>
          <option value="1">В наличии</option>
          <option value="0">Нет в наличии</option>
        </select>
      </label>

      <label class="field">
        <span>Сортировка</span>
        <select v-model="filters.sort">
          <option value="created_at">Дата создания</option>
          <option value="name">Название</option>
          <option value="price">Цена</option>
          <option value="brand">Бренд</option>
          <option value="in_stock">Наличие</option>
        </select>
      </label>

      <label class="field">
        <span>Порядок</span>
        <select v-model="filters.order">
          <option value="desc">По убыванию</option>
          <option value="asc">По возрастанию</option>
        </select>
      </label>

      <label class="field">
        <span>На странице</span>
        <select v-model.number="filters.per_page">
          <option :value="5">5</option>
          <option :value="10">10</option>
          <option :value="20">20</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </label>

      <div class="field">
        <span>&nbsp;</span>
        <div class="actions">
          <button class="btn-primary" type="submit">Применить</button>
          <button class="btn-secondary" type="button" @click="resetFilters">Сброс</button>
        </div>
      </div>
    </form>

    <form class="form-create" @submit.prevent="submitCreate">
      <h2>Добавить товар</h2>

      <div v-if="createMessage" class="alert alert-success">
        {{ createMessage }}
      </div>

      <div class="filters">
        <label class="field">
          <span>Название*</span>
          <input v-model.trim="createForm.name" type="text">
          <small v-if="createErrors.name" class="error">{{ createErrors.name }}</small>
        </label>

        <label class="field">
          <span>Код*</span>
          <input v-model.trim="createForm.code" type="text" placeholder="drel-bosch-gsb-13re">
          <small v-if="createErrors.code" class="error">{{ createErrors.code }}</small>
        </label>

        <label class="field">
          <span>Цена*</span>
          <input v-model.trim="createForm.price" type="number" min="0" step="0.01">
          <small v-if="createErrors.price" class="error">{{ createErrors.price }}</small>
        </label>

        <label class="field">
          <span>Бренд</span>
          <input v-model.trim="createForm.brand" type="text">
          <small v-if="createErrors.brand" class="error">{{ createErrors.brand }}</small>
        </label>

        <label class="field checkbox">
          <span>В наличии</span>
          <input v-model="createForm.in_stock" type="checkbox">
        </label>

        <div class="field">
          <span>&nbsp;</span>
          <button class="btn-primary" type="submit">Создать</button>
        </div>
      </div>
    </form>

    <div v-if="loading" class="loading">
      Загрузка...
    </div>

    <div v-else-if="items.length === 0" class="empty">
      Ничего не найдено
    </div>

    <table v-else class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Название</th>
          <th>Код</th>
          <th>Цена</th>
          <th>Бренд</th>
          <th>Наличие</th>
          <th>Создан</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="product in items" :key="product.id">
          <td>{{ product.id }}</td>
          <td>{{ product.name }}</td>
          <td class="muted">{{ product.code }}</td>
          <td>{{ formatPrice(product.price) }}</td>
          <td>{{ product.brand || '—' }}</td>
          <td>
            <span v-if="product.in_stock" class="badge badge-in-stock">В наличии</span>
            <span v-else class="badge badge-out">Нет</span>
          </td>
          <td>{{ formatDate(product.created_at) }}</td>
        </tr>
      </tbody>
    </table>

    <div v-if="meta" class="pagination">
      <button :disabled="filters.page <= 1" class="btn-secondary" @click="changePage(filters.page - 1)">
        Назад
      </button>

      <span>
        Страница {{ filters.page }} из {{ meta.total_pages }}
      </span>

      <button :disabled="filters.page >= meta.total_pages" class="btn-secondary" @click="changePage(filters.page + 1)">
        Вперед
      </button>

      <span class="muted">Всего: {{ meta.total }}</span>
    </div>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { fetchProducts, createProduct } from '../api/products';

const items = ref([]);
const meta = ref(null);
const loading = ref(false);
const error = ref('');

const filters = reactive({
  q: '',
  brand: '',
  price_from: '',
  price_to: '',
  in_stock: '',
  sort: 'created_at',
  order: 'desc',
  page: 1,
  per_page: 10,
});

const createForm = reactive({
  name: '',
  code: '',
  price: '',
  brand: '',
  in_stock: false,
});

const createErrors = ref({});
const createMessage = ref('');

async function loadProducts() {
  loading.value = true;
  error.value = '';

  try {
    const response = await fetchProducts({
      q: filters.q,
      brand: filters.brand,
      price_from: filters.price_from,
      price_to: filters.price_to,
      in_stock: filters.in_stock,
      sort: filters.sort,
      order: filters.order,
      page: filters.page,
      per_page: filters.per_page,
    });

    items.value = response.data;
    meta.value = response.meta;
  } catch (e) {
    items.value = [];
    meta.value = null;
    error.value = formatError(e);
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  filters.page = 1;
  loadProducts();
}

function resetFilters() {
  Object.assign(filters, {
    q: '',
    brand: '',
    price_from: '',
    price_to: '',
    in_stock: '',
    sort: 'created_at',
    order: 'desc',
    page: 1,
    per_page: 10,
  });

  loadProducts();
}

function changePage(page) {
  filters.page = page;
  loadProducts();
}

async function submitCreate() {
  createErrors.value = {};
  createMessage.value = '';

  try {
    await createProduct({
      name: createForm.name,
      code: createForm.code,
      price: createForm.price === '' ? null : createForm.price,
      brand: createForm.brand,
      in_stock: createForm.in_stock,
    });

    createMessage.value = 'Товар создан';

    createForm.name = '';
    createForm.code = '';
    createForm.price = '';
    createForm.brand = '';
    createForm.in_stock = false;

    await loadProducts();
  } catch (e) {
    if (e.payload?.errors) {
      createErrors.value = e.payload.errors;
    } else {
      error.value = formatError(e);
    }
  }
}

function formatError(e) {
  if (e.payload?.errors) {
    return Object.entries(e.payload.errors)
      .map(([field, message]) => `${field}: ${message}`)
      .join('; ');
  }

  return e.message || 'Неизвестная ошибка';
}

function formatPrice(value) {
  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency: 'RUB',
  }).format(Number(value));
}

function formatDate(value) {
  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleString('ru-RU');
}

onMounted(loadProducts);
</script>