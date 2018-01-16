<?php

/**
 * This file is part of the Grido (http://grido.bugyik.cz)
 *
 * Copyright (c) 2011 Petr Bugyík (http://petr.bugyik.cz)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

namespace App\Controls\Grids\Components;

use Grido\Grid;

class Button extends \Grido\Components\Button {
    use TElementPrototype;

    public function __construct(Grid $grid, string $name, ?string $label = NULL, ?string $destination = NULL, array $arguments = []) {
        parent::__construct($grid, $name, $label, $destination, $arguments);
        $this->label = $this->translate($label);
    }


}
