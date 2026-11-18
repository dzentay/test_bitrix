# Vendor Catalog

Небольшой каталог товаров с серверной фильтрацией, REST API и фронтендом на Vue 3.  
Проект оформлен в стиле локального модуля 1С-Битрикс и готов к локальному запуску через Docker.

## Стек

- PHP 8.2
- MySQL 8
- Redis 7
- Vue 3 (Composition API)
- Webpack
- Docker / Docker Compose
- Composer / npm

## Архитектура

    Проект оформлен как локальный модуль `local/modules/vendor.catalog/`:

    HTTP Request
    │
    ▼
    ┌─────────────────┐
    │ Router (index) │
    └────────┬────────┘
    ▼
    ┌─────────────────┐
    │ ApiController │ ← HTTP слой, формирование ответа
    └────────┬────────┘
    ▼
    ┌─────────────────┐
    │ ProductService │ ← бизнес-логика, кэш
    └───┬──────────┬──┘
    ▼ ▼
    ┌──────────┐ ┌─────────────┐
    │ Validator│ │ Repository │ ← работа с PDO
    └──────────┘ └──────┬──────┘
    ▼
    ┌──────────┐
    │ PDO │
    └──────────┘


Также используется `RedisCacheService` для кэширования списков.

## Быстрый старт (Docker)

### 1. Склонировать и настроить окружение

