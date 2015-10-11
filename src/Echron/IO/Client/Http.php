<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\AbstractIOClient;
use Echron\IO\Data\FileStat;
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
        throw new \Exception('Not implemented');
    }

    public function getRemoteSize(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
                    ->getBytes();
    }

    private function getRemoteFileStat(string $remote): FileStat
    {
        $stat = new FileStat($remote);

        try {
            $fileHeadResponse = $this->guzzleClient->head($remote, []);

            switch ($fileHeadResponse->getStatusCode()) {
                case 200:

                    if ($fileHeadResponse->hasHeader('Last-Modified')) {
                        $lastModified = $fileHeadResponse->getHeaderLine('Last-Modified');
                        $lastModified = strtotime($lastModified);
                        $stat->setChangeDate($lastModified);
                    } else {
                        if ($fileHeadResponse->hasHeader('Date')) {
                            $lastModified = $fileHeadResponse->getHeaderLine('date');
                            $lastModified = strtotime($lastModified);
                            $stat->setChangeDate($lastModified);
                        }
                    }

                    if ($fileHeadResponse->hasHeader('Content-Length')) {
                        $size = $fileHeadResponse->getHeaderLine('Content-Length');
                        $size = intval($size);
                        $stat->setBytes($size);
                    }
                    break;
                case 401:
                case 404:
                    break;
            }
        } catch (\Exception $ex) {

        }

        return $stat;
    }

    public function getLocalSize(string $local): int
    {
        throw new \Exception('Not implemented');
    }

    public function getRemoteChangeDate(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
                    ->getChangeDate();
    }

    public function getLocalChangeDate(string $local): int
    {
        throw new \Exception('Not implemented');
    }
}
