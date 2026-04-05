<?php

declare(strict_types=1);

require __DIR__ . '/../app/http/Router.php';
require __DIR__ . '/../app/http/Response.php';
require __DIR__ . '/../app/http/Input.php';
require __DIR__ . '/../app/http/Csrf.php';
require __DIR__ . '/../app/views/helpers.php';
require __DIR__ . '/../app/db/Db.php';
require __DIR__ . '/../app/models/UserModel.php';
require __DIR__ . '/../app/models/PetModel.php';
require __DIR__ . '/../app/models/TopicModel.php';
require __DIR__ . '/../app/models/PostModel.php';
require __DIR__ . '/../app/models/CommentModel.php';
require __DIR__ . '/../app/models/EventModel.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$configPath = __DIR__ . '/../app/config/config.php';
$config = file_exists($configPath) ? require $configPath : [];

function wantsJson(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return is_string($accept) && str_contains($accept, 'application/json');
}

function safeDb(array $config): ?PDO
{
    try {
        return Db::pdo($config);
    } catch (Throwable $e) {
        return null;
    }
}

function authFail(string $mode, string $message, int $status = 422): never
{
    if (wantsJson()) {
        Response::json(['ok' => false, 'error' => $message], $status);
    }
    render(
        __DIR__ . '/../app/views/pages/login.php',
        [
            'mode' => $mode,
            'error' => $message,
            'success' => '',
        ],
        [
            'title' => $mode === 'register' ? 'КэтКлуб — Регистрация' : 'КэтКлуб — Вход',
            'description' => $mode === 'register' ? 'Регистрация владельца питомца в КэтКлуб.' : 'Вход в КэтКлуб.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
            'layout' => 'auth',
            'breadcrumbs' => [],
        ],
        0,
        $status
    );
}

function currentUser(): array
{
    if (!empty($_SESSION['user']) && is_array($_SESSION['user'])) {
        return array_merge(['is_auth' => true], $_SESSION['user']);
    }
    return ['is_auth' => false];
}

/**
 * @param array{title:string,description:string,og?:array<string,mixed>,schema?:array<string,mixed>,breadcrumbs?:array<int,array{label:string,url:string}>} $meta
 */
function render(string $viewPath, array $data, array $meta = [], int $lastModifiedTs = 0, int $status = 200): never
{
    if (!Response::lastModified($lastModifiedTs)) {
        exit;
    }
    Response::status($status);
    $user = currentUser();

    // Make $user available in page templates too
    $data['user'] = $user;
    extract($data, EXTR_SKIP);
    ob_start();
    require $viewPath;
    $content = (string)ob_get_clean();

    $title = $meta['title'] ?? 'КэтКлуб';
    $description = $meta['description'] ?? 'Клуб любителей кошек.';
    $og = $meta['og'] ?? [];
    $schema = $meta['schema'] ?? [];
    $breadcrumbs = $meta['breadcrumbs'] ?? [];
    $layout = $meta['layout'] ?? 'default';

    require __DIR__ . '/../app/views/layout.php';
    exit;
}

function siteSchemaHome(): array
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url = $scheme . '://' . $host . '/';
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'КэтКлуб',
        'url' => $url,
    ];
}

function notFound(): never
{
    render(
        __DIR__ . '/../app/views/pages/404.php',
        [],
        [
            'title' => 'КэтКлуб — Страница не найдена',
            'description' => 'Запрошенная страница не найдена.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
        ],
        0,
        404
    );
}

function forbidden(): never
{
    render(
        __DIR__ . '/../app/views/pages/403.php',
        [],
        [
            'title' => 'КэтКлуб — Доступ запрещён',
            'description' => 'Доступ к странице запрещён.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
        ],
        0,
        403
    );
}

function safeInternalRedirect(string $path): string
{
    $path = trim($path);
    if ($path === '' || mb_strlen($path) > 500) {
        return '/';
    }
    if (!str_starts_with($path, '/') || str_starts_with($path, '//')) {
        return '/';
    }

    return $path;
}

function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        Response::redirect('/login', 302);
    }
}

