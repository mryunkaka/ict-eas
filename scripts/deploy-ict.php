<?php

declare(strict_types=1);

$repo = '/home/hark8423/public_html/ict_eas';
$log = '/home/hark8423/git-deploy-ict.log';
$lockFile = '/home/hark8423/git-deploy-ict.lock';
$git = '/usr/bin/git';
$branch = 'main';
$remote = 'origin';

$deployKey = '';

date_default_timezone_set('Asia/Jakarta');

$isCli = \in_array(PHP_SAPI, ['cli', 'phpdbg'], true);

if (! $isCli) {
    header('Content-Type: text/plain; charset=UTF-8');
    if ($deployKey !== '' && (! isset($_GET['key']) || ! hash_equals($deployKey, (string) $_GET['key']))) {
        http_response_code(403);
        echo 'Forbidden';

        exit(1);
    }
}

if (! is_dir($repo) || ! is_dir($repo.'/.git')) {
    echo "Invalid repo path.\n";

    exit(1);
}

putenv('GIT_TERMINAL_PROMPT=0');
putenv('LANG=C');

$lockFp = fopen($lockFile, 'c');
if ($lockFp === false) {
    echo "Cannot open lock file.\n";

    exit(1);
}

if (! flock($lockFp, LOCK_EX | LOCK_NB)) {
    echo "Deploy already running.\n";

    exit(0);
}

$logLine = static function (string $line) use ($log): void {
    file_put_contents($log, '['.date('Y-m-d H:i:s').'] '.$line."\n", FILE_APPEND);
};

$run = static function (string $cmd) use ($logLine): string {
    $out = shell_exec($cmd.' 2>&1');
    $out = $out === null ? '' : $out;
    $logLine(trim('CMD: '.$cmd));
    foreach (preg_split("/\r\n|\n|\r/", $out) as $row) {
        if ($row !== '') {
            $logLine('OUT: '.$row);
        }
    }

    return $out;
};

$old = trim((string) shell_exec($git.' -C '.escapeshellarg($repo).' rev-parse HEAD 2>&1'));
if ($old === '' || strlen($old) < 7) {
    $logLine('ERROR: cannot read current HEAD: '.$old);
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    echo "Cannot read git HEAD.\n";

    exit(1);
}

$fetchOut = $run($git.' -C '.escapeshellarg($repo).' fetch '.$remote.' '.$branch);
if (stripos($fetchOut, 'fatal:') !== false) {
    $logLine('ERROR: fetch failed');
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    echo "Git fetch failed. See log.\n";

    exit(1);
}

$mergeOut = $run($git.' -C '.escapeshellarg($repo).' merge --ff-only FETCH_HEAD');
if (stripos($mergeOut, 'fatal:') !== false || stripos($mergeOut, 'error:') !== false) {
    $logLine('ERROR: merge failed');
    flock($lockFp, LOCK_UN);
    fclose($lockFp);
    echo "Git merge failed. See log.\n";

    exit(1);
}

$new = trim((string) shell_exec($git.' -C '.escapeshellarg($repo).' rev-parse HEAD 2>&1'));

if ($old !== $new) {
    $commits = shell_exec($git.' -C '.escapeshellarg($repo).' log '.$old.'..'.$new." --pretty=format:'%h | %an | %s'");
    $commits = $commits === null ? '' : $commits;
    foreach (explode("\n", trim($commits)) as $commit) {
        if (trim($commit) !== '') {
            $logLine('Deploy '.$commit);
        }
    }
    echo "Updated: {$old} -> {$new}\n";
} else {
    $logLine('No new commits (HEAD '.$new.')');
    echo "No new commits.\n";
}

flock($lockFp, LOCK_UN);
fclose($lockFp);
