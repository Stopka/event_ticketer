<?php

declare(strict_types=1);


namespace Ticketer\Modules\AdminModule\Controls\Forms\Inputs;


use Nette\Forms\Controls\CheckboxList;
use Ticketer\Model\Database\Entities\AdditionVisibilityEntity;

class AdditionVisibilityCheckboxList extends CheckboxList
{
    /**
     * AdditionVisibilityList constructor.
     * @param null|string|object $label
     */
    public function __construct($label = null)
    {
        parent::__construct($label, self::getItemLabels());
    }

    /**
     * @return string[]
     */
    public static function getItemLabels(): array
    {
        return [
            'reservation' => 'Value.Addition.Visible.Reservation',
            'registration' => 'Value.Addition.Visible.Register',
            'customer' => 'Value.Addition.Visible.Customer',
            'preview' => 'Value.Addition.Visible.Preview',
            'export' => 'Value.Addition.Visible.Export',
        ];
    }

    public function setValue($values)
    {
        if ($values instanceof AdditionVisibilityEntity) {
            $values = $values->getValueArray(null, ['getId']);
            $result = [];
            foreach ($values as $key => $value) {
                if (!$value) {
                    continue;
                }
                $result[] = $key;
            }
            $values = $result;
        }
        parent::setValue($values);
    }


}
