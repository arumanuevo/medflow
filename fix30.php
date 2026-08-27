<?php
$c = file_get_contents('resources/views/landing.blade.php');

// Smooth scrolling function for the href="#contacto" buttons to actually slide down smoothly
$smoothScrollScript = <<<EOF
<script>
    $(document).ready(function() {
        $('a[href^="#contacto"]').on('click', function(e) {
            e.preventDefault();
            var target = $(this.getAttribute('href'));
            if( target.length ) {
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 80
                }, 800);
            }
        });
    });
</script>
</body>
EOF;

$c = str_replace('</body>', $smoothScrollScript, $c);

// Add button to Custom Card
$t1 = '<div class="plan-feature"><i class="bi bi-check"></i> DelegaciÃ³n completa de inspectores y rutas</div>';
$t2 = '<div class="plan-feature"><i class="bi bi-check"></i> Delegación completa de inspectores y rutas</div>';
$t3 = '<div class="plan-feature"><i class="bi bi-check"></i> Delegaci&oacute;n completa de inspectores y rutas</div>';

foreach ([$t1, $t2, $t3] as $target) {
    if (strpos($c, $target) !== false) {
        $replacement = $target . "\n                          </div>\n                          <a href=\"#contacto\" class=\"btn btn-dark w-100 mt-4 rounded-pill fw-bold shadow-sm\"><i class=\"bi bi-headset me-2\"></i>Contactar Asesor</a>";
        $c = str_replace($target . "\n                          </div>", $replacement, $c);
        echo "Found and replaced target.\n";
        break;
    }
}

file_put_contents('resources/views/landing.blade.php', $c);
echo "Injected Script and Button\n";
