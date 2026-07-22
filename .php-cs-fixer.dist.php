<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        './Neos.Cache/Classes',
        './Neos.Cache/Tests',
        './Neos.Eel/Classes',
        './Neos.Eel/Tests',
        './Neos.Error.Messages/Classes',
        './Neos.Error.Messages/Tests',
        './Neos.Flow/Classes',
        './Neos.Flow/Tests',
        './Neos.FluidAdaptor/Classes',
        './Neos.FluidAdaptor/Tests',
        './Neos.Kickstarter/Classes',
        './Neos.Utility.Arrays/Classes',
        './Neos.Utility.Arrays/Tests',
        './Neos.Utility.Files/Classes',
        './Neos.Utility.Files/Tests',
        './Neos.Utility.MediaTypes/Classes',
        './Neos.Utility.MediaTypes/Tests',
        './Neos.Utility.ObjectHandling/Classes',
        './Neos.Utility.ObjectHandling/Tests',
        './Neos.Utility.OpcodeCache/Classes',
        './Neos.Utility.Pdo/Classes',
        './Neos.Utility.Schema/Classes',
        './Neos.Utility.Schema/Tests',
        './Neos.Utility.Unicode/Classes',
        './Neos.Utility.Unicode/Tests',
    ])
    ->notPath([
        './Neos.Eel/Classes/FlowQuery/FizzleParser.php',
        './Neos.Eel/Classes/EelParser.php',
        './Neos.Eel/Classes/AbstractParser.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ]
    ])
    ->setFinder($finder);
