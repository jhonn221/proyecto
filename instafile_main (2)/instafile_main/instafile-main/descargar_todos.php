<?php
// Obtiene el nombre de la carpeta desde el parámetro GET
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

// Verifica si la carpeta existe
if (is_dir($carpetaRuta)) {
    $zip = new ZipArchive();
    $zipFilename = tempnam(sys_get_temp_dir(), 'zip');

    // Crea el archivo ZIP
    if ($zip->open($zipFilename, ZipArchive::CREATE) === TRUE) {
        $archivos = scandir($carpetaRuta);
        $archivos = array_diff($archivos, array('.', '..'));

        // Agrega cada archivo al ZIP
        foreach ($archivos as $archivo) {
            $zip->addFile($carpetaRuta . '/' . $archivo, $archivo);
        }
        
        $zip->close();

        // Envia el archivo ZIP al navegador
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="archivos.zip"');
        header('Content-Length: ' . filesize($zipFilename));
        readfile($zipFilename);

        // Elimina el archivo ZIP temporal
        unlink($zipFilename);
    } else {
        echo 'No se pudo crear el archivo ZIP.';
    }
} else {
    echo 'La carpeta no existe.';
}
?>

