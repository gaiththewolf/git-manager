<?php

namespace Gaiththewolf\GitManager\Data;

class GitStatus
{

    public string $branch;
    public bool $isUpToDate;
    public bool $isPush;
    public bool $isPull;

    public function __construct(string $branch, bool $isUpToDate, bool $isPush, bool $isPull)
    {
        $this->branch = $branch;
        $this->isUpToDate = $isUpToDate;
        $this->isPush = $isPush;
        $this->isPull = $isPull;
    }

    public function getBranch() : string
    {
        return $this->branch;
    }
    public function setBranch(string $value)
    {
        $this->branch = $value;
    }

    public function getIsUpToDate() : bool
    {
        return $this->isUpToDate;
    }
    public function setIsUpToDate(bool $value)
    {
        $this->isUpToDate = $value;
    }

    public function getIsPush() : bool
    {
        return $this->isPush;
    }
    public function setIsPush(bool $value)
    {
        $this->isPush = $value;
    }

    public function getIsPull() : bool
    {
        return $this->isPull;
    }
    public function setIsPull(bool $value)
    {
        $this->isPull = $value;
    }
}
