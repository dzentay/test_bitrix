const BASE_URL = '/api/v1/products';

async function request(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    ...options,
  });

  let payload = null;

  try {
    payload = await response.json();
  } catch {
    payload = null;
  }

  if (!response.ok) {
    const error = new Error(payload?.message || `HTTP ${response.status}`);
    error.status = response.status;
    error.payload = payload;

    throw error;
  }

  return payload;
}

export function buildProductsUrl(params) {
  const search = new URLSearchParams();

  Object.entries(params).forEach(([key, value]) => {
    if (value === '' || value === null || typeof value === 'undefined') {
      return;
    }

    search.append(key, value);
  });

  const query = search.toString();

  return query ? `${BASE_URL}?${query}` : BASE_URL;
}

export function fetchProducts(params) {
  return request(buildProductsUrl(params));
}

export function createProduct(product) {
  return request(BASE_URL, {
    method: 'POST',
    body: JSON.stringify(product),
  });
}