<?php
declare(strict_types=1);

namespace Echron\IO\Data;

class FileTransferInfo
{
    private $success = false;
    private $bytesTransferred = -1;
    private $wasLazyTransfer = false;
    private $transferWasNeeded = true;

    public function __construct(bool $success, int $bytesTransferred = -1)
    {
        $this->success = $success;
        $this->bytesTransferred = $bytesTransferred;
    }

    public function setLazyTransfer(bool $wasLazyTransfer, bool $transferWasNeeded): void
    {
        $this->wasLazyTransfer = $wasLazyTransfer;
        $this->transferWasNeeded = $transferWasNeeded;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess();
    }

    public function bytesTransferred(): int
    {
        return $this->bytesTransferred();
    }
}
