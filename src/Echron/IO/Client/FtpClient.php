<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileTransferInfo;
use Echron\IO\Data\FileType;
use Exception;

use League\Flysystem\Ftp\FtpAdapter;
use League\Flysystem\Ftp\FtpConnectionOptions;
use function is_null;

class FtpClient extends Base
{
    protected int $_timeout = 10;
    protected bool $_passive = false;
    protected string $_username;
    protected int $_port;
    protected string $_password;
    protected string $_host;

    protected bool $connected = false;
    private FtpAdapter $client;

    public function __construct(
        string $host,
        string $username,
        string $password,
        int    $port = 21,
        bool   $passive = false,
        bool   $autoConnect = false
    )
    {
        $this->_host = $host;
        $this->_username = $username;
        $this->_password = $password;
        $this->_port = $port;

        $this->_passive = $passive;
        // $this->enableDebug();


        $config = FtpConnectionOptions::fromArray(
            [
                'host'     => $this->_host,
                'username' => $this->_username,
                'password' => $this->_password,

                /** optional config settings */
                'port'     => $this->_port,
                'root'     => '/',
                'passive'  => $this->_passive,
                //'ssl' => true,
                'timeout'  => $this->_timeout,
            ]
        );
        $this->client = new FtpAdapter($config);
//        $this->client->setTimeout(5);

        //$this->client->setRoot('/');

        if ($autoConnect) {
            $this->connect();
        }
    }


    private function connect()
    {
//        $this->client->getConnection();
    }

    public function push(string $local, string $remote, int $setRemoteChangeDate = null): FileTransferInfo
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

        $timeStamp = $this->client->lastModified($remote)->lastModified();
        if ($timeStamp !== null) {
            $stat->setChangeDate($timeStamp);
            $stat->setExists(true);
        }
        $metaData = $this->client->fileSize($remote);
        if ($metaData->fileSize() !== null) {
            $stat->setBytes($metaData->fileSize());
            // TODO: how do we know the type (ATTRIBUTE_TYPE)
//            if ($metaData['type'] === 'file') {
//                $stat->setType(FileType::File());
//                $stat->setBytes($metaData['size']);
//            }
        }

        return $stat;
    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->client->fileExists($remote);
    }

    public function pull(string $remote, string $local, int $localChangeDate = null): FileTransferInfo
    {
        $data = $this->client->read($remote);

        if (\is_array($data)) {
            if (isset($data['contents'])) {
                $contents = $data['contents'];
            } else {
                throw new Exception('Unable to pull file (content is not defined)');
            }
        } else {
            $contents = $data;
        }


        $this->setLocalFileContent($local, $contents);
        if (!is_null($localChangeDate)) {
            $this->setLocalChangeDate($local, $localChangeDate);
        }

        // TODO: determine transferred bytes
        return new FileTransferInfo(true);
    }

    public function delete(string $remote): bool
    {
        $this->client->delete($remote);
        return true;
    }

    public function setRemoteChangeDate(string $remote, int $changeDate): bool
    {
        // TODO: Implement setRemoteChangeDate() method.
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function list(string $remotePath, bool $recursive = false): FileStatCollection
    {
        throw new Exception('Not implemented');
    }
}
