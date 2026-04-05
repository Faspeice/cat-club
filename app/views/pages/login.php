<?php
/** @var string $mode */
/** @var string $error */
/** @var string $success */
declare(strict_types=1);

$mode = $mode === 'register' ? 'register' : 'login';
$showLogin = $mode === 'login';
?>

<div class="auth-wrap" style="background:#e3dee3;min-height:100vh;display:flex;align-items:center;justify-content:center">
    <div class="auth-card" style="max-width:420px;width:100%;padding:50px 40px">
        <h1 class="auth-title" style="margin-bottom:35px">Добро пожаловать!</h1>

        <?php if ($error !== ''): ?>
            <div class="error-box" role="alert"><?= e($error) ?></div>
        <?php endif; ?>
        <?php if ($success !== ''): ?>
            <div class="success-box" role="status"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($showLogin): ?>
            <form action="/api/auth/login" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <div class="form-group">
                    <label class="form-label" for="login-nick">Логин</label>
                    <input class="form-input" type="text" id="login-nick" name="nick" placeholder="Введите логин" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="login-password">Пароль</label>
                    <div class="password-container">
                        <input class="form-input" type="password" id="login-password" name="password" placeholder="Введите пароль" required>
                        <button type="button" class="password-toggle" data-toggle-password="login-password" title="Показать/скрыть пароль">👁</button>
                    </div>
                </div>
                <button type="submit" class="auth-button" title="Войти">Вход</button>
            </form>
            <div class="divider"></div>
            <div class="auth-switch">
                Регистрация <a href="/register" title="Перейти к регистрации">→</a>
            </div>
        <?php else: ?>
            <form action="/api/auth/register" method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?= e(Csrf::token()) ?>">
                <div class="form-group">
                    <label class="form-label" for="reg-nick">Логин</label>
                    <input class="form-input" type="text" id="reg-nick" name="nick" placeholder="Придумайте логин" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-contact">Контакты</label>
                    <input class="form-input" type="text" id="reg-contact" name="contact" placeholder="Email или телефон" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-password">Пароль</label>
                    <div class="password-container">
                        <input class="form-input" type="password" id="reg-password" name="password" placeholder="Придумайте пароль" required>
                        <button type="button" class="password-toggle" data-toggle-password="reg-password" title="Показать/скрыть пароль">👁</button>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="reg-confirm">Повторите пароль</label>
                    <div class="password-container">
                        <input class="form-input" type="password" id="reg-confirm" name="password_confirm" placeholder="Повторите пароль" required>
                        <button type="button" class="password-toggle" data-toggle-password="reg-confirm" title="Показать/скрыть пароль">👁</button>
                    </div>
                </div>
                <button type="submit" class="auth-button" title="Зарегистрироваться">Регистрация</button>
            </form>
            <div class="divider"></div>
            <div class="auth-switch">
                Вход <a href="/login" title="Перейти ко входу">→</a>
            </div>
        <?php endif; ?>
    </div>
</div>