```bash
cp .env.example .env

Отредактируйте .env при необходимости. По умолчанию:
    APP_TIMEZONE=Europe/Moscow
    DB_DATABASE=catalog
    DB_USERNAME=catalog
    DB_PASSWORD=secret
    REDIS_HOST=redis
    CACHE_TTL=60
    HOST_PORT=8090

2. Запустить контейнеры
    docker compose up --build

    При первом запуске автоматически выполнятся:
    миграция таблицы vendor_catalog_product;
    сид базы 18 тестовыми товарами.
3. Открыть приложение
    Фронтенд: http://localhost:8090 (лучше использовать ip, а не localhost или 127.0.0.1)
API: http://localhost:8090/api/v1/products

Полезные команды
# Остановить
docker compose down

# Полностью очистить БД
docker compose down -v

# Войти в контейнер API
docker compose exec api sh

# Заново применить миграции
docker compose exec api php bin/migrate.php

# Заново выполнить сид
docker compose exec api php bin/seed.php

# Посмотреть логи API
docker compose logs api -f


Локальная разработка фронтенда
Фронтенд собирается в Docker, но его можно разрабатывать и отдельно:

    npm install
    npm run watch      # пересборка при изменениях
    npm run build      # финальная сборка
    Собраные файлы попадают в public/assets/.
    API
    Все ответы отдаются в формате JSON. Корневой URL: /api/v1/products.
    GET /api/v1/products
    Возвращает пагинированный список товаров с серверной фильтрацией.
    Параметры (query string)
    API
    Все ответы отдаются в формате JSON. Корневой URL: /api/v1/products.
    GET /api/v1/products
    Возвращает пагинированный список товаров с серверной фильтрацией.
Параметры (query string)

Разрешённые значения sort:
    name
    price
    brand
    created_at
    in_stock
    POST /api/v1/products
    Создаёт новый товар.
    Тело запроса (JSON)

Разрешённые значения sort:
    name
    price
    brand
    created_at
    in_stock
    
POST /api/v1/products
Создаёт новый товар.
Тело запроса (JSON)

    {
    "name": "Дрель ударная Bosch GSB 13RE",
    "code": "drel-bosch-gsb-13re",
    "price": 8990.00,
    "brand": "Bosch",
    "in_stock": true
    }

Примеры запросов (curl)
Получить список товаров
    curl "http://localhost:8090/api/v1/products"

Поиск по названию и бренду
    curl "http://localhost:8090/api/v1/products?q=дрель&brand=Bosch&in_stock=yes"

Фильтр по цене с сортировкой
    curl "http://localhost:8090/api/v1/products?price_from=10000&price_to=50000&sort=price&order=asc&page=1&per_page=10"

Создать товар
    curl -X POST http://localhost:8090/api/v1/products \
    -H "Content-Type: application/json" \
    -d '{
        "name": "Дрель ударная Bosch GSB 13RE",
        "code": "drel-bosch-gsb-13re",
        "price": 8990.00,
        "brand": "Bosch",
        "in_stock": true
    }'
  
  
Примеры ответов

Успешный список (200 OK)
    {
    "data": [
        {
        "id": 1,
        "name": "Дрель ударная Bosch GSB 13RE",
        "code": "drel-bosch-gsb-13re",
        "price": "8990.00",
        "brand": "Bosch",
        "in_stock": true,
        "created_at": "2026-11-10T12:00:00+03:00"
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 10,
        "total": 18,
        "total_pages": 2
    }
    }

Успешное создание (201 Created)
    {
    "data": {
        "id": 19,
        "name": "Дрель ударная Bosch GSB 13RE",
        "code": "drel-bosch-gsb-13re",
        "price": "8990.00",
        "brand": "Bosch",
        "in_stock": true,
        "created_at": "2026-11-18T12:45:00+03:00"
    }
    }

Ошибка валидации (422 Unprocessable Entity)
    {
    "message": "Validation failed",
    "errors": {
        "name": "Name is required",
        "code": "Code must be a slug: lowercase latin letters, digits and hyphens",
        "sort": "Allowed sort fields: name, price, brand, created_at, in_stock"
    }
    }

Конфликт (409 Conflict)

    {
    "message": "Product with this code already exists"
    }

Серверная ошибка (500 Internal Server Error)
    {
    "message": "Internal server error"
    }
В режиме APP_DEBUG=1 добавится поле error с текстом исключения.


Структура проекта

.
    ├── bin/
    │   ├── migrate.php                 # Создание таблицы
    │   ├── seed.php                    # Наполнение тестовыми данными
    │   └── wait-for-db.php             # Ожидание поднятия MySQL
    ├── docker/
    │   ├── entrypoint.sh               # Запуск миграций и сервера
    │   └── php/Dockerfile              # Образ API
    ├── frontend/
    │   ├── api/products.js             # Клиент API
    │   ├── components/ProductCatalog.vue
    │   ├── styles/app.css
    │   ├── App.vue
    │   └── main.js
    ├── local/modules/vendor.catalog/
    │   ├── include.php                 # Точка подключения модуля
    │   ├── install/index.php           # Заготовка установщика Битрикс
    │   └── lib/
    │       ├── Application/            # Bootstrap, Container, Env
    │       ├── Bitrix/ProductTable.php # Заготовка D7 ORM
    │       ├── Cache/                  # RedisCacheService
    │       ├── Database/               # ConnectionFactory
    │       ├── Domain/Product.php      # Модель товара
    │       ├── Exceptions/             # Validation / Conflict
    │       ├── Http/                   # Router, Request, Response, ApiController
    │       ├── Repository/             # ProductRepository
    │       ├── Service/                # ProductService
    │       └── Validation/             # ProductValidator
    ├── public/
    │   ├── index.php                   # Точка входа
    │   └── assets/                     # Собранный фронтенд
    ├── .env.example
    ├── composer.json
    ├── docker-compose.yml
    ├── package.json
    ├── webpack.config.js
    └── README.md


Важные допущения
1. Нет полноценного ядра 1С-Битрикс.
    Полноценная установка Битрикс требует лицензии и ручного мастера настройки. Для гарантированного локального запуска проект работает как автономное PHP-приложение, но оформлен в виде локального модуля local/modules/vendor.catalog/ по стандарту Битрикс.
    Каталог в собственной таблице vendor_catalog_product.
    По ТЗ предпочтительнее инфоблок Битрикса. Реализована собственная таблица — это проще, быстрее и полностью работоспособно локально.
    Для миграции на инфоблок достаточно заменить ProductRepository на ProductTable (D7 ORM) или CIBlockElement.
    Формат полей соответствует инфоблоку.
    В таблице есть все поля из ТЗ: ID, NAME, CODE, PRICE, BRAND, IN_STOCK, CREATED_AT, UPDATED_AT. Заготовка Bitrix\ProductTable с описанием полей уже включена.
    Что упрощено и как доделать
    1. Полноценный инфоблок Битрикс
    Что сделано: собственная таблица.
    Как доделать:
    Установить Битрикс (редакция «Малый бизнес» или выше).
    Создать инфоблок «Товары» со свойствами: Цена, Бренд, Наличие.
    Переписать ProductRepository на CIBlockElement::GetList() или Bitrix\Main\ORM\Query\Query через ProductTable.
    Перенести сид в админку или скрипт с CIBlockElement::Add().

2. Авторизация и права доступа
    Что сделано: API открыто.
    Как доделать:
    Использовать сессии Битрикса (CSite::GetDefSite(), $USER->IsAdmin()).
    Для внешнего API — добавить JWT или API-ключи в заголовок Authorization.
    В ApiController::create() проверять права перед созданием.
3. Тесты
    Что сделано: нет автоматических тестов.
    Как доделать:
    phpunit/phpunit для ProductValidator и ProductService.
    Интеграционные тесты API с тестовой БД через TestContainers.
    Vitest / Jest для фронтенда: проверка запросов и фильтрации.
4. Кэш
    Что сделано: список товаров кэшируется в Redis на 60 секунд, инвалидируется по KEYS prefix*.
    Как доделать:
    Использовать теги кэша (Bitrix\Main\Data\TaggedCache).
    Или хранить версию списка в отдельном ключе и инкрементировать её при создании товара — это быстрее KEYS.
    Для высоких нагрузок — фоновая инвалидация через очередь (Beanstalk / RabbitMQ).
5. Обновление и удаление товара
    Что сделано: только создание и список.
    Как доделать:
    PUT /api/v1/products/{id} — обновление.
    DELETE /api/v1/products/{id} — удаление.
    В репозитории — методы update() и delete() с проверкой ID и инвалидацией кэша.
6. Расширенная валидация кода
    Что сделано: slug-регулярка.
    Как доделать:
    Проверять уникальность CODE в валидаторе перед INSERT.
    Автоматически транслитерировать NAME в CODE, если код не передан.
    Использовать CSite::GetDefSite() и CUtil::translit() в Битрикс-окружении.
7. Фронтенд
    Что сделано: минимальный UI без сторонних библиотек.
    Как доделать:
    Разбить ProductCatalog.vue на компоненты: Filters, ProductTable, Pagination, CreateProductForm.
    Debounce для поиска по названию (300–500 мс).
    AbortController для отмены старых запросов.
    Сохранение фильтров в URL через history.pushState.
    Skeleton-loader при загрузке.
8. Админка
    Что сделано: нет.
    Как доделать:
    Страница списка товаров с редактированием inline.
    Форма создания/редактирования с подтверждением удаления.
    Импорт/экспорт товаров (CSV / Excel).
9. Логирование и метрики
    Что сделано: только error_log при необработанных исключениях.
    Как доделать:
    Monolog с каналами api, cache, database.
    Отдельный канал для ошибок валидации.
    Prometheus + Grafana для мониторинга API.
    10. CI/CD
    Что сделано: базовый GitHub Actions с php -l и сборкой фронтенда.
    Как доделать:
    Запуск phpunit.
    Запуск phpstan / psalm.
    Запуск eslint / prettier для фронтенда.
    Сборка Docker-образов и публикация в registry.
    Автотесты API в поднятом контейнере.
