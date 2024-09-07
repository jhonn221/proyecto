<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

try {
    if (!file_exists($carpetaRuta)) {
        mkdir($carpetaRuta, 0755, true);
        $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
    } else {
        $mensaje = "La carpeta '$carpetaNombre' ya existe.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['archivos'])) {
            $archivos = $_FILES['archivos'];

            for ($i = 0; $i < count($archivos['name']); $i++) {
                if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                    if (move_uploaded_file($archivos['tmp_name'][$i], $carpetaRuta . '/' . $archivos['name'][$i])) {
                        $mensaje = "Archivo(s) subido(s) con éxito.";
                    } else {
                        throw new Exception("Error al subir el archivo: " . $archivos['name'][$i]);
                    }
                } else {
                    throw new Exception("Error en la carga del archivo: " . $archivos['name'][$i]);
                }
            }
        }

        if (isset($_POST['eliminarArchivo'])) {
            $archivoAEliminar = $_POST['eliminarArchivo'];
            $archivoRutaAEliminar = $carpetaRuta . '/' . $archivoAEliminar;

            if (file_exists($archivoRutaAEliminar)) {
                if (unlink($archivoRutaAEliminar)) {
                    $mensaje = "Archivo '$archivoAEliminar' eliminado con éxito.";
                } else {
                    throw new Exception("Error al eliminar el archivo.");
                }
            } else {
                throw new Exception("El archivo '$archivoAEliminar' no existe.");
            }
        }

        if (isset($_POST['borrarTodos'])) {
            $archivos = scandir($carpetaRuta);
            $archivos = array_diff($archivos, array('.', '..'));

            foreach ($archivos as $archivo) {
                unlink($carpetaRuta . '/' . $archivo);
            }
            $mensaje = "Todos los archivos han sido eliminados.";
        }
    }
} catch (Exception $e) {
    $mensaje = "Error: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir archivos</title>
    <link rel="stylesheet" href="estilo.css">
</head>

<body>
    <h1>Compartir archivos <sup class="beta">BETA</sup></h1>
    <div class="content">
        <h3>Sube tus archivos y comparte este enlace temporal: <span>ibu.pe/?nombre=<?php echo $carpetaNombre; ?></span></h3>
        <div class="container">
            <div class="drop-area" id="drop-area">
                <form action="" id="form" method="POST" enctype="multipart/form-data">
                    <input type="file" class="file-input" name="archivos[]" id="archivo" multiple>
                    <label for="archivo" id="upload-label">Arrastra tus archivos aquí<br>o</label>
                    <p><b>Abre el explorador</b></p>
                </form>
                <div id="progress-bar" style="display:none;">
                    <progress id="progress" value="0" max="100"></progress>
                    <span id="percentage">0%</span>
                </div>

            </div>

            <div class="container2">
                <div id="file-list" class="pila">
                    <?php
                    $targetDir = $carpetaRuta;
                    $files = scandir($targetDir);
                    $files = array_diff($files, array('.', '..'));

                    if (count($files) > 0) {
                        echo "<h3 style='margin-bottom:10px;'>Archivos Subidos:</h3>";

                        foreach ($files as $file) {
                            echo "<div class='archivos_subidos'>
                            <div><a href='$carpetaRuta/$file' download class='boton-descargar'>$file</a></div>
                            <div>
                            <form action='' method='POST' style='display:inline;'>
                                <input type='hidden' name='eliminarArchivo' value='$file'>
                                <button type='submit' class='btn_delete'>
                                    <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-trash' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'>
                                        <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                        <path d='M4 7l16 0' />
                                        <path d='M10 11l0 6' />
                                        <path d='M14 11l0 6' />
                                        <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                        <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                    </svg>
                                </button>
                            </form>
                        </div>
                        </div>";
                        }
                    } else {
                        echo "No se han subido archivos.";
                    }
                    ?>
                    </br></br>
                    <button id="download-all">Descargar Todos</button>
                    <form action="" method="POST" style="display:inline;">
                        <button type="submit" name="borrarTodos" class="btn_delete_all">Eliminar Todos</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('archivo');
        const form = document.getElementById('form');
        const progressBar = document.getElementById('progress-bar');
        const progress = document.getElementById('progress');
        const percentage = document.getElementById('percentage');
        const uploadLabel = document.getElementById('upload-label');
        const downloadAllButton = document.getElementById('download-all');

        // Cambia la imagen cuando un archivo es arrastrado sobre el área de carga
        dropArea.addEventListener('dragover', (event) => {
            event.preventDefault();
            dropArea.classList.add('dragover');
            uploadLabel.textContent = 'Suelta tus archivos aquí';
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('dragover');
            uploadLabel.textContent = 'Arrastra tus archivos aquí o';
        });

        dropArea.addEventListener('drop', (event) => {
            event.preventDefault();
            dropArea.classList.remove('dragover');
            uploadLabel.textContent = 'Arrastra tus archivos aquí o';
            const files = event.dataTransfer.files;
            fileInput.files = files;
            form.submit();
        });

        fileInput.addEventListener('change', () => {
            form.submit();
        });

        form.addEventListener('submit', (event) => {
            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.upload.onprogress = (event) => {
                if (event.lengthComputable) {
                    const percentComplete = Math.round((event.loaded / event.total) * 100);
                    progressBar.style.display = 'block';
                    progress.value = percentComplete;
                    percentage.textContent = percentComplete + '%';
                }
            };
            xhr.onload = () => {
                if (xhr.status === 200) {
                    console.log('Files uploaded successfully');
                    // Actualiza localStorage con la lista de archivos
                    updateFileList();
                } else {
                    console.error('An error occurred while uploading files');
                }
                progressBar.style.display = 'none';
            };
            xhr.send(formData);
            event.preventDefault();
        });

        // Descargar todos los archivos
        downloadAllButton.addEventListener('click', () => {
            window.location.href = 'descargar_todos.php?nombre=' + encodeURIComponent('<?php echo $carpetaNombre; ?>');
        });

        // Configura la instalación de PWA
        window.addEventListener('load', () => {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/service-worker.js').
                then(() => {
                    console.log('Service Worker registered');
                });
            }
            // Botón de instalación de PWA
            let deferredPrompt;
            window.addEventListener('beforeinstallprompt', (event) => {
                event.preventDefault();
                deferredPrompt = event;
                const installButton = document.createElement('button');
                installButton.textContent = 'Instalar App';
                installButton.addEventListener('click', () => {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then((result) => {
                        if (result.outcome === 'accepted') {
                            console.log('User accepted the A2HS prompt');
                        } else {
                            console.log('User dismissed the A2HS prompt');
                        }
                        deferredPrompt = null;
                    });
                });
                document.body.appendChild(installButton);
            });
        });

        // Función para actualizar la lista de archivos en localStorage
        function updateFileList() {
            fetch('<?php echo $carpetaNombre; ?>')
                .then(response => response.text())
                .then(data => {
                    localStorage.setItem('fileList', data);
                    window.dispatchEvent(new Event('storage'));
                });
        }

        // Maneja el evento de almacenamiento para actualizar la interfaz
        window.addEventListener('storage', () => {
            fetch('<?php echo $carpetaNombre; ?>')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('file-list').innerHTML = data;
                });
        });

        // Inicializa la lista de archivos desde localStorage
        document.addEventListener('DOMContentLoaded', () => {
            const storedFiles = localStorage.getItem('fileList');
            if (storedFiles) {
                document.getElementById('file-list').innerHTML = storedFiles;
            }
        });
    </script>
</body>

</html>