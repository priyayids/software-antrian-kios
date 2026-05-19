<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

function cetak($noAntrian): bool
{
    $connector = null;
    $printer = null;

    $printerHost  = getenv('PRINTER_HOST');
    $printerShare = getenv('PRINTER_SHARE');
    $printerUser  = getenv('PRINTER_USER');
    $printerPass  = getenv('PRINTER_PASSWORD');

    // LOG: env values
    error_log("[PRINTER] HOST: " . ($printerHost ?: 'empty'));
    error_log("[PRINTER] SHARE: " . ($printerShare ?: 'empty'));
    error_log("[PRINTER] USER: " . ($printerUser ?: 'empty'));
    error_log("[PRINTER] PASS: " . ($printerPass ? '***set***' : 'empty'));

    $useNetwork = !empty($printerHost);
    $useWindows = !empty($printerShare);

    error_log("[PRINTER] Mode: " . ($useNetwork ? 'NETWORK' : ($useWindows ? 'WINDOWS SMB' : 'NONE')));

    if (!$useNetwork && !$useWindows) {
        error_log("[PRINTER] ERROR: No printer configured, both HOST and SHARE are empty");
        return false;
    }

    try {
        if ($useNetwork) {
            $printerPort = (int)(getenv('PRINTER_PORT') ?: '9100');
            error_log("[PRINTER] Connecting via Network: $printerHost:$printerPort");
            $connector = new NetworkPrintConnector($printerHost, $printerPort);
        } else {
            if (!empty($printerUser) && !empty($printerPass)) {
                $smbUrl = "smb://" . urlencode($printerUser) . ":" . urlencode($printerPass) . "@host.docker.internal/" . $printerShare;
                error_log("[PRINTER] Connecting via SMB with credentials: smb://" . urlencode($printerUser) . ":***@host.docker.internal/" . $printerShare);
            } else {
                $smbUrl = "smb://host.docker.internal/" . $printerShare;
                error_log("[PRINTER] Connecting via SMB without credentials: $smbUrl");
            }
            $connector = new WindowsPrintConnector($smbUrl);
        }

        error_log("[PRINTER] Connector created, initializing printer...");
        $printer = new Printer($connector);
        error_log("[PRINTER] Printer initialized, starting print job...");

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setFont(Printer::FONT_A);
        $printer->setTextSize(2, 1);
        $printer->text("PT NISCAYA UNGGUL NUSANTARA\n");
        $printer->selectPrintMode();

        $printer->setFont(Printer::FONT_B);
        $printer->setTextSize(1, 1);
        $printer->text("Rukan graha mas Jl. Pejuangan No.C 11,\n");
        $printer->text("RT.1/RW.7, Kebon Jeruk, Kebonjeruk,\n");
        $printer->text("West Jakarta City, Jakarta 11520\n");
        $printer->feed();

        $printer->setEmphasis(true);
        $printer->setFont(Printer::FONT_B);
        $printer->setTextSize(2, 1);
        $printer->text("NOMOR ANTRIAN ANDA\n");
        $printer->selectPrintMode();

        $printer->setEmphasis(true);
        $printer->setFont(Printer::FONT_A);
        $printer->setTextSize(6, 6);
        $printer->text($noAntrian . "\n\n");
        $printer->selectPrintMode();

        $printer->setFont(Printer::FONT_B);
        $printer->setTextSize(1, 1);
        $printer->text("Silakan menunggu hingga nomor antrian\n");
        $printer->text("Anda dipanggil.\n");
        $printer->text("Nomor ini hanya berlaku pada hari ini.\n");
        $printer->feed();

        $printer->setFont(Printer::FONT_B);
        $printer->setTextSize(1, 1);
        $printer->text(hariIndo(date('l')) . ", " . date('d') . " " . bulanIndo(date('m')) . " " . date('Y') . "\n");
        $printer->feed();

        $printer->setEmphasis(true);
        $printer->setFont(Printer::FONT_B);
        $printer->setTextSize(1, 1);
        $printer->text("TERIMA KASIH\n");
        $printer->selectPrintMode();

        $printer->feed(3);
        $printer->cut();
        $printer->close();

        error_log("[PRINTER] Print job completed successfully!");
        return true;

    } catch (\Exception $e) {
        error_log("[PRINTER] FAILED: " . $e->getMessage());
        error_log("[PRINTER] Stack trace: " . $e->getTraceAsString());
        if ($printer) {
            try {
                $printer->close();
            } catch (\Exception $e) {
                error_log("[PRINTER] Failed to close printer: " . $e->getMessage());
            }
        }
        return false;
    }
}