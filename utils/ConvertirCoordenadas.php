<?php
// Conversión de coordenadas de Inkscape (píxeles) a TCPDF (mm)
// Factor de conversión: 1 píxel = 0.2646 mm (96 DPI)

$factor = 0.2646;

$coordenadas = [
    'Fecha'    => ['x' => 602.93, 'y' => 492.77],
    'Nombre'   => ['x' => 656.04, 'y' => 531.13],
    'RUT'      => ['x' => 277.37, 'y' => 569.49],
    'Título'   => ['x' => 675.71, 'y' => 568.50],
    'Registro' => ['x' => 543.91, 'y' => 683.58],
    'Emisión'  => ['x' => 706.20, 'y' => 800.63],
    'Nombre_Encargado' => ['x' => 476.05, 'y' => 960.95],
    'Cargo'    => ['x' => 446.54, 'y' => 988.49],
];

echo "=== Conversión de Coordenadas Inkscape → TCPDF ===\n\n";
echo "Campo                    | Inkscape (px)      | TCPDF (mm)\n";
echo "-------------------------|--------------------|-----------------\n";

foreach ($coordenadas as $campo => $coords) {
    $x_mm = round($coords['x'] * $factor, 2);
    $y_mm = round($coords['y'] * $factor, 2);
    
    printf("%-24s | X=%7.2f Y=%7.2f | X=%6.2f Y=%6.2f\n", 
        $campo, 
        $coords['x'], 
        $coords['y'],
        $x_mm,
        $y_mm
    );
}

echo "\n=== Código PHP para TCPDF ===\n\n";
echo "\$data = [\n";
foreach ($coordenadas as $campo => $coords) {
    $x_mm = round($coords['x'] * $factor, 2);
    $y_mm = round($coords['y'] * $factor, 2);
    
    $comentario = str_pad("// $campo:", 30);
    echo "    {$comentario} ['text' => \$texto, 'x' => $x_mm, 'y' => $y_mm, 'w' => 0, 'h' => 0, 'size' => 10, 'align' => 'L'],\n";
}
echo "];\n";
