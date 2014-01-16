<?php

require_once __DIR__.'/../utils.php';

use ju1ius\Pegasus\Expression\Reference as Ref;
use ju1ius\Pegasus\Expression\Literal;
use ju1ius\Pegasus\Expression\Regex;
use ju1ius\Pegasus\Expression\OneOrMore;
use ju1ius\Pegasus\Expression\ZeroOrMore;
use ju1ius\Pegasus\Expression\Not;
use ju1ius\Pegasus\Expression\OneOf;
use ju1ius\Pegasus\Expression\Sequence;


function oldgram()
{
    $g = new Grammar();
    $g['rules'] = new Sequence([
        new Ref('_'),
        new OneOrMore([new Ref('rule')])
    ]);
    $g['rule'] = new Sequence([
        new Ref('identifier'),
        new Ref('equals'),
        new Ref('expression')
        //new OneOrMore([$expression])
    ]);
    $g['expression'] = new OneOf([
        new Ref('ored'),
        new Ref('sequence'),
        new Ref('term')
    ]);
    $g['ored'] = new Sequence([
        new Ref('sequence'),
        new OneOrMore([new Ref('or_term')])
    ]);
    $g['or_term'] = new Sequence([
        new Literal('|'),
        new Ref('_'),
        new Ref('sequence')
    ]);
    $g['sequence'] = new OneOrMore([new Ref('term')]);
    $g['term'] = new OneOf([
        new Ref('not_term'),
        new Ref('quantified'),
        new Ref('atom')
    ]);
    $g['not_term'] = new Sequence([
        new Literal('!'),
        new Ref('term'),
        new Ref('_')
    ]);
    $g['quantified'] = new Sequence([
        new Ref('atom'),
        new Ref('quantifier')
    ]);
    $g['atom'] = new OneOf([
        new Ref('reference'),
        new Ref('literal'),
        new Ref('regex')
    ]);
    $g['reference'] = new Sequence([
        new Ref('identifier'),
        new Not([new Ref('equals')])
    ]);
    $g['equals'] = new Sequence([new Literal('='), $_]);
    $g['identifier'] = new Sequence([
        new Regex('[a-zA-Z_]\w*'),
        new Ref('_')
    ]);
    $g['literal'] = new Sequence([
        new Regex('(["\'])((?:(?:\\\\.)|(?:(?!\1).))*)\1'),
        new Ref('_')
    ]);
    $g['regex'] = new Sequence([
        new Regex('\/((?:(?:\\\\.)|[^\/])*)\/([ilmsux]*)?'),
        new Ref('_')
    ]);
    $g['quantifier'] = new Sequence([
        new Regex('([*+?])|(?:\{(\d+)(?:,(\d*))?\})'),
        new Ref('_')
    ]);
    $g['_'] = new ZeroOrMore([new Ref('ws_or_comment')]);
    $g['ws_com'] = new OneOf([
        new Ref('ws'),
        new Ref('comment')
    ]);
    $g['ws'] = new Regex('\s+');
    $g['comment'] = new Regex('\#([^\r\n]*)');
    //$sequence = new Sequence([$term, new OneOrMore([$term], 'term+')], 'sequence');
    //$or_term = new Sequence([new Literal('|', 'pipe'), $_, $term], 'or_term');
    //$ored = new Sequence([$term, new OneOrMore([$or_term], 'or_term+')], 'ored');

    return $g;
}
