<?php declare(strict_types=1);


namespace ju1ius\Pegasus\Debug;


use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\Trace\Trace;
use ju1ius\Pegasus\Trace\TraceEntry;
use ju1ius\Pegasus\Utils\Str;
use Symfony\Component\Console\Output\OutputInterface;


final class TraceDumper
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * GrammarDumper constructor.
     *
     * @param OutputInterface $output
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public static function dump(Trace $trace, OutputInterface $output)
    {
        (new self($output))->dumpTrace($trace);
    }

    public function dumpTrace(Trace $trace)
    {
        $source = $trace->getSource();
        $output = $this->output;

        /** @var TraceEntry $entry */
        foreach ($trace as $entry) {
            $result = $entry->result;
            $output->write(str_repeat('  ', $entry->depth));
            $pos = sprintf('[%d,%d] ', $entry->start, $entry->end);
            if ($result) {
                $output->write(sprintf('<success>%s</success>', $pos));
            } else {
                $output->write(sprintf('<failure>%s</failure>', $pos));
            }
            $output->write(Str::className($entry->expression));
            $output->write(': ');
            ExpressionHighlighter::highlight($entry->expression, $output);
            if ($result) {
                $output->write(' => ');
                if ($result instanceof Node) {
                    $this->dumpNode($result);
                } else {
                    $output->write(sprintf('<success>%s</success>', $result));
                }
            }
            $output->writeln('');
        }
    }

    private function dumpNode(Node $node): void
    {
        $class = Str::className($node, 1);
        $id = spl_object_id($node);
        $this->output->write(sprintf('%s#%s', $class, $id));
        if ($node->name) {
            $this->output->write(sprintf('(%s)', $node->name));
        }
        if ($node->value !== null) {
            $this->output->write(sprintf(': <success>%s</success>', $node->value));
        }
    }
}
