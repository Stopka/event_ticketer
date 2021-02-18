<?php

declare(strict_types=1);

namespace Ticketer\Templates;

use Nette\Bridges\ApplicationLatte\Template;
use RuntimeException;
use Ticketer\Templates\Filters\FilterInterface;

class AddFiltersToTemplate
{
    /**
     * @var FilterInterface[]
     */
    private array $filters;

    /**
     * @param FilterInterface[] $filters
     */
    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function __invoke(Template $template): void
    {
        foreach ($this->filters as $filter) {
            if (!is_callable($filter)) {
                $message = sprintf('Add "__invoke()" method to filter "%s"', get_class($filter));
                throw new RuntimeException($message);
            }
            $template->addFilter($filter->getName(), $filter);
        }
    }
}
