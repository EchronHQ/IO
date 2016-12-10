<?php
declare(strict_types = 1);

namespace Echron\IO\Data;

class Capabilities
{
    private $pull = false, $push = false, $modifyDate = false, $delete = false, $move = false, $copy = false;

    public function canPull(): bool
    {
        return $this->pull;
    }

    public function setCanPull(bool $pull)
    {
        $this->pull = $pull;
    }

    public function canPush(): bool
    {
        return $this->push;
    }

    public function setCanPush(bool $push)
    {
        $this->push = $push;
    }

    public function canChangeModifyDate(): bool
    {
        return $this->modifyDate;
    }

    public function setCanChangeModifyDate(bool $modifyDate)
    {
        $this->modifyDate = $modifyDate;
    }

    public function canDelete(): bool
    {
        return $this->delete;
    }

    public function setCanDelete(bool $delete)
    {
        $this->delete = $delete;
    }

    public function canMove(): bool
    {
        return $this->move;
    }

    public function setCanMove(bool $move)
    {
        $this->move = $move;
    }

    public function canCopy(): bool
    {
        return $this->copy;
    }

    public function setCanCopy(bool $copy)
    {
        $this->copy = $copy;
    }

}
