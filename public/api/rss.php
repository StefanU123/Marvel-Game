<?php

/**
 * api/rss.php — Leaderboard RSS 2.0 feed.
 *
 * Exposes the "best players" ranking as a syndication feed, as required by the
 * project brief ("Clasamentul ... disponibil ... ca flux de date RSS").
 *
 * Each <item> is one ranked player. Optional query filters mirror the JSON
 * leaderboard endpoint:
 *   ?hero_id=<int>        restrict to a single hero
 *   ?difficulty=<easy|medium|hard>
 *   ?limit=<1..100>       number of ranked players (default 20)
 *
 * Output is application/rss+xml. All dynamic text is XML-escaped so the feed
 * stays well-formed and is not vulnerable to injection.
 */

require_once __DIR__ . '/../../includes/db.php';

header('Content-Type: application/rss+xml; charset=UTF-8');

/**
 * Escape a value for safe inclusion in XML text/attribute nodes.
 */
function xml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * Convert a SQLite UTC timestamp ("YYYY-MM-DD HH:MM:SS") to an RFC-822 date,
 * which is the format RSS <pubDate> elements require. Falls back to "now".
 */
function rssDate(?string $sqliteTimestamp): string
{
    $time = $sqliteTimestamp ? strtotime($sqliteTimestamp . ' UTC') : false;
    if ($time === false) {
        $time = time();
    }
    return gmdate('D, d M Y H:i:s', $time) . ' GMT';
}

// Build the absolute base URL so feed links point back at the live site.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $scheme . '://' . $host;
$selfUrl = $baseUrl . ($_SERVER['REQUEST_URI'] ?? '/api/rss.php');
$leaderboardUrl = $baseUrl . '/leaderboard.php';

// ── Read + validate filters ────────────────────────────────────────────────
$heroId = filter_input(INPUT_GET, 'hero_id', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$difficulty = $_GET['difficulty'] ?? '';
$allowedDifficulties = ['easy', 'medium', 'hard'];
if (!in_array($difficulty, $allowedDifficulties, true)) {
    $difficulty = '';
}
$limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, [
    'options' => ['default' => 20, 'min_range' => 1, 'max_range' => 100],
]);

// Compose a human-readable feed title that reflects the active filters.
$titleSuffix = '';
$heroName = null;

try {
    $pdo = getDatabaseConnection();

    if ($heroId) {
        $heroStmt = $pdo->prepare('SELECT name FROM heroes WHERE id = :id');
        $heroStmt->execute([':id' => $heroId]);
        $heroName = $heroStmt->fetchColumn() ?: null;
    }
    if ($heroName) {
        $titleSuffix .= ' — ' . $heroName;
    }
    if ($difficulty) {
        $titleSuffix .= ' (' . ucfirst($difficulty) . ')';
    }

    // ── Aggregate the ranking (same shape as the JSON leaderboard) ──────────
    $where = [];
    $params = [];
    if ($heroId) {
        $where[] = 's.hero_id = :hero_id';
        $params[':hero_id'] = $heroId;
    }
    if ($difficulty) {
        $where[] = 's.difficulty = :difficulty';
        $params[':difficulty'] = $difficulty;
    }
    $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $sql = "SELECT u.username,
                   SUM(s.score)           AS total_score,
                   COUNT(s.id)            AS games_played,
                   MAX(s.score)           AS best_score,
                   SUM(s.correct_count)   AS total_correct,
                   SUM(s.total_questions) AS total_answered,
                   MAX(s.played_at)       AS last_played
            FROM scores s
            JOIN users u ON u.id = s.user_id
            $whereSql
            GROUP BY s.user_id, u.username
            ORDER BY total_score DESC, games_played ASC
            LIMIT :limit";

    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
} catch (Throwable $error) {
    // Emit a minimal but valid feed on failure rather than a broken response.
    $rows = [];
    $loadFailed = true;
}

$feedTitle = 'Marvel Trivia — Top Players' . $titleSuffix;
$now = rssDate(null);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
  <channel>
    <title><?php echo xml($feedTitle); ?></title>
    <link><?php echo xml($leaderboardUrl); ?></link>
    <atom:link href="<?php echo xml($selfUrl); ?>" rel="self" type="application/rss+xml" />
    <description>Live ranking of the best Marvel Trivia players.</description>
    <language>en-us</language>
    <lastBuildDate><?php echo $now; ?></lastBuildDate>
    <ttl>5</ttl>
<?php if (empty($rows)): ?>
    <item>
      <title>No scores yet</title>
      <link><?php echo xml($leaderboardUrl); ?></link>
      <description><?php echo xml(isset($loadFailed) ? 'The leaderboard could not be loaded.' : 'Be the first to play and top the leaderboard!'); ?></description>
      <guid isPermaLink="false">marvel-trivia-empty</guid>
      <pubDate><?php echo $now; ?></pubDate>
    </item>
<?php else: ?>
<?php foreach ($rows as $index => $row):
    $rank = $index + 1;
    $username = (string) $row['username'];
    $totalScore = (int) $row['total_score'];
    $games = (int) $row['games_played'];
    $best = (int) $row['best_score'];
    $answered = (int) $row['total_answered'];
    $accuracy = $answered > 0 ? round(((int) $row['total_correct']) / $answered * 100) : 0;

    $itemTitle = sprintf('#%d %s — %s pts', $rank, $username, number_format($totalScore));
    $itemDesc = sprintf(
        '%s is ranked #%d with %s total points across %d game%s (best %s pts, %d%% accuracy).',
        $username,
        $rank,
        number_format($totalScore),
        $games,
        $games === 1 ? '' : 's',
        number_format($best),
        $accuracy
    );
    // Stable per-player GUID so readers can track rank changes per entry.
    $guid = 'marvel-trivia-player-' . rawurlencode($username);
?>
    <item>
      <title><?php echo xml($itemTitle); ?></title>
      <link><?php echo xml($leaderboardUrl); ?></link>
      <description><?php echo xml($itemDesc); ?></description>
      <guid isPermaLink="false"><?php echo xml($guid); ?></guid>
      <pubDate><?php echo rssDate($row['last_played'] ?? null); ?></pubDate>
    </item>
<?php endforeach; ?>
<?php endif; ?>
  </channel>
</rss>
