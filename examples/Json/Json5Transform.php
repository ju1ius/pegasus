<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Examples\Json;


class Json5Transform extends JsonTransform
{
    protected function leave_string($node, $value)
    {
        return $node->attributes['groups'][2];
    }

    protected function leave_hex($node, $hex)
    {
        return hexdec($hex);
    }

    protected function leave_infinity($node, $inf)
    {
        if ($inf[0] === '-') return -INF;
        return INF;
    }

    protected function leave_nan($node, $_)
    {
        return NAN;
    }
}
