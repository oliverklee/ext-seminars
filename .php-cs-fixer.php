<?php

$config = \TYPO3\CodingStandards\CsFixerConfig::create();
$config->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect());
$config->getFinder()->in('Classes')->in('Configuration')->in('Tests');
return $config;
