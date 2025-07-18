<?php
$success = null;
$errorMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['ddsfile']) && $_FILES['ddsfile']['error'] === UPLOAD_ERR_OK) {

        $ddsFile = $_FILES['ddsfile']['tmp_name'];
        $fileName = $_FILES['ddsfile']['name'];
        // JANGAN DIUBAH, API INI PENTING!!
        // My Replit API Token
        $replitAPI = 'Your API Here';

        $curl = curl_init();
        $cfile = new CURLFile($ddsFile, 'application/octet-stream', $fileName);
        $data = ['ddsfile' => $cfile];

        curl_setopt_array($curl, [
            CURLOPT_URL => $replitAPI,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $errorMsg = "Gagal menghubungi API: " . curl_error($curl);
            curl_close($curl);
        } else {
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);

            curl_close($curl);

            if ($httpCode !== 200) {
                $errorMsg = "Fatal Error | API Tidak terhubung | err: $httpCode";
            } else {
                if (preg_match('/filename="([^"]+)"/', $headers, $matches)) {
                    $filename = $matches[1];
                } else {
                    $filename = "converted_" . time() . ".tex";
                }

                // Output file langsung
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"$filename\"");
                echo $body;
                exit;
            }
        }
    } else {
        $errorMsg = "Upload gagal atau file tidak valid.";
    }

    // Gagal > Balik ke html & **modal error**
    $success = false;
}
?>