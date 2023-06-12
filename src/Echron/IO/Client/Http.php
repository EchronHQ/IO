<?php

declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;

use function is_null;

class Http extends Base
{
    private GuzzleClient $guzzleClient;

    private array|null $basicAuth = null;

    public function __construct(array $guzzleClientConfig = [])
    {
        $this->guzzleClient = new GuzzleClient($guzzleClientConfig);
    }

    public function setBasicAuth(string $username, string $password): void
    {
        $this->basicAuth = [
            $username,
            $password,
        ];
    }

    public function pull(string $remote, string $local, int $localChangeDate = null): FileTransferInfo
    {
        $options = [];
        if (!is_null($this->basicAuth)) {
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

        $response = $this->guzzleClient->request('GET', $remote, $options);
        $contents = $response->getBody()
            ->getContents();
        $this->setLocalFileContent($local, $contents);

        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null): FileTransferInfo
    {
        throw new Exception('Not implemented');
    }

    public function delete(string $remote): bool
    {
        throw new Exception('Not implemented');
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $stat = new FileStat($remote);

        try {
            $options = [];
            if (!is_null($this->basicAuth)) {
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
        } catch (Exception $ex) {
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

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }

    public function getClient(): ClientInterface
    {
        return $this->guzzleClient;
    }

}
