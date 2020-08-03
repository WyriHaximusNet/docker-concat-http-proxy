<?php declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Browser;
use React\Http\Message\Response;
use React\Http\Server as HttpServer;
use React\Promise\PromiseInterface;
use React\Socket\Server as SocketServer;
use function React\Promise\all;

require 'vendor/autoload.php';

$loop = Factory::create();

$browser = new Browser($loop);
$socketServer = new SocketServer('0.0.0.0:33333', $loop);
$httpServer = new HttpServer(
    $loop,
    static function (ServerRequestInterface $request) use ($browser): PromiseInterface {
        $requests = [];

        foreach (explode(',', getenv('URLS')) as $url) {
            $requests[] = $browser->get($url . $request->getUri()->getPath() . '?' . $request->getUri()->getQuery());
        }

        return all($requests)->then(function (array $responses) {
            $body = '';

            /** @var ResponseInterface $response */
            foreach ($responses as $response) {
                $body .= (string)$response->getBody() . PHP_EOL;
            }

            return new Response(200, [], $body);
        });
    }
);
$httpServer->listen($socketServer);
$httpServer->on('error', function (Throwable $throwable) {
    echo $throwable;
});

$loop->run();
