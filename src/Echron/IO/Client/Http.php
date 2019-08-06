<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use GuzzleHttp\Client as GuzzleClient;

class Http extends Base
{
    private $guzzleClient;

    private $basicAuth;

    public function __construct(array $guzzleClientConfig = [])
    {
        $this->guzzleClient = new GuzzleClient($guzzleClientConfig);
    }

    public function setBasicAuth(string $username, string $password)
    {
        $this->basicAuth = [
            $username,
            $password,
        ];
    }

    public function pull(string $remote, string $local)
    {
        $options = [];
        if (!\is_null($this->basicAuth)) {
            $options['auth'] = $this->basicAuth;
        }

        //Progress
        //        $options['progress'] = function ($dl_total_size, $dl_size_so_far, $ul_total_size, $ul_size_so_far) {
        //            if ($dl_total_size !== 0) {
        //                $procent = (string)number_format($dl_size_so_far / $dl_total_size * 100, 2);
        //
        //                echo \str_pad($procent, 6, ' ', \STR_PAD_LEFT) . '%' . \PHP_EOL;
        //            }
        //        };

        $response = $this->guzzleClient->get($remote, $options);
        $fileContent = $response->getBody();
        file_put_contents($local, $fileContent);
    }

    public function push(string $local, string $remote)
    {
        throw new \Exception('Not implemented');
    }

    public function delete(string $remote)
    {
        throw new \Exception('Not implemented');
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $stat = new FileStat($remote);

        try {
            $options = [];
            if (!\is_null($this->basicAuth)) {
                $options ['auth'] = $this->basicAuth;
            }

            $fileHeadResponse = $this->guzzleClient->head($remote, $options);

            switch ($fileHeadResponse->getStatusCode()) {
                case 200:

                    $stat->setExists(true);

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
                default:
                    $this->logger->warning('Unknown status code "' . $fileHeadResponse->getStatusCode() . '"');
                    break;
            }
        } catch (\Exception $ex) {
            if ($this->logger) {
                $this->logger->error('Error while getting remote file stat: ' . $ex);
            }
        }

        return $stat;
    }

    public function remoteFileExists(string $remote): bool
    {
        $remoteFileStat = $this->getRemoteFileStat($remote);

        return $remoteFileStat->getExists();
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        throw new \Exception('Not implemented');
    }
}
