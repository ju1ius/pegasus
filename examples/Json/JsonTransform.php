<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Examples\Json;

use ju1ius\Pegasus\CST\Transform;

class JsonTransform extends Transform
{
    protected function leave_object($node, $elements): array
    {
        return $elements ?: [];
    }

    protected function leave_members($node, $first, $others): array
    {
        $assoc = [$first[0] => $first[1]];
        foreach ($others as [$key, $value]) {
            $assoc[$key] = $value;
        }

        return $assoc;
    }

    protected function leave_array($node, $elements): array
    {
        return $elements;
    }

    protected function leave_elements($node, $first, $others): array
    {
        if (!$others) {
            return [$first];
        }
        return array_merge([$first], $others);
    }

    protected function leave_number($node, $number): float|int
    {
        // let PHP figure it out !
        return 0 + $number;
    }

    protected function leave_string($node, $value): string
    {
        return $value;
    }

    protected function leave_null($node, $value)
    {
        return null;
    }

    protected function leave_true($node, $value): bool
    {
        return true;
    }

    protected function leave_false($node, $value): bool
    {
        return false;
    }
}
