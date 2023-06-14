<?php

namespace Gaiththewolf\GitManager\Data;

class GitPull
{

    public array $files;
    public bool $isUpToDate;
    public string $changed;
    public string $insertion;
    public string $deletion;

    public function __construct(array $files, bool $isUpToDate, string $changed, string $insertion, string $deletion)
    {
        $this->files = $files;
        $this->isUpToDate = $isUpToDate;
        $this->changed = $changed;
        $this->insertion = $insertion;
        $this->deletion = $deletion;
    }

    public function getIsUpToDate() : bool
    {
        return $this->isUpToDate;
    }
    public function setIsUpToDate(bool $value)
    {
        $this->isUpToDate = $value;
    }

    public function getFiles() : array
    {
        return $this->files;
    }
    public function setFiles(array $value)
    {
        $this->files = $value;
    }

    public function getChanged() : string
    {
        return $this->changed;
    }
    public function setChanged(string $value)
    {
        $this->changed = $value;
    }

    public function getInsertion() : string
    {
        return $this->insertion;
    }
    public function setInsertion(string $value)
    {
        $this->insertion = $value;
    }

    public function getDeletion() : string
    {
        return $this->deletion;
    }
    public function setDeletion(string $value)
    {
        $this->deletion = $value;
    }
}
