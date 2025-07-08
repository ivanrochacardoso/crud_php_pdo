<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Dinâmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <div class="container mt-5">
        <h1 class="mb-4 text-center" id="table-title">Carregando...</h1>

        <div class="d-flex justify-content-end mb-3">
            <button id="add-record-btn" class="btn btn-primary">Adicionar Novo</button>
        </div>

        <div id="loading" class="text-center" style="display: none;">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead id="table-head" class="table-dark">
                    <!-- Cabeçalhos gerados via JS -->
                </thead>
                <tbody id="table-body">
                    <!-- Linhas geradas via JS -->
                </tbody>
            </table>
        </div>

        <nav>
            <div class="d-flex justify-content-center" id="pagination-controls"></div>
        </nav>
    </div>

    <!-- Modal Genérico para Adicionar/Editar -->
    <div id="form-modal" class="modal-backdrop" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-title"></h5>
                <div>
                    <button id="save-header-btn" class="btn btn-success me-2" title="Salvar"></button>
                    <button id="cancel-header-btn" class="btn btn-danger" title="Cancelar"></button>
                </div>
            </div>
            <div class="modal-body">
                <form id="data-form">
                    <div id="form-fields">
                        <!-- Campos do formulário gerados via JS -->
                    </div>
                    <!-- Botão de submit oculto para que o form possa ser enviado com Enter -->
                    <button type="submit" style="display: none;"></button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
