<?php
/**
 * This file has been generated by Pegasus.
 */

use ju1ius\Pegasus\Node;


class Calculator_lr extends ju1ius\Pegasus\Parser\Generated\LRPackrat
{
    /**
     * The default start rule from the grammar.
     *
     * @var string
     */
    protected $start_rule = 'expr';

    /**
     * expr = <Reference to expr> "+" <Reference to term> | <Reference to expr> "-" <Reference to term> | <Reference to term>
     */
    public function match_expr()
    {
        $result = null;
        /**
         * matching <Reference to expr> "+" <Reference to term> | <Reference to expr> "-" <Reference to term> | <Reference to term>
         */
        $match_0 = false;
        do {
            $pos_0 = $this->pos;
            $result_0 = null;
            /**
             * matching <Reference to expr> "+" <Reference to term>
             */
            $match_1 = true;
            $pos_1 = $this->pos;
            $children_1 = [];
            do {
                $result_1 = null;
                $result_1 = $this->apply('expr', $this->pos);
                if (!$result_1) {
                    $match_1 = false;
                    break;
                } else {
                    $children_1[] = $result_1;
                }
                $result_1 = null;
                /**
                 * matching: "+"
                 */
                if (strpos($this->text, '+', $this->pos) === $this->pos) {
                    $result_1 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_1->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "+">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_1) {
                    $match_1 = false;
                    break;
                } else {
                    $children_1[] = $result_1;
                }
                $result_1 = null;
                $result_1 = $this->apply('term', $this->pos);
                if (!$result_1) {
                    $match_1 = false;
                    break;
                } else {
                    $children_1[] = $result_1;
                }
            } while(0);
            if ($_1 === true) {
                $result_0 = new Node\Sequence('', $this->text, $pos_1, $this->pos, $children_1);
            } else {
                $result_0 = null;
                $this->pos = $pos_1;
            }
            if ($result_0) {
                $match_0 = true;
                break;
            }
            $this->pos = $pos_0;
            $result_0 = null;
            /**
             * matching <Reference to expr> "-" <Reference to term>
             */
            $match_2 = true;
            $pos_2 = $this->pos;
            $children_2 = [];
            do {
                $result_2 = null;
                $result_2 = $this->apply('expr', $this->pos);
                if (!$result_2) {
                    $match_2 = false;
                    break;
                } else {
                    $children_2[] = $result_2;
                }
                $result_2 = null;
                /**
                 * matching: "-"
                 */
                if (strpos($this->text, '-', $this->pos) === $this->pos) {
                    $result_2 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_2->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "-">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_2) {
                    $match_2 = false;
                    break;
                } else {
                    $children_2[] = $result_2;
                }
                $result_2 = null;
                $result_2 = $this->apply('term', $this->pos);
                if (!$result_2) {
                    $match_2 = false;
                    break;
                } else {
                    $children_2[] = $result_2;
                }
            } while(0);
            if ($_2 === true) {
                $result_0 = new Node\Sequence('', $this->text, $pos_2, $this->pos, $children_2);
            } else {
                $result_0 = null;
                $this->pos = $pos_2;
            }
            if ($result_0) {
                $match_0 = true;
                break;
            }
            $this->pos = $pos_0;
            $result_0 = null;
            $result_0 = $this->apply('term', $this->pos);
            if ($result_0) {
                $match_0 = true;
                break;
            }
            $this->pos = $pos_0;
        } while (0);
        if ($match_0 === true) {
            $result = new Node\OneOf('expr', $this->text, $pos_0, $this->pos, [$result_0]);
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\OneOf: expr = <Reference to expr> "+" <Reference to term> | <Reference to expr> "-" <Reference to term> | <Reference to term>>';
            $this->error->pos = $pos_0;

        }
        return $result; 
    }

