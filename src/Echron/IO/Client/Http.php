<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\AbstractIOClient;
use GuzzleHttp\Client as GuzzleClient;

class Http extends AbstractIOClient
{
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleClient();
    }

    public function pull(string $remote, string $local)
    {
        $options = [];
        $response = $this->guzzleClient->get($remote, $options);
        $fileContent = $response->getBody();
        file_put_contents($local, $fileContent);
    }

    public function push(string $local, string $remote)
    {
        // TODO: Implement push() method.

    }

    public function getRemoteSize(string $remote): int
    {
        // TODO: Implement getRemoteSize() method.
    }

    public function getLocalSize(string $local): int
    {
        // TODO: Implement getLocalSize() method.
    }

    public function getRemoteChangeDate(string $remote): int
    {
        // TODO: Implement getRemoteChangeDate() method.
    }

    public function getLocalChangeDate(string $local): int
    {
        // TODO: Implement getLocalChangeDate() method.
    }
}
