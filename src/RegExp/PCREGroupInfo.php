<?php declare(strict_types=1);
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
        | (?<setopt>                # option setting
            (?<setopt_opts> [imsxUXJ-]+)
            (?<setopt_inside> :)?   # options only apply inside this group
          )
        | (?<branchreset> \|)       # pcre branch reset
        | (?<comment> \#)           # inline comment
    )
)
/Sx
REGEXP;

    private $patternLength = 0;

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
    public static function captureCount(string $pattern): int
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
    public static function groupCount(string $pattern): int
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
     *                          'conditional', 'condition', 'comment'.
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
    public function parse(string $pattern): array
    {
        $this->pos = 0;
        $length = $this->patternLength = strlen($pattern);
        $this->groups = [];
        $this->groupCount = 0;
        $this->groupStack = new \SplStack();
        $this->captureCount = 0;
        while ($this->pos < $length) {
            $char = $pattern[$this->pos];
            switch ($char) {
                case '\\':
                    $this->pos += 2;
                    break;
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

        $this->handleModifiers();

        return $this->groups;
    }

    private function handleGroupStart(string $pattern): void
    {
        if (preg_match(self::GROUP_START_RX, $pattern, $matches, 0, $this->pos)) {
            $groupInfo = $this->extractGroupInfo($matches);
            $this->groups[++$this->groupCount] = $groupInfo;
            $this->groupStack->push($this->groupCount);
            $this->pos += strlen($matches[0]);

            return;
        }

        $p = $this->groupStack->isEmpty() ? null : $this->groupStack->top();
        $parent = $p ? $this->groups[$p] : null;

        // conditional subpattern, push one group plus another for the condition
        if (substr($pattern, $this->pos, 3) === '(?(') {
            $i = ++$this->groupCount;
            $this->groups[$i] = [
                'type' => 'conditional',
                'parent' => $parent,
                'capturing' => false,
                'start' => $this->pos
            ];
            $this->groupStack->push($this->groupCount);
            $this->groups[++$this->groupCount] = [
                'type' => 'condition',
                'parent' => $i,
                'capturing' => false,
                'start' => $this->pos + 2
            ];
            $this->groupStack->push($this->groupCount);
            $this->pos += 3;
        }
    }

    private function handleGroupEnd(string $pattern): void
    {
        $i = $this->groupStack->pop();
        $group = $this->groups[$i];
        if (!$this->groupStack->isEmpty()) {
            $group['parent'] = $this->groupStack->top();
        }
        $this->pos++;

        $group['end'] = $this->pos;
        $group['pattern'] = substr($pattern, $group['start'], $group['end'] - $group['start']);

        if ($group['type'] === 'setopt') {
            if ($group['applies_to'] === 'self') {
                $group['applies_until'] = $this->pos;
            }
        }

        $this->groups[$i] = $group;
    }

    private function extractGroupInfo(array $matches): array
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
            'parent' => null,
            'start' => $this->pos,
        ];
        if ($capturing) {
            $groupInfo['number'] = ++$this->captureCount;
        }
        if ($type === 'named') {
            $groupInfo['name'] = $filtered['name'];
        }
        if ($type === 'setopt') {
            preg_match_all('/-?[imsxUXJ]/', $filtered['setopt_opts'], $options);
            $groupInfo['options'] = [];
            foreach ($options[0] as $option) {
                if ($option[0] === '-') {
                    $groupInfo['options'][$option[1]] = false;
                } else {
                    $groupInfo['options'][$option[0]] = true;
                }
            }
            if (!empty($filtered['setopt_inside'])) {
                $groupInfo['applies_to'] = 'self';
            } else {
                $groupInfo['applies_to'] = 'parent';
            }
            $groupInfo['applies_from'] = $this->pos;
        }

        return $groupInfo;
    }

    private function handleModifiers()
    {
        // get setopt groups
        $setopts = [];
        foreach ($this->groups as $i => $group) {
            if ($group['type'] === 'setopt' && $group['applies_to'] === 'parent') {
                if ($p = $group['parent']) {
                    $parent = $this->groups[$p];
                    $group['applies_until'] = $parent['end'];
                } else {
                    $group['applies_until'] = $this->pos - 1;
                }
                $this->groups[$i] = $group;
            }
            //unset($group['applies_to']);
        }
        //foreach ($setopts as $i => $setopt) {
        //    $parent = $this->findParentGroup($setopt);
        //    $setopt['applies_until'] = $parent ? $parent['end'] : $this->patternLength - 1;
        //    $this->groups[$i] = $setopt;
        //}
    }

    private function findParentGroup($group)
    {
        $parent = null;
        $nearestStart = INF;
        $nearestEnd = INF;
        foreach ($this->groups as $candidate) {
            if ($candidate['start'] >= $group['start'] || $candidate['end'] <= $group['end']) {
                continue;
            }
            $distanceLeft = $group['start'] - $candidate['start'];
            $distanceRight = $candidate['end'] - $group['end'];
            if ($distanceLeft < $nearestStart && $distanceRight < $nearestEnd) {
                $nearestStart = $distanceLeft;
                $nearestEnd = $distanceRight;
                $parent = $candidate;
            }
        }

        return $parent;
    }
}
