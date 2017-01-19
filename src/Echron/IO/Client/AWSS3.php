<?php
declare(strict_types = 1);
namespace Echron\IO\Client;

use Echron\IO\Data\FileStat;
use phpseclib\Net\SFTP as SFTPClient;

/**
 * http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/basic-usage.html
 */
class AWSS3 extends Base
{

    public function push(string $local, string $remote)
    {
        // TODO: Implement push() method.
    }

    public function getRemoteFileStat(string $remote): FileStat
    {
        // TODO: Implement getRemoteFileStat() method.
    }

    public function remoteFileExists(string $remote): bool
    {
        // TODO: Implement remoteFileExists() method.
    }

    public function pull(string $remote, string $local)
    {
        // TODO: Implement pull() method.
    }

    public function delete(string $remote)
    {
        // TODO: Implement delete() method.
    }

    public function setRemoteChangeDate(string $remote, int $changeDate)
    {
        // TODO: Implement setRemoteChangeDate() method.
    }
}