    /**
     * term = <Reference to term> "*" <Reference to primary> | <Reference to term> "/" <Reference to primary> | <Reference to primary>
     */
    public function match_term()
    {
        $result = null;
        /**
         * matching <Reference to term> "*" <Reference to primary> | <Reference to term> "/" <Reference to primary> | <Reference to primary>
         */
        $match_3 = false;
        do {
            $pos_3 = $this->pos;
            $result_3 = null;
            /**
             * matching <Reference to term> "*" <Reference to primary>
             */
            $match_4 = true;
            $pos_4 = $this->pos;
            $children_4 = [];
            do {
                $result_4 = null;
                $result_4 = $this->apply('term', $this->pos);
                if (!$result_4) {
                    $match_4 = false;
                    break;
                } else {
                    $children_4[] = $result_4;
                }
                $result_4 = null;
                /**
                 * matching: "*"
                 */
                if (strpos($this->text, '*', $this->pos) === $this->pos) {
                    $result_4 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_4->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "*">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_4) {
                    $match_4 = false;
                    break;
                } else {
                    $children_4[] = $result_4;
                }
                $result_4 = null;
                $result_4 = $this->apply('primary', $this->pos);
                if (!$result_4) {
                    $match_4 = false;
                    break;
                } else {
                    $children_4[] = $result_4;
                }
            } while(0);
            if ($_4 === true) {
                $result_3 = new Node\Sequence('', $this->text, $pos_4, $this->pos, $children_4);
            } else {
                $result_3 = null;
                $this->pos = $pos_4;
            }
            if ($result_3) {
                $match_3 = true;
                break;
            }
            $this->pos = $pos_3;
            $result_3 = null;
            /**
             * matching <Reference to term> "/" <Reference to primary>
             */
            $match_5 = true;
            $pos_5 = $this->pos;
            $children_5 = [];
            do {
                $result_5 = null;
                $result_5 = $this->apply('term', $this->pos);
                if (!$result_5) {
                    $match_5 = false;
                    break;
                } else {
                    $children_5[] = $result_5;
                }
                $result_5 = null;
                /**
                 * matching: "/"
                 */
                if (strpos($this->text, '/', $this->pos) === $this->pos) {
                    $result_5 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_5->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "/">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_5) {
                    $match_5 = false;
                    break;
                } else {
                    $children_5[] = $result_5;
                }
                $result_5 = null;
                $result_5 = $this->apply('primary', $this->pos);
                if (!$result_5) {
                    $match_5 = false;
                    break;
                } else {
                    $children_5[] = $result_5;
                }
            } while(0);
            if ($_5 === true) {
                $result_3 = new Node\Sequence('', $this->text, $pos_5, $this->pos, $children_5);
            } else {
                $result_3 = null;
                $this->pos = $pos_5;
            }
            if ($result_3) {
                $match_3 = true;
                break;
            }
            $this->pos = $pos_3;
            $result_3 = null;
            $result_3 = $this->apply('primary', $this->pos);
            if ($result_3) {
                $match_3 = true;
                break;
            }
            $this->pos = $pos_3;
        } while (0);
        if ($match_3 === true) {
            $result = new Node\OneOf('term', $this->text, $pos_3, $this->pos, [$result_3]);
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\OneOf: term = <Reference to term> "*" <Reference to primary> | <Reference to term> "/" <Reference to primary> | <Reference to primary>>';
            $this->error->pos = $pos_3;

        }
        return $result; 
    }

    /**
     * primary = "(" <Reference to expr> ")" | <Reference to num>
     */
    public function match_primary()
    {
        $result = null;
        /**
         * matching "(" <Reference to expr> ")" | <Reference to num>
         */
        $match_6 = false;
        do {
            $pos_6 = $this->pos;
            $result_6 = null;
            /**
             * matching "(" <Reference to expr> ")"
             */
            $match_7 = true;
            $pos_7 = $this->pos;
            $children_7 = [];
            do {
                $result_7 = null;
                /**
                 * matching: "("
                 */
                if (strpos($this->text, '(', $this->pos) === $this->pos) {
                    $result_7 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_7->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "(">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_7) {
                    $match_7 = false;
                    break;
                } else {
                    $children_7[] = $result_7;
                }
                $result_7 = null;
                $result_7 = $this->apply('expr', $this->pos);
                if (!$result_7) {
                    $match_7 = false;
                    break;
                } else {
                    $children_7[] = $result_7;
                }
                $result_7 = null;
                /**
                 * matching: ")"
                 */
                if (strpos($this->text, ')', $this->pos) === $this->pos) {
                    $result_7 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                    $this->pos = $result_7->end;
                } else {
                    $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: ")">';
                    $this->error->pos = $this->pos;

                }
                if (!$result_7) {
                    $match_7 = false;
                    break;
                } else {
                    $children_7[] = $result_7;
                }
            } while(0);
            if ($_7 === true) {
                $result_6 = new Node\Sequence('', $this->text, $pos_7, $this->pos, $children_7);
            } else {
                $result_6 = null;
                $this->pos = $pos_7;
            }
            if ($result_6) {
                $match_6 = true;
                break;
            }
            $this->pos = $pos_6;
            $result_6 = null;
            $result_6 = $this->apply('num', $this->pos);
            if ($result_6) {
                $match_6 = true;
                break;
            }
            $this->pos = $pos_6;
        } while (0);
        if ($match_6 === true) {
            $result = new Node\OneOf('primary', $this->text, $pos_6, $this->pos, [$result_6]);
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\OneOf: primary = "(" <Reference to expr> ")" | <Reference to num>>';
            $this->error->pos = $pos_6;

        }
        return $result; 
    }

