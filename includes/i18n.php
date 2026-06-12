<?php

/**
 * i18n.php — tiny framework-free internationalisation layer.
 *
 * Supported languages live in includes/lang/<code>.php, each returning an
 * associative array of translation keys. The active language is resolved from
 * (in order): a ?lang= query param, the session, a persistent cookie, then the
 * default. Switching language is just a link to any page with ?lang=ro|en.
 */

require_once __DIR__ . '/auth.php';

const I18N_DEFAULT_LANG = 'en';
const I18N_SUPPORTED    = ['en', 'ro'];

/**
 * Resolve and persist the active language. Call once per request (cheap to
 * call again — it is idempotent).
 */
function i18nInit(): void
{
    // Resolve language at most once per request — repeated calls (via lang()/t())
    // must not re-issue setcookie() after output has begun.
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;

    startSessionIfNeeded();

    // An explicit ?lang= switch wins and is remembered.
    if (isset($_GET['lang']) && in_array($_GET['lang'], I18N_SUPPORTED, true)) {
        $_SESSION['lang'] = $_GET['lang'];
        setcookie('lang', $_GET['lang'], time() + 60 * 60 * 24 * 365, '/');
    } elseif (!isset($_SESSION['lang'])) {
        // Fall back to a previously stored cookie on a fresh session.
        if (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], I18N_SUPPORTED, true)) {
            $_SESSION['lang'] = $_COOKIE['lang'];
        } else {
            $_SESSION['lang'] = I18N_DEFAULT_LANG;
        }
    }
}

/** Current language code, e.g. "en" or "ro". */
function lang(): string
{
    i18nInit();
    return $_SESSION['lang'] ?? I18N_DEFAULT_LANG;
}

/** The "other" language code — handy for a two-way toggle button. */
function otherLang(): string
{
    return lang() === 'ro' ? 'en' : 'ro';
}

/**
 * Translate a key for the active language, falling back to English and then to
 * the raw key. Optional sprintf-style args are applied when provided.
 */
function t(string $key, ...$args): string
{
    static $cache = [];

    $current = lang();
    if (!isset($cache[$current])) {
        $path = __DIR__ . '/lang/' . $current . '.php';
        $cache[$current] = is_file($path) ? require $path : [];
    }
    if (!isset($cache['en'])) {
        $cache['en'] = require __DIR__ . '/lang/en.php';
    }

    $value = $cache[$current][$key] ?? $cache['en'][$key] ?? $key;

    if ($args) {
        return vsprintf($value, $args);
    }
    return $value;
}

/**
 * Build a URL to the current page that switches to $targetLang, preserving any
 * existing query parameters. Used by the header toggle button.
 */
function langSwitchUrl(string $targetLang): string
{
    $script = basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    $params = $_GET;
    $params['lang'] = $targetLang;
    return $script . '?' . http_build_query($params);
}

/**
 * Pick the localized description for a hero row, falling back to English when
 * the Romanian column is missing or empty.
 */
function heroDescription(array $hero): string
{
    if (lang() === 'ro' && !empty($hero['description_ro'])) {
        return $hero['description_ro'];
    }
    return $hero['description'] ?? '';
}

/**
 * Initialise language as soon as this file is included — before any page output
 * — so the persistence cookie can be set without a "headers already sent"
 * warning. Pages must require this file at the top, before emitting markup.
 */
i18nInit();

/**
 * Returns the subset of UI strings the front-end JavaScript needs, ready to be
 * JSON-encoded into the page as window.__I18N__.
 */
function jsTranslations(): array
{
    $keys = [
        'js.loading', 'js.lockedIn', 'js.timeUp', 'js.couldNotLoad', 'js.scoring',
        'js.retrySubmit', 'js.saveError', 'js.saved', 'js.notSavedGuest', 'js.next',
        'js.startQuiz', 'js.ready', 'js.selectToBegin', 'js.pickDifficulty', 'js.noAnswer', 'js.youChose',
        'js.correctIs', 'js.yourAnswer', 'js.review', 'js.quizComplete', 'js.perfectRun',
        'js.outstanding', 'js.solidEffort', 'js.keepStudying', 'js.perfectTitle',
        'js.expertTitle', 'js.solidTitle', 'js.studyTitle', 'js.points', 'js.pts',
    ];
    $out = [];
    foreach ($keys as $k) {
        $out[$k] = t($k);
    }
    return $out;
}
