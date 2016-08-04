<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileType;
use phpseclib\Net\SFTP as SFTPClient;

class SFTP extends Base
{
    private $host, $username, $password, $port, $timeout = 30;
    /** @var  \phpseclib\Net\SFTP */
    private $sftpClient;

    public function __construct(string $host, string $username, string $password, int $port = 22)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;

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

    public function push(string $local, string $remote)
    {
        $this->sftpClient->put($remote, $local, SFTPClient::SOURCE_LOCAL_FILE);
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        $bytes = intval($this->sftpClient->filesize($remote));
        $changedate = intval($this->sftpClient->filemtime($remote));
        $sftpType = $this->sftpClient->filetype($remote);
        $type = $this->parseSFTPTypeToFileType($sftpType);

        //TODO: separate when only 1 of the stats is needed
        //TODO: try  $this->sftpClient->stat()
        $stat = new FileStat($remote);
        $stat->setBytes($bytes);
        $stat->setChangeDate($changedate);
        $stat->setType($type);

        return $stat;
    }

    private function parseSFTPTypeToFileType(string $sftpType): FileType
    {
        $parsedtype = FileType::Unknown();
        switch ($sftpType) {
            case '':
                break;
            default:
                throw new \Exception('Unknown SFTP file type "' . $sftpType . '"');
        }

        return $parsedtype;
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
