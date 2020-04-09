<?php
declare(strict_types=1);

namespace Echron\IO\Client;

use DateTime;
use Echron\IO\Data\FileStat;
use Echron\IO\Data\FileStatCollection;
use Echron\IO\Data\FileType;
use Echron\Tools\FileSystem;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use function file_exists;
use function file_put_contents;
use function in_array;

abstract class Base implements LoggerAwareInterface
{

    /** @var  LoggerInterface */
    protected $logger;

    abstract public function push(string $local, string $remote, int $setRemoteChangeDate = null);

    public function getLocalSize(string $local): int
    {
        return $this->getLocalFileStat($local)
                    ->getBytes();
    }

    private $localFileChanged = [];

    public function setLocalFileContent(string $local, string $contents)
    {
        file_put_contents($local, $contents);

        $this->localFileChanged[] = $local;
    }

    public function flushLocalStatCache(string $local = null)
    {
        clearstatcache(false, $local);
    }

    public function getLocalFileStat(string $local): FileStat
    {
        //TODO: we should not call this method every time we call this method
        if (in_array($local, $this->localFileChanged)) {
            $this->flushLocalStatCache($local);
        }

        $stat = new FileStat($local);
        if (file_exists($local)) {
            $fileModificationTime = filemtime($local);
            $fileSize = filesize($local);

            $stat->setExists(true);
            $stat->setChangeDate($fileModificationTime);
            $stat->setBytes($fileSize);
            //TODO: determine file type
            $stat->setType(FileType::File());
        }

        return $stat;
    }

    public function getRemoteChangeDate(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
                    ->getChangeDate();
    }

    abstract public function getRemoteFileStat(string $remote): FileStat;

    public function getRemoteSize(string $remote): int
    {
        return $this->getRemoteFileStat($remote)
                    ->getBytes();
    }

    public function getLocalChangeDate(string $local): int
    {
        return $this->getLocalFileStat($local)
                    ->getChangeDate();
    }

    public final function pullLazy(string $remote, string $local)
    {
        if (!$this->remoteFileExists($remote)) {
            throw new Exception('Unable to pull file: remote file `' . $remote . '` does not exist');
        } else {
            $remoteFileStat = $this->getRemoteFileStat($remote);
            $localFileStat = $this->getLocalFileStat($local);

            //TODO: when datetime is different or only when remote file is newer?
            if (!$localFileStat->equals($remoteFileStat)) {
                $downloaded = $this->pull($remote, $local, $remoteFileStat->getChangeDate());
                //                if ($downloaded) {
                //                $this->setLocalChangeDate($local, $remoteFileStat->getChangeDate());

                return true;
                //                } else {
                //                    return false;
                //                }
            } else {
                return true;
            }
        }
    }

    public final function pushLazy(string $local, string $remote)
    {
        if (!file_exists($local)) {
            throw new Exception('Unable to push file: local file `' . $local . '` does not exist');
        } else {
            $remoteFileStat = $this->getRemoteFileStat($remote);
            $localFileStat = $this->getLocalFileStat($local);

            //            echo 'Push lazy (' . $local . ' > ' . $remote . '):' . PHP_EOL . "\t" . 'Local:  ' . $localFileStat->debug() . PHP_EOL . "\t" . 'Remote: ' . $remoteFileStat->debug() . '' . PHP_EOL;

            //echo 'Lazy' . \PHP_EOL;

            if (!$remoteFileStat->equals($localFileStat)) {
                //                echo "\t" . 'Upload needed' . PHP_EOL;
                $uploaded = $this->push($local, $remote, $localFileStat->getChangeDate());
                // if ($uploaded) {
                //                    echo "\t" . 'Set change date' . \PHP_EOL;
                //                    $this->setRemoteChangeDate($remote, $localFileStat->getChangeDate());

                return true;
                //                } else {
                //                    return false;
                //                }
            } else {
                return true;
            }
        }
    }

    abstract public function remoteFileExists(string $remote): bool;

    abstract public function pull(string $remote, string $local, int $localChangeDate = null);

    public function setLocalChangeDate(string $local, int $changeDate)
    {
        FileSystem::touch($local, DateTime::createFromFormat('U', (string)$changeDate));
    }

    abstract public function delete(string $remote);

    abstract public function setRemoteChangeDate(string $remote, int $changeDate);

    public function removeLocal(string $local): bool
    {
        return unlink($local);
    }

    /**
     * @param string $remotePath
     * @param bool $recursive
     * @return FileStatCollection
     */
    abstract public function list(string $remotePath, bool $recursive = false): FileStatCollection;

    abstract public function deleteFile(string $remotePath): bool;

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
