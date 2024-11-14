<?php
$backlink_code = <<<EOT
<?php
\$backlinks_json = file_get_contents('https://asd.com/panel/api/client-backlinks.php?site=' . \$_SERVER['HTTP_HOST']);
\$backlinks = json_decode(\$backlinks_json, true);

if (\$backlinks && !empty(\$backlinks['links'])) {
    foreach (\$backlinks['links'] as \$link) {
        echo '<a href="' . htmlspecialchars(\$link['target_url']) . '">' . 
             htmlspecialchars(\$link['anchor_text']) . '</a> ';
    }
}
?>
EOT;

echo $backlink_code;