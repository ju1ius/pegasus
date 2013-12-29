<?php

namespace ju1ius\Pegasus;


/**
 * Class PackratCache
 * @author ju1ius
 */
class PackratCache
{
    public function __construct($options)
    {
        $this->heads = [];
        $this->lr_stack = [];
    }
    public function recall($m, $expr)
    {
        $result = $m->result;
        if ()
    }
    
    public function lr($seed, $expr, $head)
    {
        return [
            'seed' => $seed,
            'expr' => $expr,
            'head' => $head
        ];
    }
    public function head($expr)
    {
        return [
            'expr' => $expr,
            'involved' => [],
            'eval' => []
        ];
    }
    public function headInvolvesExpr($head, $expr)
    {
        return $expr->id === $head['expr']->id
            || isset($head['involved'][$expr->id]);
    }
    public function setup_lr($expr, $lr)
    {
        if(!$lr['head']) $lr['head'] = $this->head();
        foreach (array_reverse($this->lr_stack) as $value) {
            if ($value['head'] === $lr['head']) return;
            $lr['head']['involved'][$value['expr']] = $value['expr'];
        }
    }
    
    public function lr_answer($expr, $start_pos, $m)
    {
        $head = $m->result['head'];
        if ($head['expr'] === $expr) {
            $this->grow_lr($expr, $start_pos, $m, $head);
        } else {
            $this->save($m, $m->result['seed'])
        }
    }
    public function grow_lr($expr, $start_pos, $m, $head)
    {
        $this->heads[$start_pos] = $head;
        while (true) {
            $pos = $start_pos;
            $head['eval'] = $head['involved'];
            $result = $expr->_match();
            if (!$result || $pos < $m->end) {
                unset($this->heads[$start_pos]);
                return $this->recall($m, $expr);
            }
            $this->save($m, $result);
        }
    }
}

