<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP as SFTPClient;

class SFTP extends Base
{
    private $host, $username, $password, $port, $timeout = 30;
    /** @var  \phpseclib\Net\SFTP */
    private $sftpClient;

    public function __construct(string $host, int $port = 22)
    {
        $this->host = $host;
        $this->port = $port;
    }

    public function loginWithPassword(string $username, string $password = null)
    {
        $this->username = $username;
        $this->password = $password;
        //TODO: lazy connection
        $this->initClient();
    }

    public function loginWithKey(string $username, string $keyFilePath, string $keyFilePassword = null)
    {
        if (!\file_exists($keyFilePath)) {
            throw new \Exception('Key file does not exist');
        }
        $key = new RSA();
        if (!\is_null($keyFilePassword)) {
            $key->setPassword($keyFilePassword);
        }
        $key->loadKey(\file_get_contents($keyFilePath));

        $this->username = $username;
        $this->password = $key;
        //TODO: lazy connection
        $this->initClient();
    }

    private function initClient()
    {
        $this->sftpClient = new SFTPClient($this->host, $this->port, $this->timeout);
        $authenticated = $this->sftpClient->login($this->username, $this->password);
        if (!$authenticated) {
            $this->sftpClient = null;
            throw new \Exception('Unable to login: not authenticated 2 `' . $this->username . '@' . $this->host . ':' . $this->port . '`');
        }
    }

    public function push(string $local, string $remote): bool
    {
        if (!\file_exists($local)) {
            throw new \Exception('Unable to push, local file does not exist');
        }
        //Create directory

        $directory = dirname($remote);

        $this->sftpClient->mkdir($directory, -1, true);

        $res = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE);

        return true;

//        $res = $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE, -1, -1, function ($sftp_packet_size) {
//            echo 'X: ' . $sftp_packet_size . \PHP_EOL;
//
//        });
//        var_dump($res);

    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $sftpType = $this->sftpClient->filetype($remote);

        $stat = new FileStat($remote);
        if ($sftpType) {
            $type = $this->parseSFTPTypeToFileType($sftpType);

            //TODO: separate when only 1 of the stats is needed
            //TODO: try  $this->sftpClient->stat()

            $bytes = intval($this->sftpClient->filesize($remote));
            $changedate = intval($this->sftpClient->filemtime($remote));

            $stat->setExists(true);
            $stat->setBytes($bytes);
            $stat->setChangeDate($changedate);
            $stat->setType($type);
        } else {
            $stat->setExists(false);
        }

        return $stat;
    }

    private function parseSFTPTypeToFileType(string $sftpType): FileType
    {
        $parsedType = FileType::Unknown();
        switch ($sftpType) {
            case 'file':
                $parsedType = FileType::File();
                break;
            case 'dir':
                $parsedType = FileType::Dir();
                break;
            default:
                throw new \Exception('Unknown SFTP file type "' . $sftpType . '"');
        }

        return $parsedType;
    }

    public function delete(string $remote)
    {
        $this->sftpClient->delete($remote);
    }

    public function remoteFileExists(string $remote): bool
    {
        return $this->sftpClient->file_exists($remote);
    }

    public function pull(string $remote, string $local)
    {
        return $this->sftpClient->get($remote, $local);
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        return $this->sftpClient->touch($remote, $changeDate);
    }
}
