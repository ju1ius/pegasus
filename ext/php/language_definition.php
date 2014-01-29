<?php

return [
    'name' => 'php',
    'extension' => 'php',
    'templates_dirs' => [__DIR__.'/templates'],
    'packrat_class' => 'ju1ius\Pegasus\Parser\Generated\Packrat',
    'lr_packrat_class' => 'ju1ius\Pegasus\Parser\Generated\LRPackrat',
    'node_visitor_class' => 'ju1ius\Pegasus\NodeVisitor'
];