$parsed = Router::parsePath();
$path = $parsed['path'];
$segments = $parsed['segments'];
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// API
if (str_starts_with($path, '/api/')) {
    if (strtoupper($method) !== 'POST') {
        Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
    }
    if (!Csrf::validate(Input::str($_POST, 'csrf_token', 200))) {
        if (wantsJson()) {
            Response::json(['ok' => false, 'error' => 'CSRF'], 403);
        }
        forbidden();
    }

    $pdo = safeDb($config);
    if (!$pdo) {
        if (wantsJson()) {
            Response::json(['ok' => false, 'error' => 'Service unavailable'], 503);
        }
        Response::status(503);
        Response::contentType('text/plain; charset=utf-8');
        echo 'Сервис временно недоступен.';
        exit;
    }

    if ($path === '/api/auth/register') {
        $nick = Input::str($_POST, 'nick', 30);
        $contact = Input::str($_POST, 'contact', 120);
        $pass = Input::str($_POST, 'password', 200);
        $pass2 = Input::str($_POST, 'password_confirm', 200);

        if (mb_strlen($nick) < 3) authFail('register', 'Ник слишком короткий', 422);
        if (mb_strlen($contact) < 3) authFail('register', 'Контакты обязательны', 422);
        if (mb_strlen($pass) < 6) authFail('register', 'Пароль минимум 6 символов', 422);
        if ($pass !== $pass2) authFail('register', 'Пароли не совпадают', 422);

        if (UserModel::findByNick($pdo, $nick)) {
            authFail('register', 'Ник уже занят', 409);
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $uid = UserModel::create($pdo, $nick, $contact, $hash);

        $_SESSION['user'] = ['id' => $uid, 'nick' => $nick, 'contact' => $contact];
        session_regenerate_id(true);

        if (wantsJson()) {
            Response::json(['ok' => true, 'user' => ['id' => $uid, 'nick' => $nick, 'contact' => $contact]]);
        }
        Response::redirect('/', 302);
    }

    if ($path === '/api/auth/login') {
        $nick = Input::str($_POST, 'nick', 30);
        $pass = Input::str($_POST, 'password', 200);
        $u = UserModel::findByNick($pdo, $nick);
        if (!$u || empty($u['password_hash']) || !password_verify($pass, (string)$u['password_hash'])) {
            authFail('login', 'Неверный ник или пароль', 401);
        }

        $_SESSION['user'] = ['id' => (int)$u['id'], 'nick' => (string)$u['nick'], 'contact' => (string)$u['contact']];
        session_regenerate_id(true);
        if (wantsJson()) {
            Response::json(['ok' => true, 'user' => ['id' => (int)$u['id'], 'nick' => (string)$u['nick']]]);
        }
        Response::redirect('/', 302);
    }

    if ($path === '/api/pets/create') {
        requireAuth();
        $name = Input::str($_POST, 'name', 80);
        $breed = Input::str($_POST, 'breed', 120);
        $age = Input::int($_POST, 'age', 0);
        $photo = Input::url($_POST, 'photo_url', 2000);
        $story = Input::str($_POST, 'story', 5000);
        $morePhotos = Input::str($_POST, 'more_photos', 12000);

        if (mb_strlen($name) < 1) authFail('login', 'Кличка обязательна', 422);
        if (mb_strlen($breed) < 1) authFail('login', 'Порода обязательна', 422);
        if ($age < 0 || $age > 50) authFail('login', 'Возраст должен быть от 0 до 50', 422);

        $uid = (int)($_SESSION['user']['id'] ?? 0);
        $pid = PetModel::create($pdo, $uid, $name, $breed, $age, $photo, $story);
        foreach (preg_split('/\r\n|\r|\n/', $morePhotos) as $line) {
            $line = trim($line);
            if ($line === '' || mb_strlen($line) > 2000) {
                continue;
            }
            if (!filter_var($line, FILTER_VALIDATE_URL)) {
                continue;
            }
            PetModel::addPhoto($pdo, $pid, $line);
        }
        if (wantsJson()) {
            Response::json(['ok' => true, 'id' => $pid], 201);
        }
        Response::redirect('/pets/' . $pid, 302);
    }

    if ($path === '/api/posts/create') {
        requireAuth();
        $title = Input::str($_POST, 'title', 200);
        $body = Input::str($_POST, 'body', 8000);
        $photo = Input::url($_POST, 'photo_url', 2000);
        $topicTitle = Input::str($_POST, 'topic_title', 120);
        $redirect = safeInternalRedirect(Input::str($_POST, 'redirect', 500));

        if (mb_strlen($title) < 3) {
            if (wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Слишком короткий заголовок'], 422);
            }
            Response::redirect($redirect, 302);
        }
        if (mb_strlen($body) < 1) {
            if (wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Текст поста обязателен'], 422);
            }
            Response::redirect($redirect, 302);
        }

        $topicId = null;
        if ($topicTitle !== '') {
            $st = $pdo->prepare('SELECT id FROM topics WHERE title = :t LIMIT 1');
            $st->execute([':t' => $topicTitle]);
            $tid = $st->fetchColumn();
            if ($tid !== false) {
                $topicId = (int)$tid;
            }
        }

        $uid = (int)($_SESSION['user']['id'] ?? 0);
        PostModel::create($pdo, $uid, $topicId, $title, $body, $photo);
        if (wantsJson()) {
            Response::json(['ok' => true], 201);
        }
        Response::redirect($redirect, 302);
    }

    // Forum endpoints removed

    if ($path === '/api/events/create') {
        requireAuth();
        $title = Input::str($_POST, 'title', 140);
        $body = Input::str($_POST, 'body', 5000);
        $place = Input::str($_POST, 'place', 200);
        $startsAt = Input::str($_POST, 'starts_at', 40);
        $endsAt = Input::str($_POST, 'ends_at', 40);
        $endsAt = $endsAt !== '' ? $endsAt : null;

        if (mb_strlen($title) < 3) authFail('login', 'Название события слишком короткое', 422);
        if ($startsAt === '') authFail('login', 'Дата/время начала обязательны', 422);

        $id = EventModel::create($pdo, $title, $body, $place, $startsAt, $endsAt);
        if (wantsJson()) {
            Response::json(['ok' => true, 'id' => $id], 201);
        }
        Response::redirect('/events', 302);
    }

    if ($path === '/api/posts/toggle-like') {
        requireAuth();
        $postId = Input::int($_POST, 'post_id', 0);
        $redirect = safeInternalRedirect(Input::str($_POST, 'redirect', 500));
        if ($postId < 1 || !PostModel::exists($pdo, $postId)) {
            if (wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Пост не найден'], 404);
            }
            Response::redirect($redirect, 302);
        }
        PostModel::toggleLike($pdo, $postId, (int)$_SESSION['user']['id']);
        if (wantsJson()) {
            $uid = (int)$_SESSION['user']['id'];
            $data = PostModel::feedLikeData($pdo, [$postId], $uid);

            Response::json([
                'ok' => true,
                'post_id' => $postId,
                'like_count' => $data['counts'][$postId] ?? 0,
                'liked' => $data['liked'][$postId] ?? false,
            ]);
        }
        Response::redirect($redirect, 302);
    }

    if ($path === '/api/posts/comment') {
        requireAuth();
        $postId = Input::int($_POST, 'post_id', 0);
        $body = Input::str($_POST, 'body', 2000);
        $redirect = safeInternalRedirect(Input::str($_POST, 'redirect', 500));
        if ($postId < 1 || !PostModel::exists($pdo, $postId)) {
            if (wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Пост не найден'], 404);
            }
            Response::redirect($redirect, 302);
        }
        if (mb_strlen($body) < 1) {
            if (wantsJson()) {
                Response::json(['ok' => false, 'error' => 'Введите текст комментария'], 422);
            }
            Response::redirect($redirect, 302);
        }
        CommentModel::create($pdo, $postId, (int)$_SESSION['user']['id'], $body);
        if (wantsJson()) {
            Response::json(['ok' => true], 201);
        }
        Response::redirect($redirect, 302);
    }

    Response::json(['ok' => false, 'error' => 'Unknown API route'], 404);
}

// Static like endpoints
if ($path === '/robots.txt') {
    $robotsFile = __DIR__ . '/robots.txt';
    $lm = file_exists($robotsFile) ? (int)filemtime($robotsFile) : (time() - 60);
    if (!Response::lastModified($lm)) {
        exit;
    }
    Response::contentType('text/plain; charset=utf-8');
    echo "User-agent: *\n";
    echo "Disallow: /api/\n";
    echo "Disallow: /app/\n";
    echo "Disallow: /nginx/\n";
    echo "Sitemap: " . ((isset($_SERVER['HTTP_HOST']) ? (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']) : '') . "/sitemap.xml") . "\n";
    exit;
}

if ($path === '/sitemap.xml') {
    $pdoLm = safeDb($config);
    $lm = time() - 60;
    if ($pdoLm) {
        $lm = max(PetModel::lastUpdated($pdoLm), TopicModel::lastUpdated($pdoLm), PostModel::lastUpdated($pdoLm), EventModel::lastUpdated($pdoLm));
    }
    if (!Response::lastModified($lm)) {
        exit;
    }
    Response::contentType('application/xml; charset=utf-8');
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = $scheme . '://' . $host;
    $urls = [
        ['loc' => $base . '/', 'lastmod' => null],
        ['loc' => $base . '/pets', 'lastmod' => null],
        ['loc' => $base . '/events', 'lastmod' => null],
        ['loc' => $base . '/login', 'lastmod' => null],
        ['loc' => $base . '/register', 'lastmod' => null],
    ];

    $pdo = safeDb($config);
    if ($pdo) {
        $pets = $pdo->query('SELECT id, updated_at FROM pets ORDER BY id ASC')->fetchAll();
        foreach ($pets as $p) {
            $urls[] = [
                'loc' => $base . '/pets/' . (int)$p['id'],
                'lastmod' => (string)$p['updated_at'],
            ];
        }

    }
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
    foreach ($urls as $u) {
        $loc = (string)($u['loc'] ?? '');
        $lm = (string)($u['lastmod'] ?? '');
        echo "  <url><loc>" . htmlspecialchars($loc, ENT_QUOTES) . "</loc>";
        if ($lm !== '') {
            $d = date(DATE_W3C, strtotime($lm) ?: time());
            echo "<lastmod>" . htmlspecialchars($d, ENT_QUOTES) . "</lastmod>";
        }
        echo "</url>\n";
    }
    echo "</urlset>\n";
    exit;
}

// Auth routes
if ($path === '/logout' && strtoupper($method) === 'POST') {
    if (!Csrf::validate(Input::str($_POST, 'csrf_token', 200))) {
        forbidden();
    }
    $_SESSION = [];
    session_regenerate_id(true);
    Response::redirect('/', 302);
}

if ($path === '/login') {
    render(
        __DIR__ . '/../app/views/pages/login.php',
        [
            'mode' => 'login',
            'error' => '',
            'success' => Input::str($_GET, 'registered', 50) ? 'Регистрация завершена. Теперь войдите.' : '',
        ],
        [
            'title' => 'КэтКлуб — Вход',
            'description' => 'Вход в КэтКлуб: лента, питомцы, события.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
            'layout' => 'auth',
            'breadcrumbs' => [
                ['label' => 'Главная', 'url' => '/'],
                ['label' => 'Вход', 'url' => '/login'],
            ],
        ],
        0
    );
}

if ($path === '/register') {
    render(
        __DIR__ . '/../app/views/pages/login.php',
        [
            'mode' => 'register',
            'error' => '',
            'success' => '',
        ],
        [
            'title' => 'КэтКлуб — Регистрация',
            'description' => 'Регистрация владельца питомца в КэтКлуб.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
            'layout' => 'auth',
            'breadcrumbs' => [
                ['label' => 'Главная', 'url' => '/'],
                ['label' => 'Регистрация', 'url' => '/register'],
            ],
        ],
        0
    );
}

// Pages
if ($path === '/') {
    $pdo = safeDb($config);
    $selectedTopic = Input::str($_GET, 'topic', 120);
    $selectedAuthor = Input::str($_GET, 'author', 30);
    $sidebarPerPage = 6;
    $topicPage = max(1, Input::int($_GET, 'tp', 1));
    $memberPage = max(1, Input::int($_GET, 'mp', 1));

    $posts = [];
    $topics = [];
    $members = [];
    $topicOptions = [];
    $topicsPages = 1;
    $membersPages = 1;

    $lastMod = time() - 60;
    $feedRedirect = currentFeedRedirect();
    $sessionUser = currentUser();
    $userId = !empty($sessionUser['is_auth']) ? (int)($sessionUser['id'] ?? 0) : 0;
    $likeUserId = $userId > 0 ? $userId : null;

    if ($pdo) {
        $tList = TopicModel::list($pdo, $sidebarPerPage, ($topicPage - 1) * $sidebarPerPage);
        $topicsTotal = $tList['total'];
        $topicsPages = (int)max(1, (int)ceil($topicsTotal / $sidebarPerPage));
        if ($topicPage > $topicsPages) {
            $topicPage = $topicsPages;
            $tList = TopicModel::list($pdo, $sidebarPerPage, ($topicPage - 1) * $sidebarPerPage);
        }
        $topics = array_map(static fn(array $r): string => (string)$r['title'], $tList['items']);
        $topicOptions = TopicModel::listTitles($pdo, 100);

        $mList = UserModel::listNicksPage($pdo, $sidebarPerPage, ($memberPage - 1) * $sidebarPerPage);
        $membersTotal = $mList['total'];
        $membersPages = (int)max(1, (int)ceil($membersTotal / $sidebarPerPage));
        if ($memberPage > $membersPages) {
            $memberPage = $membersPages;
            $mList = UserModel::listNicksPage($pdo, $sidebarPerPage, ($memberPage - 1) * $sidebarPerPage);
        }
        $members = $mList['items'];

        $res = PostModel::listFeed(
            $pdo,
            $selectedTopic !== '' ? $selectedTopic : null,
            $selectedAuthor !== '' ? $selectedAuthor : null,
            10,
            0
        );
        $items = $res['items'];
        $postIds = array_map(static fn(array $p): int => (int)($p['id'] ?? 0), $items);
        $likeData = PostModel::feedLikeData($pdo, $postIds, $likeUserId);
        $commentsByPost = CommentModel::listGroupedForPosts($pdo, $postIds);

        $posts = array_map(static function (array $p) use ($likeData, $commentsByPost): array {
            $id = (int)($p['id'] ?? 0);

            return [
                'id' => $id,
                'user' => (string)($p['nick'] ?? ''),
                'date' => (string)($p['created_at'] ?? ''),
                'tag' => (string)($p['topic_title'] ?? 'Без темы'),
                'title' => (string)($p['title'] ?? ''),
                'image' => (string)($p['photo_url'] ?? ''),
                'image_alt' => (string)($p['title'] ?? 'Фото'),
                'text' => (string)($p['body'] ?? ''),
                'like_count' => $likeData['counts'][$id] ?? 0,
                'liked' => (bool)($likeData['liked'][$id] ?? false),
                'comments' => $commentsByPost[$id] ?? [],
            ];
        }, $items);
        $lastMod = max(PostModel::lastUpdated($pdo), TopicModel::lastUpdated($pdo), EventModel::lastUpdated($pdo));
    } else {
        $topicOptions = ['Питание', 'Юмор'];
        $topicsAll = $topicOptions;
        $membersAll = ['Пользователь 1', 'Пользователь 2', 'Пользователь 3', 'Пользователь 4'];
        $topicsTotal = count($topicsAll);
        $membersTotal = count($membersAll);
        $topicsPages = (int)max(1, (int)ceil($topicsTotal / $sidebarPerPage));
        $membersPages = (int)max(1, (int)ceil($membersTotal / $sidebarPerPage));
        $topicPage = min($topicPage, $topicsPages);
        $memberPage = min($memberPage, $membersPages);
        $topics = array_slice($topicsAll, ($topicPage - 1) * $sidebarPerPage, $sidebarPerPage);
        $members = array_slice($membersAll, ($memberPage - 1) * $sidebarPerPage, $sidebarPerPage);
    }
    render(
        __DIR__ . '/../app/views/pages/feed.php',
        [
            'posts' => $posts,
            'topics' => $topics,
            'members' => $members,
            'topicOptions' => $topicOptions,
            'selectedTopic' => $selectedTopic,
            'selectedAuthor' => $selectedAuthor,
            'topicPage' => $topicPage,
            'topicsPages' => $topicsPages,
            'memberPage' => $memberPage,
            'membersPages' => $membersPages,
            'feedRedirect' => $feedRedirect,
            'dbConnected' => $pdo !== null,
        ],
        [
            'title' => 'КэтКлуб — Лента активностей',
            'description' => 'Лента активностей клуба любителей кошек: новости, истории питомцев, обсуждения.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
        ],
        $lastMod
    );
}

if ($path === '/pets') {
    $pdo = safeDb($config);
    $selectedBreed = Input::str($_GET, 'breed', 100);
    $page = max(1, Input::int($_GET, 'page', 1));
    $limit = 12;
    $offset = ($page - 1) * $limit;
    $pets = [];
    $breeds = ['Сиамская кошка', 'Британская короткошерстная', 'Мейн-кун', 'Сфинкс'];
    $pages = 1;
    $lastMod = time() - 60;
    if ($pdo) {
        $breeds = PetModel::listBreeds($pdo);
        $list = PetModel::list($pdo, $selectedBreed, $limit, $offset);
        $pets = $list['items'];
        $pages = (int)max(1, (int)ceil($list['total'] / $limit));
        $lastMod = PetModel::lastUpdated($pdo);
    }
    render(
        __DIR__ . '/../app/views/pages/pets.php',
        [
            'pets' => $pets,
            'breeds' => $breeds,
            'selectedBreed' => $selectedBreed,
            'page' => $page,
            'pages' => $pages,
        ],
        [
            'title' => 'КэтКлуб — Галерея питомцев',
            'description' => 'Галерея всех питомцев клуба с фильтрацией по породе.',
            'og' => ['type' => 'website'],
            'schema' => siteSchemaHome(),
            'breadcrumbs' => [
                ['label' => 'Главная', 'url' => '/'],
                ['label' => 'Питомцы', 'url' => '/pets'],
            ],
        ],
        $lastMod
    );
}

if (count($segments) === 2 && $segments[0] === 'pets' && ctype_digit($segments[1])) {
    $petId = (int)$segments[1];
    $pdo = safeDb($config);
    $pet = null;
    $lastMod = time() - 60;
    if ($pdo) {
        $pet = PetModel::find($pdo, $petId);
        if (!$pet) {
            notFound();
        }
        $lastMod = PetModel::lastUpdated($pdo, $petId);
    } else {
        $pet = [
            'id' => $petId,
            'name' => 'Сэр Николас V',
            'age' => 9,
            'breed' => 'Сиамская кошка',
            'photo_url' => 'https://images.unsplash.com/photo-1513245543132-31f507417b26?ixlib=rb-4.0.3&auto=format&fit=crop&w=900&q=80',
            'story' => 'Сэр Николас V, Барон фон ободранных обоев. Его Светлость происходит из древнего рода...',
            'photos' => [],
        ];
    }
    render(
        __DIR__ . '/../app/views/pages/pet.php',
        [
            'pet' => $pet,
        ],
        [
            'title' => 'КэтКлуб — Питомец',
            'description' => 'Профиль питомца: история и галерея.',
            'og' => ['type' => 'profile'],
            'schema' => siteSchemaHome(),
            'breadcrumbs' => [
                ['label' => 'Главная', 'url' => '/'],
                ['label' => 'Питомцы', 'url' => '/pets'],
                ['label' => 'Карточка питомца', 'url' => '/pets/' . $petId],
            ],
        ],
        $lastMod
    );
}

if ($path === '/events') {
    $pdo = safeDb($config);
    $events = [];
    $lastMod = time() - 60;
    if ($pdo) {
        $events = EventModel::listUpcoming($pdo, 50);
        $lastMod = EventModel::lastUpdated($pdo);
    }
    render(
        __DIR__ . '/../app/views/pages/events.php',
        [
            'events' => $events,
        ],
        [
            'title' => 'КэтКлуб — События',
            'description' => 'Анонсы встреч и конкурсов клуба любителей кошек.',
            'og' => ['type' => 'website'],
            'schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'ItemList',
                'name' => 'События КэтКлуба',
                'itemListElement' => array_values(array_map(static function (array $ev, int $i): array {
                    return [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'item' => [
                            '@type' => 'Event',
                            'name' => (string)($ev['title'] ?? ''),
                            'description' => (string)($ev['body'] ?? ''),
                            'startDate' => (string)($ev['starts_at'] ?? ''),
                            'endDate' => (string)($ev['ends_at'] ?? ''),
                            'location' => [
                                '@type' => 'Place',
                                'name' => (string)($ev['place'] ?? ''),
                            ],
                        ],
                    ];
                }, $events, array_keys($events))),
            ],
            'breadcrumbs' => [
                ['label' => 'Главная', 'url' => '/'],
                ['label' => 'События', 'url' => '/events'],
            ],
        ],
        $lastMod
    );
}

// Forum and profile pages removed

if ($path === '/403') {
    forbidden();
}
if ($path === '/404') {
    notFound();
}

notFound();

