<?php

return [
    'name' => 'python',
    'extension' => 'py',
    'templates_dirs' => [__DIR__.'/templates'],
    'packrat_class' => 'pegasus.parsers.packrat.Packrat',
    'lr_packrat_class' => 'pegasus.parsers.lr_packrat.LRPackrat',
    'node_visitor_class' => 'pegasus.node_visitor.NodeVisitor'
];
