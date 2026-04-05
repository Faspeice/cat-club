# КэтКлуб (pure HTML/CSS/JS + PHP + PostgreSQL)

Учебный проект «Клуб любителей кошек»: лента, питомцы, форум с комментариями, события с календарём.

## Требования
- PHP 8.1+ (желательно 8.2+)
- PostgreSQL 13+
- nginx + php-fpm (или другой сервер, но пример ниже для nginx)

## Структура
- `public/` — web root (сюда должен смотреть nginx)
- `app/` — PHP код (роутер/модели/шаблоны)
- `app/db/schema.sql` — схема БД
- `app/db/seed.sql` — тестовые данные
- `nginx/example.conf` — пример конфига nginx (ЧПУ + 301 www)

## Настройка БД
1. Создайте БД:

```sql
CREATE DATABASE cat_club;
```

2. Примените схему и сиды:

```bash
psql -d cat_club -f app/db/schema.sql
psql -d cat_club -f app/db/seed.sql
```

## Конфиг приложения
Скопируйте `app/config/config.example.php` в `app/config/config.php` и заполните креды:

- `app/config/config.php` **не коммитится** (в `.gitignore`).

## Запуск
### nginx + php-fpm
- Укажите `root` на папку `public/` (см. `nginx/example.conf`).
- Включите `try_files $uri $uri/ /index.php?$query_string;` для ЧПУ.

### Docker (рекомендуется для быстрого старта)
1. Запуск:

```bash
docker compose up --build
```

2. Открыть сайт:
- `http://localhost:8080`

PostgreSQL поднимется внутри Docker и автоматически применит:
- `app/db/schema.sql`
- `app/db/seed.sql`

Если нужно сбросить БД:

```bash
docker compose down -v
docker compose up --build
```

### Проверка страниц
- `/` — лента
- `/pets` — питомцы (фильтр/пагинация, добавление после логина)
- `/pets/{id}` — карточка питомца
- `/forum` — темы (пагинация)
- `/forum/{id}` — тема с постами и комментариями
- `/events` — события + календарь
- `/robots.txt`, `/sitemap.xml`

## Безопасность/валидация
- SQL-инъекции: только подготовленные запросы (PDO prepared statements)
- XSS: весь вывод экранируется в шаблонах
- CSRF: все POST-формы используют токен

