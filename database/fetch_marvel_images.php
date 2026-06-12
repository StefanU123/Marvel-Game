<?php

/**
 * fetch_marvel_images.php — downloads real hero portraits from the official
 * Marvel Comics API and stores them locally, then points each hero's image_url
 * at the saved file. This keeps the app fully offline at runtime: the network
 * is only touched once, when you run this script.
 *
 * Get a free key pair at https://developer.marvel.com (a PUBLIC and a PRIVATE
 * key). Marvel authenticates each call with ts + md5(ts + privateKey + publicKey).
 *
 * Usage (PowerShell):
 *   $env:MARVEL_PUBLIC_KEY="xxxx"; $env:MARVEL_PRIVATE_KEY="yyyy"; php database/fetch_marvel_images.php
 *
 * Usage (bash):
 *   MARVEL_PUBLIC_KEY=xxxx MARVEL_PRIVATE_KEY=yyyy php database/fetch_marvel_images.php
 *
 * Or pass them as arguments:
 *   php database/fetch_marvel_images.php <publicKey> <privateKey> [--all]
 *
 * By default only heroes that still use a generated .svg placeholder are
 * fetched, so the existing real photos are left untouched. Pass --all to
 * refresh every hero.
 */

require_once __DIR__ . '/../includes/db.php';

$publicKey  = $argv[1] ?? getenv('MARVEL_PUBLIC_KEY') ?: '';
$privateKey = $argv[2] ?? getenv('MARVEL_PRIVATE_KEY') ?: '';
$fetchAll   = in_array('--all', $argv, true);

if ($publicKey === '' || $privateKey === '') {
    fwrite(STDERR, "Missing Marvel API keys.\n");
    fwrite(STDERR, "Set MARVEL_PUBLIC_KEY and MARVEL_PRIVATE_KEY env vars, or pass them as arguments.\n");
    fwrite(STDERR, "Get a free key pair at https://developer.marvel.com\n");
    exit(1);
}

$imagesDir = __DIR__ . '/../public/assets/images/';

/**
 * Call a Marvel API endpoint and return the decoded JSON, or null on failure.
 */
function marvelGet(string $path, array $query, string $publicKey, string $privateKey): ?array
{
    $ts = (string) time();
    $query['ts']     = $ts;
    $query['apikey'] = $publicKey;
    $query['hash']   = md5($ts . $privateKey . $publicKey);

    $url = 'https://gateway.marvel.com/v1/public/' . $path . '?' . http_build_query($query);

    $body = httpGet($url);
    if ($body === null) {
        return null;
    }
    $data = json_decode($body, true);
    return is_array($data) ? $data : null;
}

/**
 * Minimal HTTP GET that prefers cURL and falls back to the stream wrapper.
 */
function httpGet(string $url): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_USERAGENT      => 'Marvel-Game/1.0',
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body !== false && $code >= 200 && $code < 300) {
            return $body;
        }
        return null;
    }

    $ctx = stream_context_create(['http' => ['timeout' => 30, 'user_agent' => 'Marvel-Game/1.0']]);
    $body = @file_get_contents($url, false, $ctx);
    return $body === false ? null : $body;
}

/** Turn a hero name into a safe image filename slug. */
function slugify(string $name): string
{
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

try {
    $pdo = getDatabaseConnection();
    $heroes = $pdo->query('SELECT id, name, image_url FROM heroes ORDER BY id')->fetchAll();
} catch (Throwable $e) {
    fwrite(STDERR, "Could not read heroes from the database. Run database/setup.php first.\n");
    exit(1);
}

$update = $pdo->prepare('UPDATE heroes SET image_url = :url WHERE id = :id');
$updated = 0;
$skipped = 0;

foreach ($heroes as $hero) {
    $usesPlaceholder = str_ends_with(strtolower($hero['image_url']), '.svg');
    if (!$fetchAll && !$usesPlaceholder) {
        echo "• {$hero['name']}: keeping existing image ({$hero['image_url']})\n";
        continue;
    }

    echo "• {$hero['name']}: searching Marvel API… ";

    $result = marvelGet('characters', ['nameStartsWith' => $hero['name'], 'limit' => 5], $publicKey, $privateKey);
    $chars = $result['data']['results'] ?? [];

    if (!$chars) {
        echo "no match, skipped.\n";
        $skipped++;
        continue;
    }

    // Prefer an exact (case-insensitive) name match; otherwise take the first.
    $chosen = $chars[0];
    foreach ($chars as $c) {
        if (strcasecmp($c['name'] ?? '', $hero['name']) === 0) {
            $chosen = $c;
            break;
        }
    }

    $thumb = $chosen['thumbnail'] ?? null;
    if (!$thumb || !isset($thumb['path'], $thumb['extension'])
        || str_contains($thumb['path'], 'image_not_available')) {
        echo "no usable image, kept placeholder.\n";
        $skipped++;
        continue;
    }

    // Use a "portrait_uncanny" crop for a tall hero-card friendly image.
    $imageUrl = $thumb['path'] . '/portrait_uncanny.' . $thumb['extension'];
    // Marvel serves http by default; force https.
    $imageUrl = preg_replace('#^http://#', 'https://', $imageUrl);

    $binary = httpGet($imageUrl);
    if ($binary === null || strlen($binary) < 1000) {
        echo "download failed, skipped.\n";
        $skipped++;
        continue;
    }

    $filename = slugify($hero['name']) . '.' . $thumb['extension'];
    file_put_contents($imagesDir . $filename, $binary);

    $relative = 'assets/images/' . $filename;
    $update->execute([':url' => $relative, ':id' => $hero['id']]);
    $updated++;

    echo "saved {$relative}\n";

    // Be polite to the API between calls.
    usleep(250000);
}

echo "\nDone. Updated {$updated} hero image(s), skipped {$skipped}.\n";
if ($updated > 0) {
    echo "Tip: the old .svg placeholders are still in assets/images/ if you want to revert.\n";
}
