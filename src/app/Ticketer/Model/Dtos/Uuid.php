<?php

declare(strict_types=1);

namespace Ticketer\Model\Dtos;

use Brick\Math\BigInteger;
use Brick\Math\RoundingMode;
use Nette\Utils\Strings;
use Ramsey\Uuid\Exception\InvalidArgumentException;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid as RamseyUuid;
use Ramsey\Uuid\UuidInterface;

class Uuid
{
    private UuidInterface $uuid;

    private static ?int $stringLength;

    private const UUID_SIZE = 2 ** 128;
    private const ALPHABET = [
        '0',
        '1',
        '2',
        '3',
        '4',
        '5',
        '6',
        '7',
        '8',
        '9',
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'l',
        'k',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];

    private function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @return UuidInterface
     */
    public function getInternalUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function equals(self $uuid): bool
    {
        return $this->getInternalUuid()->equals($uuid->getInternalUuid());
    }

    public function compareTo(self $uuid): int
    {
        return $this->getInternalUuid()->compareTo($uuid->getInternalUuid());
    }

    public function toString(): string
    {
        $number = BigInteger::of((string)$this->getInternalUuid()->getInteger());
        $output = '';
        $alphabetLength = count(self::ALPHABET);
        while ($number->isPositive()) {
            $previousNumber = clone $number;
            $number = $number->dividedBy($alphabetLength, RoundingMode::DOWN);
            $digit = $previousNumber->mod($alphabetLength);

            $output .= self::ALPHABET[$digit->toInt()];
        }
        $stringLength = self::getStringLength();

        return Strings::padRight($output, $stringLength, self::ALPHABET[0]);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function generate(): self
    {
        return new self(
            RamseyUuid::uuid4()
        );
    }

    public static function fromString(string $uuidString): self
    {
        $alphabetLength = count(self::ALPHABET);
        $stringLength = self::getStringLength();
        if (strlen($uuidString) !== $stringLength) {
            throw new InvalidArgumentException(
                "Encoded uuid '$uuidString' should contain $stringLength characters."
            );
        }
        $number = BigInteger::of(0);
        foreach (str_split(strrev($uuidString)) as $char) {
            $charIndex = array_search($char, self::ALPHABET, true);
            if (!is_int($charIndex)) {
                throw new InvalidArgumentException(
                    "Encoded uuid '$uuidString' contains invalid character $char."
                );
            }
            $number = $number->multipliedBy($alphabetLength)
                ->plus($charIndex);
        }
        try {
            return new self(
                RamseyUuid::fromInteger((string)$number)
            );
        } catch (InvalidUuidStringException $exception) {
            throw new InvalidArgumentException("Invalid uuid string '$uuidString'", 0, $exception);
        }
    }

    public static function getStringLength(): int
    {
        return (int)ceil(log(self::UUID_SIZE, count(self::ALPHABET)));
    }
}
