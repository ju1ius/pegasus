<?php declare(strict_types=1);

namespace ju1ius\Pegasus\Examples\Json;

class Json5Transform extends JsonTransform
{
    protected function leave_string($node, $value): string
    {
        return $value->attributes['groups'][2];
    }

    protected function leave_hex($node, $hex): float|int
    {
        return hexdec($hex);
    }

    protected function leave_infinity($node, $inf): float
    {
        if ($inf[0] === '-') return -INF;
        return INF;
    }

    protected function leave_nan($node, $_): float
    {
        return NAN;
    }
}
