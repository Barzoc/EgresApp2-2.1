<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Expediente - EGRESAPP2</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .upload-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 800px;
            width: 100%;
        }
        
        .upload-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .upload-header h1 {
            color: #667eea;
            font-weight: bold;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .upload-header p {
            color: #6c757d;
            font-size: 1rem;
        }
        
        .upload-zone {
            border: 3px dashed #667eea;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background: #f8f9ff;
        }
        
        .upload-zone:hover {
            border-color: #764ba2;
            background: #f0f2ff;
            transform: translateY(-2px);
        }
        
        .upload-zone.dragover {
            border-color: #764ba2;
            background: #e8eaff;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 4rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        
        .file-input {
            display: none;
        }
        
        .btn-upload {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        
        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-upload:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .file-preview {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 10px;
            border-left: 4px solid #17a2b8;
        }
        
        .file-preview.show {
            display: block;
        }
        
        .data-section {
            display: none;
            margin-top: 30px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 15px;
        }
        
        .data-section.show {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .data-field {
            margin-bottom: 15px;
        }
        
        .data-field label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .data-field input {
            border-radius: 8px;
            border: 1px solid #ced4da;
            padding: 10px 15px;
        }
        
        .alert {
            border-radius: 10px;
            padding: 15px 20px;
        }
        
        .spinner-border {
            display: none;
        }
        
        .processing .spinner-border {
            display: inline-block;
        }
        
        .badge-status {
            font-size: 0.9rem;
            padding: 8px 15px;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <div class="upload-header">
            <h1><i class="fas fa-file-import"></i> Procesar Expediente</h1>
            <p>Sube un archivo PDF para extraer y guardar los datos del egresado</p>
        </div>
        
        <!-- Upload Zone -->
        <div class="upload-zone" id="uploadZone">
            <i class="fas fa-cloud-upload-alt upload-icon"></i>
            <h4>Arrastra tu PDF aquí o haz clic para seleccionar</h4>
            <p class="text-muted">Solo archivos PDF - Tamaño máximo: 10MB</p>
            <input type="file" id="fileInput" class="file-input" accept="application/pdf">
        </div>
        
        <!-- File Preview -->
        <div class="file-preview" id="filePreview">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-file-pdf text-danger mr-2"></i>
                    <strong id="fileName"></strong>
                    <span class="text-muted ml-2" id="fileSize"></span>
                </div>
                <button class="btn btn-sm btn-outline-danger" id="removeFile">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        
        <!-- Process Button -->
        <div class="text-center">
            <button class="btn btn-upload" id="processBtn" disabled>
                <span class="spinner-border spinner-border-sm mr-2"></span>
                <span id="btnText">Procesar Expediente</span>
            </button>
        </div>
        
        <!-- Alerts -->
        <div id="alertContainer" class="mt-3"></div>
        
        <!-- Extracted Data Section -->
        <div class="data-section" id="dataSection">
            <h5 class="mb-3"><i class="fas fa-check-circle text-success mr-2"></i>Datos Extraídos del PDF</h5>
            
            <div class="data-field">
                <label>RUT / Identificación</label>
                <input type="text" class="form-control" id="rut" readonly>
            </div>
            
            <div class="data-field">
                <label>Nombre Completo</label>
                <input type="text" class="form-control" id="nombre" readonly>
            </div>
            
            <div class="data-field">
                <label>Título Obtenido</label>
                <input type="text" class="form-control" id="titulo" readonly>
            </div>
            
            <div class="data-field">
                <label>Número de Certificado</label>
                <input type="text" class="form-control" id="numeroCertificado" readonly>
            </div>
            
            <div class="data-field">
                <label>Fecha de Egreso</label>
                <input type="text" class="form-control" id="fechaEgreso" readonly>
            </div>
            
            <div class="data-field">
                <label>Sexo</label>
                <input type="text" class="form-control" id="sexo" readonly>
            </div>
            
            <div class="mt-4">
                <div class="alert alert-success">
                    <i class="fas fa-database mr-2"></i>
                    Los datos han sido guardados exitosamente en la base de datos.
                </div>
                
                <div class="text-center">
                    <a href="vista/adm_egresado.php" class="btn btn-primary">
                        <i class="fas fa-users mr-2"></i>Ver Egresados
                    </a>
                    <button class="btn btn-outline-primary" id="processAnother">
                        <i class="fas fa-plus mr-2"></i>Procesar Otro
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedFile = null;
        
        // DOM Elements
        const uploadZone = $('#uploadZone');
        const fileInput = $('#fileInput');
        const filePreview = $('#filePreview');
        const fileName = $('#fileName');
        const fileSize = $('#fileSize');
        const removeFileBtn = $('#removeFile');
        const processBtn = $('#processBtn');
        const btnText = $('#btnText');
        const alertContainer = $('#alertContainer');
        const dataSection = $('#dataSection');
        
        // Upload zone click
        uploadZone.on('click', () => fileInput.click());
        
        // File selection
        fileInput.on('change', (e) => {
            const file = e.target.files[0];
            if (file) handleFileSelect(file);
        });
        
        // Drag and drop
        uploadZone.on('dragover', (e) => {
            e.preventDefault();
            uploadZone.addClass('dragover');
        });
        
        uploadZone.on('dragleave', () => {
            uploadZone.removeClass('dragover');
        });
        
        uploadZone.on('drop', (e) => {
            e.preventDefault();
            uploadZone.removeClass('dragover');
            
            const file = e.originalEvent.dataTransfer.files[0];
            if (file && file.type === 'application/pdf') {
                handleFileSelect(file);
            } else {
                showAlert('Por favor, selecciona solo archivos PDF', 'warning');
            }
        });
        
        // Remove file
        removeFileBtn.on('click', () => {
            selectedFile = null;
            fileInput.val('');
            filePreview.removeClass('show');
            processBtn.prop('disabled', true);
            dataSection.removeClass('show');
            alertContainer.empty();
        });
        
        // Process another
        $('#processAnother').on('click', () => {
            removeFileBtn.click();
        });
        
        // Handle file selection
        function handleFileSelect(file) {
            if (file.size > 10 * 1024 * 1024) {
                showAlert('El archivo es demasiado grande. Máximo 10MB', 'danger');
                return;
            }
            
            selectedFile = file;
            fileName.text(file.name);
            fileSize.text(formatFileSize(file.size));
            filePreview.addClass('show');
            processBtn.prop('disabled', false);
            dataSection.removeClass('show');
            alertContainer.empty();
        }
        
        // Process button click
        processBtn.on('click', async () => {
            if (!selectedFile) return;
            
            // Show processing state
            processBtn.addClass('processing').prop('disabled', true);
            btnText.text('Procesando...');
            alertContainer.empty();
            dataSection.removeClass('show');
            
            // Create FormData
            const formData = new FormData();
            formData.append('file', selectedFile);
            
            try {
                const response = await fetch('controlador/ProcesarExpedienteController.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                // Reset button state
                processBtn.removeClass('processing').prop('disabled', false);
                btnText.text('Procesar Expediente');
                
                if (result.success) {
                    // Show extracted data
                    $('#rut').val(result.fields?.rut || 'No disponible');
                    $('#nombre').val(result.fields?.nombre || 'No disponible');
                    $('#titulo').val(result.fields?.titulo || result.fields?.especialidad || 'No disponible');
                    $('#numeroCertificado').val(result.fields?.numero_certificado || 'No disponible');
                    $('#fechaEgreso').val(result.fields?.fecha_egreso || 'No disponible');
                    $('#sexo').val(result.fields?.sexo || 'No disponible');
                    
                    dataSection.addClass('show');
                    showAlert('¡Expediente procesado exitosamente!', 'success');
                } else {
                    showAlert(result.mensaje || 'Error al procesar el expediente', 'danger');
                }
            } catch (error) {
                console.error('Error:', error);
                processBtn.removeClass('processing').prop('disabled', false);
                btnText.text('Procesar Expediente');
                showAlert('Error de conexión. Por favor, intenta de nuevo.', 'danger');
            }
        });
        
        // Utility functions
        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(2) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }
        
        function showAlert(message, type) {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'times-circle'} mr-2"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;
            alertContainer.html(alertHtml);
        }
    </script>
</body>
</html>
