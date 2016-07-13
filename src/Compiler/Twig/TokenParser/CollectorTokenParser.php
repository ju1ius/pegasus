<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Compiler\Twig\TokenParser;

use Twig_TokenParser;
use Twig_Token;

use ju1ius\Pegasus\Compiler\Twig\Node\CollectorNode;


/**
 * Token parser for collector nodes.
 * 
 * {% collect 'key' %}
 *     Collected data...
 * {% endcollect %}
 */
class CollectorTokenParser extends Twig_TokenParser
{
    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $key = $stream->expect(Twig_Token::STRING_TYPE)->getValue();
        $stream->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'checkTokenEnd'], true);
        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new CollectorNode($key, $body, $lineno, $this->getTag());
    }

    public function getTag()
    {
        return 'collect';
    }

    public function checkTokenEnd(Twig_Token $token)
    {
        return $token->test('endcollect');   
    }
}
