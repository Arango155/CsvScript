<?php
header("Access-Control-Allow-Origin: https://www.edu.asoani.org"); // Reemplaza con el origen permitido

require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/SMTP.php';
// Incluye la biblioteca PHP QR Code
require __DIR__.'/phpqrcode/phpqrcode.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Datos de conexión a la base de datos
$servername = "localhost"; // Cambia esto al nombre de tu servidor de base de datos
$username = "asoanior_tmpEventos"; // Cambia esto a tu nombre de usuario de la base de datos
$password = "asoani2023"; // Cambia esto a tu contraseña de la base de datos
$dbname = "asoanior_tmpEventos"; // Cambia esto al nombre de tu base de datos

// Obtén los valores de los campos del formulario
$tipoInscripcion = strtoupper($_POST["tipoInscripcion"]);
$numColegiado = $_POST["numColegiado"];
$name = strtoupper($_POST["name"]);
$surname = strtoupper($_POST["surname"]);
$municipio = strtoupper($_POST["municipio"]);
$celular = $_POST["celular"];
$email = $_POST["email"];

// Ruta de carga de archivos
$uploadsDirectory = __DIR__.'/boletas/'; // Cambia esto a la ruta donde deseas guardar los archivos
$uploadedFile = $_FILES["boleta"]; // Obtiene la información del archivo cargado

// Nombre del archivo cargado
$filename = $uploadsDirectory . basename($uploadedFile["name"]);

// Mueve el archivo cargado a la ruta deseada en el servidor
move_uploaded_file($uploadedFile["tmp_name"], $filename);

// Crear una conexión a la base de datos
$conexion = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión a la base de datos
if ($conexion->connect_error) {
    die("Error en la conexión a la base de datos: " . $conexion->connect_error);
}

// Prepara la consulta SQL para verificar si el correo ya existe
$sqlVerificarCorreo = "SELECT COUNT(*) FROM cena_tropical_2023 WHERE email = ?";

// Prepara la sentencia SQL
$stmtVerificarCorreo = $conexion->prepare($sqlVerificarCorreo);

// Vincula el parámetro con el valor
$stmtVerificarCorreo->bind_param("s", $email);

// Ejecuta la consulta para verificar el correo
$stmtVerificarCorreo->execute();

// Obtiene el resultado de la consulta
$stmtVerificarCorreo->bind_result($correoExistente);

// Fetch para obtener el resultado
$stmtVerificarCorreo->fetch();

// Cierra la sentencia de verificación del correo
$stmtVerificarCorreo->close();

