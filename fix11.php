<?php
$c = file_get_contents('resources/views/consumptions/index.blade.php');

// Put back the missing div that I removed in fix7.php for Radar Global
$target = "</div>\n    </div>\n\n    <!-- Modal para Radar Global";
$replacement = "</div>\n        </div>\n    </div>\n\n    <!-- Modal para Radar Global";
$c = str_replace($target, $replacement, $c);

// Also I did:
// // Fix global double div at end of container
// $c = preg_replace('/<\/div>\s*<\/div>\s*<!-- Modal para detalles del/s', "</div>\n\n    <!-- Modal para detalles del", $c);
// Wait, the main container starts with:
// <div class="container mt-4"> (1)
// <div class="row"> (2)
// <div class="col-md-12"> (3)
// <div class="card shadow-sm border-0"> (4)
// <div class="card-body"> (5)
// So we have 5 open divs!
// Let's check how many divs were originally ending it.
/*
        </div>
    </div>
</div>
<!-- Modal para detalles del consumo -->
*/
// The pristine file had THREE divs!
/*
142:         </div>
143:     </div>
144: </div>
145: 
146: <!-- Modal para detalles del consumo -->
*/
// But my regex replaced `</div> </div> <!-- Modal para detalles` with `</div> <!-- Modal`. I stripped ONE!
// I need to add it back!
$target2 = "</div>\n\n    <!-- Modal para detalles del";
$replacement2 = "</div>\n    </div>\n</div>\n\n<!-- Modal para detalles del";
$c = str_replace($target2, $replacement2, $c);

file_put_contents('resources/views/consumptions/index.blade.php', $c);
echo "Restored structural divs\n";
