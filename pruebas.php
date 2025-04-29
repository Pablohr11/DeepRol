<?php
// /**
//  * Cambia el campo "Background" a "Ermitaño2" en un PDF con AcroForms usando pdftk.
//  */

// // 1. Definir el campo
// $campos = [
//     'Background' => 'Ermitaño2'
// ];

// // 2. Crear el FDF dinámicamente
// function crearFDF(array $fields, $pdf = null): string {
//     $fdf = "%FDF-1.2\n";
//     $fdf .= "1 0 obj\n<<\n/FDF << /Fields [\n";

//     foreach ($fields as $campo => $valor) {
//         $campo_escapado = addslashes($campo);
//         $valor_escapado = addslashes($valor);
//         $fdf .= "<< /T ($campo_escapado) /V ($valor_escapado) >>\n";
//     }

//     $fdf .= "]\n";

//     if ($pdf) {
//         $fdf .= "/F ($pdf)\n";
//     }

//     $fdf .= ">>\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF\n";
//     return $fdf;
// }

// // 3. Guardamos el FDF temporalmente
// $fdfPath = './resources/fichas/temp_data.fdf';
// file_put_contents($fdfPath, crearFDF($campos));

// // 4. Ejecutar pdftk
// $inputPdf = './resources/fichas/Pablo.pdf';      // Aquí pon el nombre real de tu PDF original
// $outputPdf = './resources/fichas/formulario_background_editado.pdf'; // El nuevo PDF modificado

// if (file_exists($inputPdf)) {
//   echo("jeje");
// } else {
//   echo("jo");
// }

// // Comando
// $command = escapeshellcmd("pdftk $inputPdf fill_form $fdfPath output $outputPdf flatten");

// // Ejecutar
// exec($command, $output, $return_var);

// // 5. Limpiar
// // unlink($fdfPath);

// // 6. Confirmar éxito
// if ($return_var === 0) {
//     echo "Campo 'Background' cambiado exitosamente a 'Ermitaño2'. PDF guardado como: $outputPdf";
// } else {
//     echo "Error modificando el PDF.";
// }
// ?>

<?php
// Definimos el cambio
$campos = [
    'Background' => 'Ermitaño2'
];

// Creamos FDF
function crearFDF(array $fields): string {
  $fdf = "%FDF-1.2\n";
  $fdf .= "1 0 obj\n<<\n/FDF << /Fields [\n";

  foreach ($fields as $campo => $valor) {
      $campo_escapado = addslashes($campo);
      // Convertir valor de UTF-8 a ISO-8859-1 (Latin1)
      $valor_latin1 = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $valor);
      $valor_escapado = addslashes($valor_latin1);
      $fdf .= "<< /T ($campo_escapado) /V ($valor_escapado) >>\n";
  }

  $fdf .= "]\n>>\n>>\nendobj\ntrailer\n<<\n/Root 1 0 R\n>>\n%%EOF\n";
  return $fdf;
}


// Guardamos FDF temporal
$fdfPath = './resources/fichas/temp_data.fdf';
file_put_contents($fdfPath, crearFDF($campos));

// Procesamos
$inputPdf = './resources/fichas/Pablo.pdf'; // Tu archivo
$outputPdf = './resources/fichas/Pablo_modificado.pdf';

$command = escapeshellcmd("pdftk $inputPdf fill_form $fdfPath output $outputPdf flatten");
exec($command . " 2>&1", $output, $return_var);

// Limpiamos
unlink($fdfPath);

// Resultado
if ($return_var === 0) {
    echo "✅ Campo 'Background' actualizado exitosamente a 'Ermitaño2'.";
} else {
    echo "❌ Error modificando el PDF:";
    echo "<pre>";
    print_r($output);
    echo "</pre>";
}
?>

