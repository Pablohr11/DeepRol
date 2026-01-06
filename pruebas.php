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
      // Convertir valor de utf-32 a ISO-8859-1 (Latin1)
      $valor_latin1 = iconv('utf-32', 'ISO-8859-1//TRANSLIT', $valor);
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

