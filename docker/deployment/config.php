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
      /public/var
      /var
		',
    'allowDelete' => true,
  ],

  'tempDir' => '/tmp',
  'colors' => true,
];
