<?php

require __DIR__ . '/vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class QueueServer implements MessageComponentInterface
{
    protected \SplObjectStorage $clients;
    private int $maxClients = 100;
    private array $clientData = [];

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        echo "[" . date('Y-m-d H:i:s') . "] WebSocket server initialized\n";
    }

    public function onOpen(ConnectionInterface $conn): void
    {
        if ($this->clients->count() >= $this->maxClients) {
            $conn->close();
            echo "[" . date('Y-m-d H:i:s') . "] Connection rejected: max clients reached\n";
            return;
        }

        $this->clients->attach($conn);
        $this->clientData[$conn->resourceId] = [
            'connected_at' => time(),
            'last_activity' => time(),
            'ip' => $conn->remoteAddress ?? 'unknown',
        ];

        echo "[" . date('Y-m-d H:i:s') . "] New connection! ({$conn->resourceId}) - Total: {$this->clients->count()}\n";

        $conn->send(json_encode([
            'type' => 'connected',
            'client_id' => $conn->resourceId,
            'message' => 'Connected to queue server',
        ]));
    }

    public function onMessage(ConnectionInterface $from, $msg): void
    {
        if (!isset($this->clientData[$from->resourceId])) {
            return;
        }

        $this->clientData[$from->resourceId]['last_activity'] = time();

        $data = json_decode($msg, true);
        $message = $data ?? ['raw' => $msg];

        echo "[" . date('Y-m-d H:i:s') . "] Message from {$from->resourceId}: " . ($data['type'] ?? 'unknown') . "\n";

        foreach ($this->clients as $client) {
            if ($client !== $from) {
                try {
                    $client->send($msg);
                } catch (\Exception $e) {
                    echo "[" . date('Y-m-d H:i:s') . "] Error sending to {$client->resourceId}: {$e->getMessage()}\n";
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn): void
    {
        $this->clients->detach($conn);
        unset($this->clientData[$conn->resourceId]);

        echo "[" . date('Y-m-d H:i:s') . "] Connection {$conn->resourceId} closed - Total: {$this->clients->count()}\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        echo "[" . date('Y-m-d H:i:s') . "] Error on {$conn->resourceId}: {$e->getMessage()}\n";
        $conn->close();
    }

    public function getStatus(): array
    {
        return [
            'total_clients' => $this->clients->count(),
            'max_clients' => $this->maxClients,
            'uptime' => time() - (min(array_column($this->clientData, 'connected_at')) ?: time()),
        ];
    }
}

$port = (int)(getenv('WS_PORT') ?: 8081);

echo "Starting WebSocket server on port {$port}...\n";

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new QueueServer()
        )
    ),
    $port
);

$server->run();
