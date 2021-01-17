<?php

declare(strict_types=1);

return [
  'app' => [
    'remote' => getenv('REMOTE'),
    'local' => '/srv',
    'test' => false,
    'ignore' => '
	  /deployment*
      .git*
      /app/config/debug.php
      /app/config/parameters.environmental.php
      /app/config/parameters.local.php
      /www/var
      /var
		',
    'allowDelete' => true,
  ],

  'tempDir' => '/tmp',
  'colors' => true,
];
