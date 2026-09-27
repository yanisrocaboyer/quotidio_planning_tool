<?php
function help_update_types() {
    return ['praticien', 'partenaire', 'admin'];
}

function help_updates_dir($type = null) {
    $dir = __DIR__ . '/../page/help/updates';
    if ($type !== null) {
        $dir .= '/' . $type;
    }
    return $dir;
}

function help_update_title($path) {
    $content = @file_get_contents($path);
    if ($content === false) {
        return '';
    }
    $first = strtok($content, "\r\n");
    return trim($first !== false ? $first : '');
}

function help_update_files($type) {
    if (!in_array($type, help_update_types(), true)) {
        return [];
    }
    $list = @scandir(help_updates_dir($type));
    if ($list === false) {
        return [];
    }
    $updates = [];
    foreach ($list as $file) {
        if (!preg_match('/^(.+)(\.md)$/i', $file, $m)) {
            continue;
        }
        if (!is_file(help_updates_dir($type) . '/' . $file)) {
            continue;
        }
        $ver = $m[1];
        $name = help_update_title(help_updates_dir($type) . '/' . $file);
        if ($name === '') {
            $name = 'Version ' . $ver;
        }
        $updates[] = [
            'ver' => $ver,
            'name' => $name
        ];
    }
    usort($updates, function ($a, $b) {
        return version_compare($b['ver'], $a['ver']);
    });
    return $updates;
}

function read_help_update($type, $ver) {
    if (!in_array($type, help_update_types(), true)) {
        return false;
    }
    if (!preg_match('/^[\w.-]+$/', $ver)) {
        return false;
    }
    $path = help_updates_dir($type) . '/' . $ver . '.md';
    if (!is_file($path)) {
        return false;
    }
    $content = @file_get_contents($path);
    if ($content === false) {
        return false;
    }

    $lines = preg_split('/\r?\n/', $content);
    $name = isset($lines[0]) ? trim($lines[0]) : '';
    array_shift($lines);
    if ($name === '') {
        $name = 'Version ' . $ver;
    }

    return [
        'name' => $name,
        'body' => rtrim(implode("\n", $lines))
    ];
}

function md_inline($text) {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
    $text = preg_replace('/\[([^\]]+)\]\((https?:\/\/[^)\s]+)\)/', '<a href="$2" target="_blank" rel="noopener" class="underline text-secondary">$1</a>', $text);
    $text = preg_replace('/(?<!\*)\*([^*\n]+)\*(?!\*)/', '<em>$1</em>', $text);
    return $text;
}

function md_to_html($md) {
    if (!is_string($md) || trim($md) === '') {
        return '';
    }

    $lines = preg_split('/\r?\n/', trim($md));
    $html = '';
    $listType = null;
    $inCode = false;
    $code = [];

    $closeList = function () use (&$html, &$listType) {
        if ($listType) {
            $html .= "</$listType>\n";
            $listType = null;
        }
    };

    foreach ($lines as $line) {
        $t = trim($line);

        if (preg_match('/^\s*<[^>]+>.*<\/[^>]+>\s*$/', $line) || preg_match('/^\s*<[^>]+>\s*$/', $line) || preg_match('/^\s*<\/[^>]+>\s*$/', $line)) {
            $closeList();
            $html .= $line . "\n";
            continue;
        }

        if (preg_match('/^```/', $t)) {
            if (!$inCode) {
                $closeList();
                $inCode = true;
                $code = [];
                $html .= "<pre class=\"p-3 bg-gray-100 rounded-md overflow-x-auto\"><code>";
            } else {
                $inCode = false;
                $html .= htmlspecialchars(implode("\n", $code)) . "</code></pre>\n";
                $code = [];
            }
            continue;
        }

        if ($inCode) {
            $code[] = $line;
            continue;
        }

        if ($t === '') {
            $closeList();
            continue;
        }

        if (preg_match('/^(#{1,6})\s+(.+)$/', $t, $m)) {
            $closeList();
            $level = strlen($m[1]);
            $html .= "<h$level>" . md_inline($m[2]) . "</h$level>\n";
            continue;
        }

        if (preg_match('/^\s*[-*]\s+(.+)$/', $line, $m)) {
            if ($listType !== 'ul') {
                $closeList();
                $listType = 'ul';
                $html .= "<ul class=\"list-disc ml-5 space-y-1\">\n";
            }
            $html .= "<li>" . md_inline($m[1]) . "</li>\n";
            continue;
        }

        if (preg_match('/^\s*\d+[.)]\s+(.+)$/', $line, $m)) {
            if ($listType !== 'ol') {
                $closeList();
                $listType = 'ol';
                $html .= "<ol class=\"list-decimal ml-5 space-y-1\">\n";
            }
            $html .= "<li>" . md_inline($m[1]) . "</li>\n";
            continue;
        }

        $closeList();
        $html .= "<p>" . md_inline($t) . "</p>\n";
    }

    if ($inCode) {
        $html .= htmlspecialchars(implode("\n", $code)) . "</code></pre>\n";
    }
    $closeList();

    return $html;
}