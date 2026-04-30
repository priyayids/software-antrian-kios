<?php

require __DIR__ . '/../../vendor/autoload.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

function cetak($noAntrian): bool
{
    $connector = null;
    $printer = null;

    try {
        $connector = new WindowsPrintConnector("smb://host.docker.internal/pos-80");
       # $connector = new NetworkPrintConnector("192.168.1.50", 9100);
        $printer = new Printer($connector);

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

        return true;
    } catch (\Exception $e) {
        error_log("Printer error: " . $e->getMessage());
        if ($printer) {
            try {
                $printer->close();
            } catch (\Exception $e) {
            }
        }
        return false;
    }
}
