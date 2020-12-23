<?php

declare(strict_types=1);

namespace Ticketer\Modules\FrontModule\Controls\Grids;

/**
 * Created by IntelliJ IDEA.
 * User: stopka
 * Date: 22.1.17
 * Time: 16:20
 */
interface ICartApplicationsGridWrapperFactory
{

    /**
     * @return CartApplicationsGridWrapper
     */
    public function create(): CartApplicationsGridWrapper;
}
