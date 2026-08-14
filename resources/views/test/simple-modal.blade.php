<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Modal Test</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h3>TEST MODAL</h3>
            </div>
            <div class="card-body text-center">
                <button type="button" class="btn btn-success btn-lg" id="miBoton">
                    <i class="fas fa-user-md"></i> ABRIR MODAL
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="miModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background: #1A5276; color: white;">
                    <h5 class="modal-title">MI MODAL</h5>
                    <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                </div>
                <div class="modal-body">
                    <p>✅ EL MODAL FUNCIONA CORRECTAMENTE</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('miBoton').addEventListener('click', function() {
            console.log('Click en botón');
            $('#miModal').modal('show');
        });
    </script>
</body>
</html>