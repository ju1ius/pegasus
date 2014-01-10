<?php

require_once __DIR__.'/../../vendor/autoload.php';

use ju1ius\Pegasus\Grammar;
use ju1ius\Pegasus\Expression;
use ju1ius\Pegasus\Parser\LRPackrat;
use ju1ius\Pegasus\Parser\MemoEntry;
use ju1ius\Pegasus\Parser\LR;
use ju1ius\Pegasus\Parser\Head;


class Parser extends LRPackrat
{
    public function apply(Expression $expr, $pos)
    {
        if ($m = $this->memo_lr($expr, $pos)) {
            return $this->recall($m, $expr);
        }
        $lr = new LR($expr);
        $this->lr_stack->push($lr);
        $m = $this->inject_memo($expr, $pos, $lr, $pos);
        $result = $this->evaluate($expr);
        $this->lr_stack->pop();
        if ($lr->head) {
            $m->end = $this->pos;
            $lr->seed = $result;
            return $this->lr_answer($expr, $pos, $m);
        }
        return $this->save($m, $result);
    }
    public function memo_lr(Expression $expr, $pos)
    {
        $m = $this->memo($expr, $pos);
        if (!isset($this->heads[$pos])) return $m;
        $head = $this->heads[$pos];
        if (!$m && !$head->involves($expr)) {
            return $this->inject_fail($expr, $pos);
        }
        if (isset($head->eval[$expr->id])) {
            unset($head->eval[$expr->id]);
            $this->save($m, $this->evaluate($expr));
        }
        return $m;
    }
    public function recall(MemoEntry $m, Expression $expr)
    {
        if ($m->result instanceof LR) {
            $this->setup_lr($expr, $m->result);
            return $m->result->seed;
        }
        $this->pos = $m->end;
        return $m->result;
    }
    public function setup_lr(Expression $expr, LR $lr)
    {
        if (!$lr->head) {
            $lr->head = new Head($expr);
        }
        foreach ($this->lr_stack as $item) {
            if ($item->head === $lr->head) return;
            $lr->head->involved[$item->rule->id] = $item->rule;
        }
    }
    public function lr_answer(Expression $expr, $pos, MemoEntry $m)
    {
        $head = $m->result->head;
        if ($head->rule->id === $expr->id) {
            $m->result = $m->result->seed;
            if (!$m->result) return;
            return $this->grow_lr($expr, $pos, $m, $head);
        }
        return $this->save($m, $m->result->seed);
    }
    public function grow_lr(Expression $expr, $pos, MemoEntry $m, Head $head)
    {
        $this->heads[$pos] = $head;
        while (true) {
            $this->pos = $pos;
            $head->eval = $head->involved;
            $result = $this->evaluate($expr);
            if (!$result || $this->pos <= $m->end) {
                unset($this->heads[$pos]);
                return $this->recall($m, $expr);
            }
            $this->save($m, $result);
        }
    }
    public function save(MemoEntry $m, $result)
    {
        $m->end = $this->pos;
        return $m->result = $result;
    }
    public function inject_memo(Expression $expr, $start, $result, $end)
    {
        return $this->memo[$expr->id][$start] = new MemoEntry($result, $end);
    }
    public function inject_fail(Expression $expr, $pos)
    {
        return $this->memo[$expr->id][$pos] = new MemoEntry(null, $pos);
    }
     
    public function evaluate(Expression $expr)
    {
        $name = $expr->name ?: get_class($expr);
        echo "Trying rule $name @ {$this->pos}\n";
        $result = parent::evaluate($expr);
        echo "$name => $result\n";
        return $result;
    }
    
}

$syntax = <<<'EOS'
x = x '-' num | x '+' num | num
#minus = x '-' num
#plus = x '+' num
#x = minus | plus | num
minus = x '-' num
plus = x '+' num
num = /-?[0-9]+/
EOS;
$grammar = new Grammar($syntax);
$parser = new Parser($grammar);
$tree = $parser->parse('1+2');
echo $tree->treeview(), "\n";
