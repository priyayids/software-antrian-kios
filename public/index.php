<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = new App\Core\App();

$app->get('/', function () {
    $controller = new App\Controllers\HomeController();
    $controller->index();
});

$app->get('/nomor', function () {
    $controller = new App\Controllers\NomorController();
    $controller->index();
});

$app->get('/panggilan', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->index();
});

$app->get('/monitor', function () {
    $controller = new App\Controllers\MonitorController();
    $controller->index();
});

$app->get('/setting', function () {
    $controller = new App\Controllers\SettingController();
    $controller->index();
});

$app->post('/setting/login', function () {
    $controller = new App\Controllers\SettingController();
    $controller->login();
});

$app->post('/setting/logout', function () {
    $controller = new App\Controllers\SettingController();
    $controller->logout();
});

$app->get('/api/nomor/antrian', function () {
    $controller = new App\Controllers\NomorController();
    $controller->getAntrian();
});

$app->post('/api/nomor/insert', function () {
    $controller = new App\Controllers\NomorController();
    $controller->insert();
});

$app->get('/api/panggilan/antrian', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->getAntrian();
});

$app->get('/api/panggilan/sekarang', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->getAntrianSekarang();
});

$app->get('/api/panggilan/selanjutnya', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->getAntrianSelanjutnya();
});

$app->get('/api/panggilan/jumlah', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->getJumlahAntrian();
});

$app->get('/api/panggilan/sisa', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->getSisaAntrian();
});

$app->post('/api/panggilan/create', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->createPanggilan();
});

$app->post('/api/panggilan/update', function () {
    $controller = new App\Controllers\PanggilanController();
    $controller->update();
});

$app->any('/api/monitor/panggilan', function () {
    $controller = new App\Controllers\MonitorController();
    $controller->getPanggilan();
});

$app->post('/api/monitor/panggilan/delete', function () {
    $controller = new App\Controllers\MonitorController();
    $controller->deletePanggilan();
});

$app->post('/api/setting/save', function () {
    $controller = new App\Controllers\SettingController();
    $controller->save();
});

$app->run();
