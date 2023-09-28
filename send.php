<?php

require __DIR__.'/PHPMailer/src/PHPMailer.php';
require __DIR__.'/PHPMailer/src/Exception.php';
require __DIR__.'/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

function mailtrap($mail) {
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Port = 2525;
    $mail->Username = '5b62bf74e151d3';
    $mail->SMTPSecure = false; //PHPMailer::ENCRYPTION_STARTTLS; // Opciones: 'ssl', 'tls', o false para ninguna
    $mail->Password = '********2efd';
}


//// Configuración de PHPMailer
//$mail = new PHPMailer(true);
//// Configurar el servidor SMTP externo y las credenciales
//$mail->isSMTP();
//$mail->Host       = 'mail.asoani.org';  // Cambia esto al servidor SMTP externo
//$mail->SMTPAuth   = true;
//$mail->Username   = 'inscripciones@asoani.org';  // Cambia esto a tu dirección de correo
//$mail->Password   = 'Asoani20$3';  // Cambia esto a tu contraseña de correo
//$mail->SMTPSecure = false; //PHPMailer::ENCRYPTION_STARTTLS; // Opciones: 'ssl', 'tls', o false para ninguna
//$mail->Port       = 26; // Puerto SMTP externo, puede variar según tu proveedor de correo

// Ruta al archivo CSV
$rutaArchivoCSV = __DIR__.'/csv-envio-2.csv'; // Cambia esto a la ruta de tu archivo CSV
$i = 93;

// Lee el archivo CSV
if (($handle = fopen($rutaArchivoCSV, 'r')) !== false) {
    while (($data = fgetcsv($handle, 0, ';')) !== false) {
        $colegiado = $data[0]; // Suponiendo que la primera columna contiene el número de colegiado
        $nombre = $data[1];    // Suponiendo que la segunda columna contiene el nombre
        $apellido = $data[2];  // Suponiendo que la tercera columna contiene el apellido
        $correo = $data[3];    // Suponiendo que la cuarta columna contiene el correo electrónico
        //$departamento = $data[4]; // Suponiendo que la quinta columna contiene el departamento
        $mail = new PHPMailer(true);
        // Verifica si el correo está vacío y omite el registro si es así
        if (empty($correo)) {
            echo "Correo vacío, omitiendo registro.\n" . PHP_EOL;
            continue;
        }

        $rutaImagenGen = $i . 'diplomas' . $i;

        $rutaImagen = __DIR__.'/diplomas/'. $rutaImagenGen . '.pdf'; // Cambia esto a la ruta de tu imagen

        // Configura el destinatario y el contenido del correo
        $mail->setFrom('inscripciones@edu.asoani.org', 'ASOANI Edu');
        $mail->addAddress($correo, $nombre);
        $mail->isHTML(true);
        $mail->Subject = ''; //Cambia esto al titulo del nuevo correo
        $mail->Body    = file_get_contents(__DIR__.'/plantilla-nueva.html');

        // Adjunta la imagen
        $mail->addAttachment($rutaImagen);

        // Envía el correo
        if ($mail->send()) {
            echo 'Correo enviado a ' . $correo . PHP_EOL . "\n";
        } else {
            echo 'Error al enviar el correo a ' . $correo . ': ' . $mail->ErrorInfo . PHP_EOL . "\n";
        }

        $i = $i + 1;
        // Limpiar los destinatarios y el contenido del correo para el siguiente bucle
        $mail->clearAddresses();
        $mail->clearAttachments();
    }
    fclose($handle);
} else {
    echo 'No se pudo abrir el archivo CSV.' . PHP_EOL;



///recibe el archivo csv asumiendo que fue enviado manualmente mediante un formulario y recibo un archivo file
    if (isset($_POST["submit"])) {

    $csv = $_FILES["csvfile"];

    // Verifico si no hubo error en obtener el archivo
    if ($csv["error"] === UPLOAD_ERR_OK) {
        $RutaCsv = $csv["plantilla-nueva"];
        $mail = new PHPMailer(true);
//Lee al archivo CSV
        if (($handle = fopen($RutaCsv, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {

                $email = $data[0]; // Asumiendo que el correo esta en la primera columna
                $name = $data[1]; // Asumiendo que el nombre esta en la segunda columna


                // Verifica si el correo esta vacio y omite el registro si es asi
                if (empty($email)) {
                    echo "Email is empty, skipping record.\n" . PHP_EOL;
                    continue;
                }

                // Genera QR
                $qr = "";

                // Send email
                try {
                    $mail->setFrom('your_email@example.com', 'tu nombre');
                    $mail->addAddress($email, $name . ' ' );
                    $mail->isHTML(true);
                    $mail->Subject = ''; // Cambia esto con el titulo
                    $mail->Body = 'Your email body content here'; // Reemplazalo con el contenido
                    //Se adjunta el codigo qr definiendo un path asumiendo que se sube como imagen
                    $mail->addAttachment('path/to/qr_code.png');

                    // Enviar el correo
                    if ($mail->send()) {
                        echo 'Correo enviado a ' . $email . PHP_EOL . "\n";
                    } else {
                        echo 'Error enviando el correo ' . $email . ': ' . $mail->ErrorInfo . PHP_EOL . "\n";
                    }

                    //Limpiar los destinatarios y el contenido del correo para el siguiente bucle
                    $mail->clearAddresses();
                    $mail->clearAttachments();
                } catch (Exception $e) {
                    echo 'Error enviando el correo: ' . $e->getMessage() . PHP_EOL . "\n";
                }
            }
            fclose($handle);
        } else {
            echo 'Error al abrir el archivo CSV.' . PHP_EOL;
        }
    } else {
        echo 'Error al cargar el archivo CSV.' . PHP_EOL;
    }
}}
?>

?>