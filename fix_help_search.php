<?php
$file = 'k:\desarrollo\medflow\resources\views\help\index.blade.php';
$content = file_get_contents($file);

// Find the javascript logic for HelpSearch and replace it
$jsOldRegex = '/searchInput\.addEventListener\(\'input\', function \(\) \{.*?\n\s*\}\);\s*\}\);/s';

$jsNew = <<<EOT
searchInput.addEventListener('input', function () {
                // Removemos acentos para la búsqueda (Ej: importacion -> importación)
                const normalize = str => str.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
                const query = normalize(this.value.trim());

                helpCards.forEach(card => {
                    const cardText = normalize(card.textContent);
                    const headerText = normalize(card.querySelector('h5') ? card.querySelector('h5').textContent : '');

                    // Mostramos u ocultamos las tarjetas según match
                    if (cardText.includes(query)) {
                        card.style.display = 'block';

                        // Si el título del módulo coincide con la búsqueda, mostramos todos los items.
                        // Si no, filtramos individualmente los items para dejar solo los que coinciden.
                        const headerMatch = headerText.includes(query);
                        const lis = card.querySelectorAll('li');
                        
                        let hasVisibleLi = false;
                        lis.forEach(li => {
                            const liText = normalize(li.textContent);
                            if (headerMatch || query === '' || liText.includes(query)) {
                                li.style.display = 'block';
                                hasVisibleLi = true;
                            } else {
                                li.style.display = 'none';
                            }
                        });

                        // Si el header NO hizo match, y resulta que todos los LI se ocultaron 
                        // (porque el match fue de alguna palabra oculta que no esta en los lis),
                        // entonces ocultamos la tarjeta para no mostrar una vacía.
                        if (!headerMatch && !hasVisibleLi && query !== '') {
                            card.style.display = 'none';
                        }
                        
                    } else {
                        card.style.display = 'none';
                    }
                });
            });

        });
EOT;

$content = preg_replace($jsOldRegex, $jsNew, $content);

file_put_contents($file, $content);
echo "Search logic improved!\n";