    /**
     * num = <Reference to expo> | <Reference to float> | <Reference to int>
     */
    public function match_num()
    {
        $result = null;
        /**
         * matching <Reference to expo> | <Reference to float> | <Reference to int>
         */
        $match_8 = false;
        do {
            $pos_8 = $this->pos;
            $result_8 = null;
            $result_8 = $this->apply('expo', $this->pos);
            if ($result_8) {
                $match_8 = true;
                break;
            }
            $this->pos = $pos_8;
            $result_8 = null;
            $result_8 = $this->apply('float', $this->pos);
            if ($result_8) {
                $match_8 = true;
                break;
            }
            $this->pos = $pos_8;
            $result_8 = null;
            $result_8 = $this->apply('int', $this->pos);
            if ($result_8) {
                $match_8 = true;
                break;
            }
            $this->pos = $pos_8;
        } while (0);
        if ($match_8 === true) {
            $result = new Node\OneOf('num', $this->text, $pos_8, $this->pos, [$result_8]);
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\OneOf: num = <Reference to expo> | <Reference to float> | <Reference to int>>';
            $this->error->pos = $pos_8;

        }
        return $result; 
    }

    /**
     * float = /\G-?[0-9]*\.[0-9]+/Sx
     */
    public function match_float()
    {
        $result = null;
        /**
         * matching: /\G-?[0-9]*\.[0-9]+/Sx
         */
        if(preg_match('/\G-?[0-9]*\.[0-9]+/Sx', $this->text, $matches, 0, $this->pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $result = new Node\Regex('float', $this->text, $this->pos, $this->pos + $length, $matches); 
            $this->pos = $result->end;
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\Regex: float = /\G-?[0-9]*\.[0-9]+/Sx>';
            $this->error->pos = $this->pos;

        }
        return $result; 
    }

    /**
     * int = /\G-?[0-9]+/Sx
     */
    public function match_int()
    {
        $result = null;
        /**
         * matching: /\G-?[0-9]+/Sx
         */
        if(preg_match('/\G-?[0-9]+/Sx', $this->text, $matches, 0, $this->pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $result = new Node\Regex('int', $this->text, $this->pos, $this->pos + $length, $matches); 
            $this->pos = $result->end;
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\Regex: int = /\G-?[0-9]+/Sx>';
            $this->error->pos = $this->pos;

        }
        return $result; 
    }

    /**
     * expo = <Reference to float> | <Reference to int> "e" <Reference to int>
     */
    public function match_expo()
    {
        $result = null;
        /**
         * matching <Reference to float> | <Reference to int> "e" <Reference to int>
         */
        $match_9 = true;
        $pos_9 = $this->pos;
        $children_9 = [];
        do {
            $result_9 = null;
            /**
             * matching <Reference to float> | <Reference to int>
             */
            $match_10 = false;
            do {
                $pos_10 = $this->pos;
                $result_10 = null;
                $result_10 = $this->apply('float', $this->pos);
                if ($result_10) {
                    $match_10 = true;
                    break;
                }
                $this->pos = $pos_10;
                $result_10 = null;
                $result_10 = $this->apply('int', $this->pos);
                if ($result_10) {
                    $match_10 = true;
                    break;
                }
                $this->pos = $pos_10;
            } while (0);
            if ($match_10 === true) {
                $result_9 = new Node\OneOf('', $this->text, $pos_10, $this->pos, [$result_10]);
            } else {
                $this->error->expr = '<ju1ius\Pegasus\Expression\OneOf: <Reference to float> | <Reference to int>>';
                $this->error->pos = $pos_10;

            }
            if (!$result_9) {
                $match_9 = false;
                break;
            } else {
                $children_9[] = $result_9;
            }
            $result_9 = null;
            /**
             * matching: "e"
             */
            if (strpos($this->text, 'e', $this->pos) === $this->pos) {
                $result_9 = new Node\Literal('', $this->text, $this->pos, $this->pos + 1);
                $this->pos = $result_9->end;
            } else {
                $this->error->expr = '<ju1ius\Pegasus\Expression\Literal: "e">';
                $this->error->pos = $this->pos;

            }
            if (!$result_9) {
                $match_9 = false;
                break;
            } else {
                $children_9[] = $result_9;
            }
            $result_9 = null;
            $result_9 = $this->apply('int', $this->pos);
            if (!$result_9) {
                $match_9 = false;
                break;
            } else {
                $children_9[] = $result_9;
            }
        } while(0);
        if ($_9 === true) {
            $result = new Node\Sequence('expo', $this->text, $pos_9, $this->pos, $children_9);
        } else {
            $result = null;
            $this->pos = $pos_9;
        }
        return $result; 
    }

    /**
     * _ = /\G\s*/Sx
     */
    public function match__()
    {
        $result = null;
        /**
         * matching: /\G\s*/Sx
         */
        if(preg_match('/\G\s*/Sx', $this->text, $matches, 0, $this->pos)) {
            $match = $matches[0];
            $length = strlen($match);
            $result = new Node\Regex('_', $this->text, $this->pos, $this->pos + $length, $matches); 
            $this->pos = $result->end;
        } else {
            $this->error->expr = '<ju1ius\Pegasus\Expression\Regex: _ = /\G\s*/Sx>';
            $this->error->pos = $this->pos;

        }
        return $result; 
    }
}