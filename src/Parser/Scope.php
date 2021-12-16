<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Parser;

final class Scope
{
    /** @var array<string, string> */
    public array $bindings = [];
    public bool $isCapturing = true;

    public function capturing(): self
    {
        if ($this->isCapturing) return $this;
        $scope = clone $this;
        $scope->isCapturing = true;
        return $scope;
    }

    public function matching(): self
    {
        if (!$this->isCapturing) return $this;
        $scope = clone $this;
        $scope->isCapturing = false;
        return $scope;
    }
}
