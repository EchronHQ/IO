<?php

declare(strict_types=1);

namespace Echron\IO\Data;

class FileTransferInfo
{
    private bool $success;
    private int $bytesTransferred;
    private bool $wasLazyTransfer = false;
    private bool $transferWasNeeded = true;

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
        return $this->success;
    }

    public function bytesTransferred(): int
    {
        return $this->bytesTransferred;
    }

    public function transferWasLazy(): bool
    {
        return $this->wasLazyTransfer;
    }

    public function transferWasNeeded(): bool
    {
        return $this->transferWasNeeded;
    }
}
