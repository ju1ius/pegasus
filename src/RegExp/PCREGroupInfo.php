<?php
/*
 * This file is part of Pegasus
 *
 * © 2014 Jules Bernable
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ju1ius\Pegasus\RegExp;

use ju1ius\Pegasus\RegExp\Exception\MissingClosingParenthesis;
use ju1ius\Pegasus\RegExp\Exception\UnmatchedClosingParenthesis;

/**
 * @author ju1ius <ju1ius@laposte.net>
 */
final class PCREGroupInfo
{
    const GROUP_START_RX = <<<'REGEXP'
/\G (?>                             # Prevent backtracking for conditional groups (?(2)foo|bar)
    (?<numbered> \( (?!\?))         # no \? after parenthese => numbered capturing group
    | \( \? (?:                     # qmark followed by one of
        (?<noncapturing> :)         # ':' => non-capturing
        | (?<atomic> >)             # '>' => atomic
        | (?<assertion> <?[!=])     # assertion
        | (?<named>(?J)             # named capture
            P?<(?<name>\w+)>
            |'(?<name>\w+)'
          )
        | (?<setopt> [imsxUXJ-]+:?) # option setting
        | (?<branchreset> \|)       # pcre branch reset
    )
)
/Sx
REGEXP;

    /**
     * @var int
     */
    private $pos = 0;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * Total group count.
     *
     * @var int
     */
    private $groupCount = 0;

    /**
     * A stack of indexes into the groups array, so we know which one to close.
     *
     * @var \SplStack
     */
    private $groupStack;

    /**
     * @var int Number of capturing groups.
     */
    private $captureCount = 0;

    /**
     * Returns the number of capturing groups in the given pattern.
     *
     * @param string $pattern
     *
     * @return int
     */
    public static function captureCount($pattern)
    {
        $info = new self();
        $info->parse($pattern);

        return $info->captureCount;
    }

    /**
     * Returns the number of groups (capturing & non-capturing) in the given pattern.
     *
     * @param string $pattern
     *
     * @return int
     */
    public static function groupCount($pattern)
    {
        $info = new self();
        $info->parse($pattern);

        return $info->groupCount;
    }

    /**
     * Returns an array of group info arrays.
     *
     * A group info array contains the following properties:
     *
     * - 'type' (string)        The type of the group. Can be one of:
     *                          'numbered', 'named', 'noncapturing', 'atomic', 'assertion', 'setopt', 'branchreset',
     *                          'conditional', 'condition'.
     * - 'capturing' (boolean)  Whether the group is capturing
     * - 'number' (integer)     The group number if it's a capturing group.
     * - 'name' (string)        The group name if it's a named capturing group.
     * - 'start' (integer)      The offset of the opening parenthesis.
     * - 'end' (integer)        The offset of the closing parenthesis.
     * - 'pattern' (string)     The text of the group, including parentheses.
     *
     * @param string $pattern
     *
     * @return array
     */
    public function parse($pattern)
    {
        $this->pos = 0;
        $length = strlen($pattern);
        $this->groups = [];
        $this->groupCount = 0;
        $this->groupStack = new \SplStack();
        $this->captureCount = 0;
        while ($this->pos < $length) {
            $char = $pattern[$this->pos];
            switch ($char) {
                case '\\':
                    $this->pos += 2;
                    continue;
                case '(':
                    $this->handleGroupStart($pattern);
                    break;
                case ')':
                    if ($this->groupStack->isEmpty()) {
                        throw new UnmatchedClosingParenthesis($pattern, $this->pos);
                    }
                    $this->handleGroupEnd($pattern);
                    break;
                default:
                    $this->pos++;
                    break;
            }
        }

        if (!$this->groupStack->isEmpty()) {
            throw new MissingClosingParenthesis($pattern);
        }

        return array_values($this->groups);
    }

    private function handleGroupStart($pattern)
    {
        if (preg_match(self::GROUP_START_RX, $pattern, $matches, 0, $this->pos)) {
            $groupInfo = $this->extractGroupInfo($matches);
            $this->groups[++$this->groupCount] = $groupInfo;
            $this->groupStack->push($this->groupCount);
            $this->pos += strlen($matches[0]);

            return;
        }
        // conditional subpattern, push one group plus another for the condition
        if (substr($pattern, $this->pos, 3) === '(?(') {
            $this->groups[++$this->groupCount] = [
                'type' => 'conditional',
                'capturing' => false,
                'start' => $this->pos
            ];
            $this->groupStack->push($this->groupCount);
            $this->groups[++$this->groupCount] = [
                'type' => 'condition',
                'capturing' => false,
                'start' => $this->pos + 2
            ];
            $this->groupStack->push($this->groupCount);
            $this->pos += 3;
        }
    }

    private function handleGroupEnd($pattern)
    {
        $i = $this->groupStack->pop();
        $group = $this->groups[$i];
        $this->pos++;

        $group['end'] = $this->pos;
        $group['pattern'] = substr($pattern, $group['start'], $group['end'] - $group['start']);

        $this->groups[$i] = $group;
    }

    private function extractGroupInfo(array $matches)
    {
        // keep only non-empty named captures
        $filtered = array_filter($matches, function ($v, $k) {
            return !empty($v) && is_string($k);
        }, ARRAY_FILTER_USE_BOTH);

        $type = key($filtered);
        $capturing = in_array($type, ['numbered', 'named'], true);

        $groupInfo = [
            'type' => $type,
            'capturing' => $capturing,
            'start' => $this->pos
        ];
        if ($capturing) {
            $groupInfo['number'] = ++$this->captureCount;
        }
        if ($type === 'named') {
            $groupInfo['name'] = $filtered['name'];
        }

        return $groupInfo;
    }
}
