<?php

declare(strict_types=1);

namespace Ticketer\Tests\Model\Dtos\Uuid;

require __DIR__ . '/../../../../../vendor/autoload.php';

use Tester\Assert;
use Tester\Environment;
use Tester\TestCase;
use Ticketer\Model\Dtos\Uuid;

/**
 * Class UuidTest
 * @package Ticketer\Tests\Model\Dtos\Uuid
 * @testCase
 */
class UuidTest extends TestCase
{
    public function setUp()
    {
        Environment::setup();
    }

    public function testEncodeDecode(): void
    {
        $inputUuid = Uuid::generate();
        $uuidString = (string)$inputUuid;
        $outputUuid = Uuid::fromString($uuidString);
        Assert::true($inputUuid->equals($outputUuid));
    }
}

(new UuidTest())->run();
