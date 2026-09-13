<?php
$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

// The old javascript parsing block
$oldJS = <<<EOT
                // Advanced Lightweight Markdown Parser
                function parseMD(md) {
                    let html = md;
                    html = html.replace(/^### (.*\$)/gim, '<h6 class="mt-3 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/^## (.*\$)/gim, '<h6 class="mt-3 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/^# (.*\$)/gim, '<h6 class="mt-3 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>\$1</strong>');
                    html = html.replace(/`(.*?)`/gim, '<code class="bg-light px-1 text-danger rounded" style="font-size: 0.85rem;">\$1</code>');
                    html = html.replace(/^\s*-\s(.*\$)/gim, '<li class="ms-3 mb-1">\$1</li>');
                    html = html.replace(/^\-\-\-[ \t]*\$/gim, '<hr class="my-2 border-secondary">');
                    html = html.replace(/\\n/g, '<br>');
                    return html;
                }
EOT;

$newJS = <<<EOT
                // Advanced Lightweight Markdown Parser (Tightened spacing)
                function parseMD(md) {
                    let html = md;
                    
                    // 1. Convert markup to tags (reduced mt-3 to mt-2 for tighter spacing)
                    html = html.replace(/^### (.*\$)/gim, '<h6 class="mt-2 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/^## (.*\$)/gim, '<h6 class="mt-2 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/^# (.*\$)/gim, '<h6 class="mt-2 mb-1 fw-bold text-primary border-bottom pb-1">\$1</h6>');
                    html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>\$1</strong>');
                    html = html.replace(/`(.*?)`/gim, '<code class="bg-light px-1 text-danger rounded" style="font-size: 0.85rem;">\$1</code>');
                    html = html.replace(/^\s*-\s(.*\$)/gim, '<li class="ms-3 mb-0">\$1</li>');
                    html = html.replace(/^\-\-\-[ \t]*\$/gim, '<hr class="my-2 border-secondary">');
                    
                    // 2. Colapsar multiples newlines a uno solo logico para evitar enormes huecos
                    html = html.replace(/\\n\\s*\\n/g, '<br><br>');
                    html = html.replace(/\\n/g, '<br>');
                    
                    // 3. Limpieza Quirurgica: Quitar saltos de linea sobrantes anexados a Titulos y Listas
                    html = html.replace(/<br><h6/g, '<h6');
                    html = html.replace(/<\\/h6><br>/g, '</h6>');
                    html = html.replace(/<\\/li><br>/g, '</li>');
                    html = html.replace(/<br><li/g, '<li');
                    html = html.replace(/<br><br><li/g, '<li'); // Evita dobles saltos antes de una lista
                    
                    return html;
                }
EOT;

$content = str_replace($oldJS, $newJS, $content);

// Ensure the bubble line-height makes text cohesive
$oldBubble = 'display: inline-block;">${formattedHtml}</div>';
$newBubble = 'display: inline-block; line-height: 1.35;">${formattedHtml}</div>';
$content = str_replace($oldBubble, $newBubble, $content);

file_put_contents($layoutPath, $content);
echo "Chat parser tight spacing applied!\n";
