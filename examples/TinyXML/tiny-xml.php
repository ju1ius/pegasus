<?php declare(strict_types=1);

use ju1ius\Pegasus\CST\Node;
use ju1ius\Pegasus\CST\Transform;
use ju1ius\Pegasus\Debug\Debug;
use ju1ius\Pegasus\GrammarBuilder;
use ju1ius\Pegasus\Parser\RecursiveDescentParser;

require_once __DIR__ . '/../../vendor/autoload.php';

$builder = GrammarBuilder::create('TinyXML');

$builder->rule('XML')->sequence()
    ->optional()->reference('ws')
    ->reference('element')
    ->optional()->reference('ws')
;
$builder->rule('element')->oneOf()
    ->reference('void_element')
    ->reference('non_void_element')
;
$builder->rule('content')->zeroOrMore()->oneOf()
    ->reference('pcdata')
    ->reference('element')
;
$builder->rule('non_void_element')->sequence()
    ->reference('start_tag')
    ->reference('content')
    ->reference('end_tag')
;
$builder->rule('void_element')->sequence()
    ->ignore()->literal('<')
    ->match('\w+')
    ->optional()->reference('attributes')
    ->ignore()->literal('/>')
;
$builder->rule('start_tag')->sequence()
    ->ignore()->literal('<')
    ->match('[\w-]+')
    ->optional()->reference('attributes')
    ->ignore()->literal('>')
;
$builder->rule('end_tag')->sequence()
    ->ignore()->literal('</')
    ->match('[\w-]+')
    ->ignore()->literal('>')
;
$builder->rule('pcdata')
    ->match('[^<]+')
;
$builder->rule('attributes')->sequence()
    ->ignore()->match('\s+')
    ->reference('attr_list')
;
$builder->rule('attr_list')->sequence()
    ->reference('attr')
    ->zeroOrMore()->sequence()
        ->ignore()->match('\s+')
        ->reference('attr')
;
$builder->rule('attr')->sequence()
    ->match('[\w-]+')
    ->ignore()->literal('=')
    ->reference('attr_value')
;
$builder->rule('attr_value')->oneOf()
    ->match('" [^"<]* "')
    ->match("' [^'<]* '")
;

$builder->rule('ws')->ignore()
    ->match('\s+')
;

$grammar = $builder->getGrammar();
$grammar->inline('ws');

$input = <<<'XML'
<root>
    <child id="child1"/>
    Some text...
    <p id="child2" class="pa\ra">
        Some <grand-child>other</grand-child> text!<br/>
    </p>
</root>
XML;

$parser = new RecursiveDescentParser($grammar);
$cst = $parser->parse($argv[1] ?? $input);
Debug::dump($cst);

$transform = new class extends Transform
{
    public \DOMDocument $doc;
    private \SplStack $openElements;

    protected function beforeTraverse(Node $node)
    {
        $this->doc = new \DOMDocument();
        $this->openElements = new \SplStack();
        $this->openElements->push($this->doc);
    }

    protected function afterTraverse($node): \DOMDocument
    {
        $this->openElements->pop();
        return $this->doc;
    }

    private function insertNode(\DOMNode $node): \DOMNode
    {
        $parent = $this->openElements->top();
        return $parent->appendChild($node);
    }

    private function insertElement(string $name, array $attrs = []): \DOMElement
    {
        $element = $this->doc->createElement($name);
        if ($attrs) {
            foreach ($attrs as $attr) {
                $element->setAttributeNode($attr);
            }
        }
        $this->insertNode($element);
        return $element;
    }

    protected function leave_start_tag(Node $node, string $name, ?array $attrs): void
    {
        $element = $this->insertElement($name, $attrs ?? []);
        $this->openElements->push($element);
    }

    protected function leave_end_tag(Node $node, string $name): void
    {
        $element = $this->openElements->pop();
        if ($element->localName !== $name) {
            throw new \DOMException(sprintf(
                'End tag mismatch: expected "%s" but got "%s".',
                $element->localName,
                $name,
            ));
        }
    }

    protected function leave_void_element(Node $node, string $name, ?array $attrs): void
    {
        $this->insertElement($name, $attrs ?? []);
    }

    protected function leave_attr_list(Node $node, \DOMAttr $head, array $tail): array
    {
        return [$head, ...$tail];
    }

    protected function leave_attr(Node $node, string $name, string $value): \DOMAttr
    {
        $attr = $this->doc->createAttribute($name);
        $attr->value = $value;
        return $attr;
    }

    protected function leave_attr_value(Node $node, string $value): string
    {
        return substr($value, 1, -1);
    }

    protected function leave_pcdata(Node $node, string $data): void
    {
        $node = $this->doc->createTextNode($data);
        $this->insertNode($node);
    }
};


$doc = $transform->transform($cst);
Debug::dump($doc->saveXML());
