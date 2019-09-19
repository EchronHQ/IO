<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use Exception;
use InvalidArgumentException;
use League\Flysystem\Adapter\Ftp;
use function is_null;

class FtpClient extends Base
{
    protected $_connection;
    protected $_host, $_username, $_password, $_port, $_passive = false, $_timeout = 10;

    protected $connected = false;
    protected $cachedFileInfo = [];
    /** @var Ftp */
    private $client;

    public function __construct(
        string $host,
        string $username,
        string $password,
        int $port = 21,
        bool $passive = false,
        bool $autoConnect = false
    ) {
        $this->_host = $host;
        $this->_username = $username;
        $this->_password = $password;
        $this->_port = $port;

        $this->_passive = $passive;
        // $this->enableDebug();

        $port = $this->_validatePort($port);
        if ($port !== false) {
            $this->_port = $port;
        }

        $config = [
            'host'     => $this->_host,
            'username' => $this->_username,
            'password' => $this->_password,

            /** optional config settings */
            'port'     => $this->_port,
            'root'     => '/',
            'passive'  => $this->_passive,
            //'ssl' => true,
            'timeout'  => 10,
        ];
        $this->client = new Ftp($config);
        $this->client->setTimeout(5);

        //$this->client->setRoot('/');

        if ($autoConnect) {
            $this->connect();
        }
    }

    private function _validatePort($port)
    {
        if (is_int($port)) {
            return $port;
        } else {
            if (is_string($port)) {
                $port = intval($port);
                if ($port === 0) {
                    throw new InvalidArgumentException('Port should be an integer');
                }
            }
        }

        return false;
    }

    private function connect()
    {
        $this->client->getConnection();
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null)
    {
        // TODO: Implement push() method.
        throw new Exception('Not implemented');
        //        if (!\is_null($setRemoteChangeDate)) {
        //            //TODO: can we set the remote change date when putting an object?
        //            $this->setRemoteChangeDate($remote, $setRemoteChangeDate);
        //        }
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $this->connect();
        $stat = new FileStat($remote, FileType::File());
        $stat->setExists(false);

        $timeStamp = $this->client->getTimestamp($remote);
        if ($timeStamp !== false) {
            $stat->setChangeDate($timeStamp['timestamp']);
            $stat->setExists(true);
        }
        $metaData = $this->client->getMetadata($remote);
        if ($metaData !== null && $metaData !== false) {
            if ($metaData['type'] === 'file') {
                $stat->setType(FileType::File());
                $stat->setBytes($metaData['size']);
            }
        }

        return $stat;
    }

    public function remoteFileExists(string $remote): bool
    {
        // TODO: Implement remoteFileExists() method.
        throw new Exception('Not implemented');
    }

    public function pull(string $remote, string $local, int $localChangeDate = null)
    {
        $data = $this->client->read($remote);

        $contents = $data['contents'];

        $this->setLocalFileContent($local, $contents);
        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }
    }

    public function delete(string $remote)
    {
        // TODO: Implement delete() method.
        throw new Exception('Not implemented');
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        // TODO: Implement setRemoteChangeDate() method.
        throw new Exception('Not implemented');
    }
}
