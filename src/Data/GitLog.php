<?php

namespace Gaiththewolf\GitManager\Data;

class GitLog
{

    public string $hash;
    public ?array $merge;
    public array $author;
    public string $date;
    public string $message;

    public function __construct(?string $hash = "", ?array $merge = [], ?array $author = [], ?string $date = "", ?string $message = "")
    {
        $this->hash = $hash;
        $this->merge = $merge;
        $this->author = empty($author) ? ['full_name' => "", 'email' => ""] : $author;
        $this->date = $date;
        $this->message = $message;
    }

    public function isEmpty() : bool
    {
        return empty($this->hash) || $this->hash == null;
    }

    public function getHash() : string
    {
        return $this->hash;
    }
    public function setHash(string $value)
    {
        $this->hash = $value;
    }

    public function getMerge() : ?array
    {
        return $this->merge;
    }
    public function setMerge(?array $value)
    {
        $this->merge = $value;
    }

    public function getAuthor() : array
    {
        return $this->author;
    }
    public function setAuthor(array $value)
    {
        $this->author = $value;
    }

    public function getDate() : string
    {
        return $this->date;
    }
    public function setDate(string $value)
    {
        $this->date = $value;
    }

    public function getMessage() : string
    {
        return $this->message;
    }
    public function setMessage(string $value)
    {
        $this->message = $value;
    }
    public function appendMessage(string $value)
    {
        $this->message .= $value;
    }
}
