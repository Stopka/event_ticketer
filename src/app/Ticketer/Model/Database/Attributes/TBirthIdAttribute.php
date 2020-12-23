<?php

declare(strict_types=1);

namespace Ticketer\Model\Database\Attributes;

use Nette\Utils\Strings;

trait TBirthIdAttribute
{
    use TBirthCodeAttribute;
    use TBirthDateAttribute;
    use TGenderAttribute;

    /**
     * @return string
     */
    public function getBirthIdDate()
    {
        $date = $this->getBirthDate();
        if (null === $date || null === $this->getGender()) {
            return null;
        }
        $year = $date->format('y');
        $monthNumber = (int)$date->format('m');
        if (1 === $this->getGender()) {
            $monthNumber += 50;
        }
        $month = Strings::padLeft((string)$monthNumber, 2, '0');
        $day = $date->format('d');

        return $year . $month . $day;
    }

    /**
     * @return string
     */
    public function getBirthId()
    {
        if (null === $this->getBirthIdDate() || null === $this->getBirthCode()) {
            return null;
        }

        return $this->getBirthIdDate() . '/' . $this->getBirthCode();
    }
}
