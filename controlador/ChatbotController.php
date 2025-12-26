<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Simple helper for JSON responses
function respond($payload, $code = 200)
{
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if (!isset($_SESSION['chat_history'])) {
    $_SESSION['chat_history'] = [];
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    respond([
        'history' => $_SESSION['chat_history'],
    ]);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim($input['message'] ?? '');
$isBoot = !empty($input['boot']);

if ($message === '' && !$isBoot) {
    respond(['error' => 'Mensaje vacío'], 400);
}

$sessionEmail = $_SESSION['email'] ?? null;
$hasAuthenticatedUser = !empty($sessionEmail);
$userName = $_SESSION['nombre'] ?? ($hasAuthenticatedUser ? $sessionEmail : 'usuario');
$userEmail = $hasAuthenticatedUser ? $sessionEmail : null;

date_default_timezone_set('America/Santiago');
$hour = (int) date('H');
if ($hour < 12) {
    $timeGreeting = 'Buenos días';
} elseif ($hour < 19) {
    $timeGreeting = 'Buenas tardes';
} else {
    $timeGreeting = 'Buenas noches';
}

$systemPrompt = <<<PROMPT
Eres "EgresApp Assistant", un asistente local que guía paso a paso a los administradores que usan el sistema "EgresApp2" para gestionar egresados.

Mensaje base de bienvenida:
"¡Hola, necesitas ayuda? Soy tu asistente.

Estoy aquí para guiarte en el uso de EgresApp2. ¿Necesitas ayuda para consultar certificados de egresados, o hay alguna otra tarea en la que pueda asistirte hoy?

(Debes invitar al usuario a escoger las siguientes opciones o interpretar su consulta):
- ¿Cómo obtengo una clave de acceso?
- ¿Cómo ingreso?
- ¿Cómo hago un certificado?
- Hola, ¿necesitas ayuda?
"

Instrucciones clave que debes seguir SIEMPRE:
1. Responde en español, con tono cordial y profesional, usando pasos numerados cuando corresponda.
2. Cada vez que el usuario pregunte por generar/hacer/ingresar para crear un certificado (ej. "Quiero generar un certificado", "¿Cómo hago un certificado?", "¿Cómo entro a hacer un certificado de título?"), comienza la respuesta con el saludo dinámico "{$timeGreeting}" (debe reflejar la hora real) seguido de "con gusto te ayudaré a que puedas generar uno". Luego explica:
   Paso 1: "En el recuadro que dice 'Run solicitante', debes ingresar el Rut del interesado, una vez ingresado los datos, debes hacer click en el botón de 'Validar' y se abrirá un cuadro adicional en donde podrás generar el certificado." Finaliza la respuesta indicando que esperas la siguiente instrucción.
3. Si el usuario pregunta cómo obtener una clave o cómo ingresar, explica el flujo de recuperación/ingreso dentro de EgresApp2.
4. Siempre que puedas llevarlo a una sección específica, agrega enlaces internos Markdown, por ejemplo: [Ir a Añadir Egresados](internal://adm_egresado.php).
5. Si no conoces la respuesta, dilo con honestidad y sugiere contactar a soporte.
6. Mantén recordatorio constante de que puede pedir ayuda adicional: "¿Necesitas algo más?".

Conocimientos de la aplicación:
- Iniciar sesión: pantalla principal (index). Se puede entrar con correo/contraseña o presionar "Run solicitante" para validar un RUT.
- Recuperar contraseña: enlace "Olvidé mi contraseña" -> página Recuperar. Explica que ingrese su correo y recibirá un link.
- Crear usuarios: sólo el Administrador puede hacerlo desde el menú lateral "Crear Usuario" (formulario con nombre, correo, contraseña y confirmación).
- Escanear QR: menú "Escáner QR" abre lector para cargar códigos y vincular expedientes.
- Añadir egresados: menú "Añadir Egresados" > botón "Crear Egresado" abre modal para registrar datos básicos.
- Subir expediente PDF: en "Añadir Egresados" botón "Subir Expediente" > carga PDF, extrae datos y permite editarlos manualmente si falta información.
- Generar certificados desde tabla de egresados: botón verde con icono de certificado en la columna Acciones -> requiere haber validado y/o completado los datos del egresado.
- Añadir títulos: menú "Añadir Títulos" y modal "Agregar Título Egresado" para asociar títulos a registros existentes.
- Estadísticas: menú "Estadísticas" muestra gráficos por género, títulos y otros indicadores.
- Exportar reportes: en la tabla de egresados están disponibles botones Copy/CSV/Excel/PDF/Imprimir.
- Chatbot visible en todas las páginas públicas e internas; usa controles en la esquina inferior derecha.

Recuerda mantener las respuestas claras, ordenadas y breves, y siempre hacer referencia a estas instrucciones.
PROMPT;

// Configuración de Gemini
$geminiConfig = file_exists(__DIR__ . '/../config/gemini.php')
    ? require __DIR__ . '/../config/gemini.php'
    : [];

$geminiKey = $geminiConfig['api_key'] ?? '';
$geminiModel = $geminiConfig['model'] ?? 'gemini-1.5-flash';

if ($geminiKey === '') {
    respond([
        'error' => 'Gemini no está configurado. Define GEMINI_API_KEY o edita config/gemini.php.',
    ], 500);
}

$history = $_SESSION['chat_history'];

function mapRoleToGemini(string $role): string
{
    return $role === 'assistant' ? 'model' : 'user';
}

$contents = [[
    'role' => 'user',
    'parts' => [
        ['text' => $systemPrompt],
    ],
]];
foreach ($history as $entry) {
    $contents[] = [
        'role' => mapRoleToGemini($entry['role'] ?? 'user'),
        'parts' => [
            ['text' => $entry['content'] ?? ''],
        ],
    ];
}

if ($isBoot) {
    if ($hasAuthenticatedUser) {
        $introContent = "Saluda al usuario {$userName} (correo {$userEmail}) y recuérdale decir: '¡Hola, necesitas ayuda? Soy tu asistente.' antes de continuar.\n" .
            "Ofrece ayuda inicial para consultar certificados o cualquier tarea dentro de la plataforma.";
    } else {
        $introContent = "Saluda diciendo exactamente: '¡Hola, necesitas ayuda? Soy tu asistente.' sin mencionar nombres ni correos.\n" .
            "Ofrece ayuda inicial para consultar certificados o cualquier tarea dentro de la plataforma.";
    }
    $contents[] = [
        'role' => 'user',
        'parts' => [
            ['text' => $introContent],
        ],
    ];
} else {
    $contents[] = [
        'role' => 'user',
        'parts' => [
            ['text' => $message],
        ],
    ];
}

$payload = [
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.4,
        'topK' => 32,
        'topP' => 0.9,
        'maxOutputTokens' => 512,
    ],
];

$endpoint = sprintf(
    'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent?key=%s',
    urlencode($geminiModel),
    urlencode($geminiKey)
);

$ch = curl_init($endpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 120,
    CURLOPT_CONNECTTIMEOUT => 10,
    // Fix for local SSL certificate error
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => 0,
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false) {
    respond(['error' => 'No se pudo contactar a Gemini: ' . $error], 500);
}

$data = json_decode($response, true);
if ($status >= 400 || !is_array($data)) {
    respond([
        'error' => 'Gemini devolvió un error',
        'raw' => $response,
    ], 500);
}

$assistantMessage = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
$assistantMessage = trim($assistantMessage);
if ($assistantMessage === '') {
    respond(['error' => 'El modelo no devolvió contenido'], 500);
}

if (!$isBoot) {
    $_SESSION['chat_history'][] = [
        'role' => 'user',
        'content' => $message,
    ];
}

$_SESSION['chat_history'][] = [
    'role' => 'assistant',
    'content' => $assistantMessage,
];

respond([
    'reply' => $assistantMessage,
    'history' => $_SESSION['chat_history'],
]);
