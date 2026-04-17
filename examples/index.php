<?php

require __DIR__ . '/../vendor/autoload.php';

use Zod\Z;

$schema = Z::number()
  ->min(3)
  ->lt(10);

$result = $schema->safeParse(9);

var_dump($result);