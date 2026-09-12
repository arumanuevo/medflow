<?php

$layoutPath = 'k:\desarrollo\medflow\resources\views\layouts\modern.blade.php';
$content = file_get_contents($layoutPath);

$oldAjaxSuccess = <<<EOT
            success: function(response) {
                document.getElementById(spinnerId).remove();
                if(response.success === false) {
                   chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-warning text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">\${response.answer}</span>
                        </div>
                    `;
                } else {
                    let formattedHtml = response.answer.replace(/\*\*(.*?)\*\*/g, '<strong>\$1</strong>');
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <span class="badge bg-white text-dark shadow-sm px-3 py-2 text-wrap" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; white-space: pre-wrap; font-weight: normal; line-height: 1.4;">\${formattedHtml}</span>
                        </div>
                    `;
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
EOT;

$newAjaxSuccess = <<<EOT
            success: function(response) {
                document.getElementById(spinnerId).remove();

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

                if(response.success === false) {
                   chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <div class="bg-warning text-dark shadow-sm px-3 py-2" style="border-radius: 15px 15px 15px 0; max-width: 90%; text-align: left !important; font-weight: normal; line-height: 1.5; font-size: 0.9rem; display: inline-block;">\${response.answer}</div>
                        </div>
                    `;
                } else {
                    let formattedHtml = parseMD(response.answer);
                    chatBox.innerHTML += `
                        <div class="mb-3 text-start">
                            <div class="bg-white text-dark shadow-sm px-3 py-3" style="border-radius: 15px 15px 15px 0; max-width: 95%; text-align: left !important; font-weight: normal; line-height: 1.5; font-size: 0.9rem; display: inline-block;">\${formattedHtml}</div>
                        </div>
                    `;
                }
                chatBox.scrollTop = chatBox.scrollHeight;
            },
EOT;

$content = str_replace($oldAjaxSuccess, $newAjaxSuccess, $content);

file_put_contents($layoutPath, $content);
echo "Chat Markdown formatter installed!\n";