// Verifica si el correo ya existe en la base de datos
if ($correoExistente > 0) {
    // Redireccionar al índice
    header("Location: https://www.edu.asoani.org"); // Cambia esto a la URL a la que deseas redirigir
} else {

    // Prepara la consulta SQL para insertar los datos en la tabla
    $sql = "INSERT INTO cena_tropical_2023 (tipoInscripcion, numColegiado, name, surname, municipio, celular, email, boleta) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepara la sentencia SQL
    $stmt = $conexion->prepare($sql);

    // Vincula los parámetros con los valores
    $stmt->bind_param("ssssssss", $tipoInscripcion, $numColegiado, $name, $surname, $municipio, $celular, $email, $boleta);

    // Ejecuta la consulta
    if ($stmt->execute()) {
        // Genera el contenido para el código QR (puedes usar la URL deseada)
        $qrContent = "https://www.edu.asoani.org/confirmacion_cena.php?email=" . $email;

        // Define la ruta donde se guardará la imagen del código QR
        $qrImagePath = __DIR__.'/qr/'. $email . '.png';

        // Intenta generar el código QR
        QRcode::png($qrContent, $qrImagePath, QR_ECLEVEL_L, 10);

        if($tipoInscripcion === 'PARTICULAR'){
            $report_mail = new PHPMailer(true);
            try {
                // Configurar el servidor SMTP externo y las credenciales
                $report_mail->isSMTP();
                $report_mail->Host       = 'mail.asoani.org';  // Cambia esto al servidor SMTP externo
                $report_mail->SMTPAuth   = true;
                $report_mail->Username   = 'inscripciones@asoani.org';  // Cambia esto a tu dirección de correo
                $report_mail->Password   = 'Asoani20$3';  // Cambia esto a tu contraseña de correo
                $report_mail->SMTPSecure = false; //PHPMailer::ENCRYPTION_STARTTLS; // Opciones: 'ssl', 'tls', o false para ninguna
                $report_mail->Port       = 26; // Puerto SMTP externo, puede variar según tu proveedor de correo

                // Resto de la configuración del correo
                $report_mail->setFrom('inscripciones@edu.asoani.org', 'ASOANI');
                $report_mail->addAddress('alevasyo4@gmail.com', 'Destinatario'); //alevasyo4@gmail.com
                $report_mail->isHTML(true);
                $report_mail->Subject = 'CENA TROPICAL - Inscripcion de un Particular';

                // Lee el contenido de la plantilla HTML
                $htmlContent = file_get_contents(__DIR__.'/plantilla-correo-cena-boleta.html');

                $imgLink = "https://edu.asoani.org/assets/php/boletas/" . basename($uploadedFile["name"]);

                // Realiza la sustitución de variables
                $htmlContent = str_replace("VAR_NOMBRE", $name, $htmlContent);
                $htmlContent = str_replace("VAR_APELLIDO", $surname, $htmlContent);
                $htmlContent = str_replace("VAR_TEL", $celular, $htmlContent);
                $htmlContent = str_replace("VAR_ENLACE", $imgLink, $htmlContent);

                $report_mail->Body = $htmlContent;

                // Enviar el correo
                $report_mail->send();
            } catch (Exception $e) {
                echo "Error al enviar el correo: {$mail->ErrorInfo}";
            }
        }


        // Crear una nueva instancia de PHPMailer
        $mail = new PHPMailer(true);

        //$mail->SMTPDebug = 2; // Nivel de depuración (0 para desactivar, 1 para mensajes básicos, 2 para mensajes detallados)

        try {
            // Configurar el servidor SMTP externo y las credenciales
            $mail->isSMTP();
            $mail->Host       = 'mail.asoani.org';  // Cambia esto al servidor SMTP externo
            $mail->SMTPAuth   = true;
            $mail->Username   = 'inscripciones@asoani.org';  // Cambia esto a tu dirección de correo
            $mail->Password   = 'Asoani20$3';  // Cambia esto a tu contraseña de correo
            $mail->SMTPSecure = false; //PHPMailer::ENCRYPTION_STARTTLS; // Opciones: 'ssl', 'tls', o false para ninguna
            $mail->Port       = 26; // Puerto SMTP externo, puede variar según tu proveedor de correo

            // Resto de la configuración del correo
            $mail->setFrom('inscripciones@edu.asoani.org', 'ASOANI');
            $mail->addAddress($email, 'Destinatario');
            $mail->isHTML(true);
            $mail->Subject = 'Inscripcion exitosa - Cena Tropical';
            $mail->Body    = file_get_contents(__DIR__.'/plantilla-correo-cena.html');

            // Adjunta la imagen
            $mail->addAttachment($qrImagePath);

            // Enviar el correo
            $mail->send();
            // Redireccionar al índice
            header("Location: https://www.edu.asoani.org"); // Cambia esto a la URL a la que deseas redirigir
        } catch (Exception $e) {
            echo "Error al enviar el correo: {$mail->ErrorInfo}";
        }
        // Redireccionar al índice
        header("Location: https://www.edu.asoani.org"); // Cambia esto a la URL a la que deseas redirigir
        exit(); // Asegurarse de que el script se detiene después de la redirección
    } else {
        // Error en la inserción
        echo "Error en el registro: " . $stmt->error;
    }

    // Cierra la conexión y la sentencia
    $stmt->close();
    $conexion->close();
}

?>
