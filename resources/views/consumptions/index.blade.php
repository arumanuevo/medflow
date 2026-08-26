        // Renderizar paginación
        function renderPagination(meta) {
            if (!meta || !meta.last_page) {
                $('#pagination').html('');
                $('#paginationInfo').html('');
                return;
            }

            const from = meta.from || 0;
            const to = meta.to || 0;
            const total = meta.total || 0;
            $('#paginationInfo').html(`Mostrando ${from} a ${to} de ${total} consumos`);

            let paginationHtml = '';

            window.changePage = function (page) {
                if (typeof currentPage !== 'undefined') {
                    currentPage = page;
                } else {
                    window.currentPage = page;
                }
                loadConsumptions();
            };

            if (meta.current_page > 1) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page - 1})" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&laquo;</span>
                </li>`;
            }

            const maxPages = 5;
            let startPage = Math.max(1, meta.current_page - Math.floor(maxPages / 2));
            let endPage = Math.min(meta.last_page, startPage + maxPages - 1);

            if (endPage - startPage + 1 < maxPages) {
                startPage = Math.max(1, endPage - maxPages + 1);
            }

            for (let i = startPage; i <= endPage; i++) {
                if (i === meta.current_page) {
                    paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                } else {
                    paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${i})">${i}</a></li>`;
                }
            }

            if (meta.current_page < meta.last_page) {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" onclick="event.preventDefault(); window.changePage(${meta.current_page + 1})" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item disabled">
                    <span class="page-link" aria-hidden="true">&raquo;</span>
                </li>`;
            }

            $('#pagination').html(paginationHtml);
        }

        $('#btnShareAnalysis').click(function() {
            const sensorId = $('#analyzeSensorId').val();
            const sensorName = $('#analyzeSensorName').text();
            if (!sensorId) return;

            const email = prompt('Ingrese el correo electrónico para enviar el reporte de ' + sensorName + ':');
            if (email) {
                $.ajax({
                    url: '/api/sensors/' + sensorId + '/share',
                    type: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token'),
                        'Accept': 'application/json'
                    },
                    data: { email: email },
                    success: function (res) {
                        if(res.success) {
                            alert('Informe enviado correctamente a ' + email);
                        } else {
                            alert('Error: ' + res.message);
                        }
                    },
                    error: function(xhr) {
                        alert('Error de conexión al enviar informe.');
                    }
                });
            }
        });
    </script>
@endpush