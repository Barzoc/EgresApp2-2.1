<?php
return [
    'pdftotext_path' => 'C:/Program Files/poppler/Library/bin/pdftotext.exe',
    'python_path' => 'C:/Program Files/Python310/python.exe',
    'paddle_script_path' => __DIR__ . '/../scripts/ocr_paddle.py',
    'poppler_path' => 'C:/Program Files/poppler/Library/bin',
    'convert_path' => 'C:/Program/magick.exe',  // ImageMagick para PDFs escaneados
    'enable_paddle_ocr' => false,   // DESHABILITADO: PaddleOCR es muy lento vs Tesseract (18s)
    'enable_ai_parser' => false,    // DESHABILITADO: no usar Ollama
    'ocr_ai_script_path' => __DIR__ . '/../scripts/ocr_ai_parser.py',
    'ollama_endpoint' => 'http://localhost:11434',
    'ollama_model' => 'llama3.2:3b',
    'ai_mode' => 'never',  // No usar AI
    'process_first_page_only' => true,  // OPTIMIZACIÓN: solo procesar página 1
    'skip_slow_extraction' => false, // RE-HABILITADO: Necesario para número de certificado
];
