<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Doctrine;

use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Nette\Utils\Strings;

class PostfixedUnderscoreNamingStrategy extends UnderscoreNamingStrategy
{
    /** @var string */
    private string $postfix;

    /**
     * PostfixedUnderscoreNamingStrategy constructor.
     * @param string $postfix
     * @param int $case
     * @param bool $numberAware
     */
    public function __construct(string $postfix = 'Entity', int $case = CASE_LOWER, bool $numberAware = false)
    {
        parent::__construct($case, $numberAware);
        $this->postfix = $postfix;
    }


    public function classToTableName($className): string
    {
        $className = parent::classToTableName($className);
        if (!Strings::endsWith($className, $this->postfix)) {
            return $className;
        }
        $postfix = '_' . (
            CASE_LOWER === $this->getCase()
                ? Strings::lower($this->postfix)
                : Strings::upper($this->postfix)
            );
        $startPosition = strrpos($className, $postfix);
        if (false !== $startPosition) {
            $className = substr_replace(
                $className,
                '',
                $startPosition,
                strlen($postfix)
            );
        }

        return $className;
    }
}
