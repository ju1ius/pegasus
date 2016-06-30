<?php
/*
 * This file is part of Pegasus
 *
 * (c) 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace ju1ius\Pegasus\Expression;


/**
 * Helper trait for expressions that can contain backreferences.
 */
trait HasBackReferenceTrait
{
    public static $BACKREF_REGEX = <<<'EOS'
/
    ((?: \\. | [^\$] )*?)   # Any escaped char or not $
    \$\{ ([a-zA-Z_]\w*) \}  # ${identifier}
    ((?: \\. | [^\$] )*)    # Any escaped char or not $
/Sx
EOS;

    public $hasBackReference = false;
    public $subjectParts = [];

    protected function splitSubject($subject)
    {
        if (preg_match_all(self::$BACKREF_REGEX, $subject, $matches, PREG_SET_ORDER)) {
            $this->hasBackReference = true;
            $this->subjectParts = $matches;
        }
    }

    protected function replaceSubject(callable $callback)
    {
        $output = '';
        foreach ($this->subjectParts as $part) {
            $replaced = $callback($part[2]);
            $output .= $part[1] . ($replaced ?: '') . $part[3];
        }
        return $output;
    }
}
