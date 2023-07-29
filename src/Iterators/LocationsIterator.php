<?php

namespace Maris\Symfony\Geo\Iterators;

use Doctrine\Common\Collections\Collection;
use Iterator;
use Maris\Symfony\Geo\Entity\Location;

/**
 * Итератор для переборки координат
 */

class LocationsIterator implements Iterator
{
    protected int $position;

    protected int $count;

    protected array $coordinates;

    /**
     * @param Collection $collection
     */
    public function __construct( Collection $collection )
    {
        $this->coordinates = $collection->toArray();
        $this->count = $collection->count();
    }


    /**
     * @inheritDoc
     */
    public function current(): Location
    {
        return $this->coordinates[ $this->position ];
    }

    /**
     * @inheritDoc
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @inheritDoc
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function valid(): bool
    {
        return $this->position < $this->count;
    }

    /**
     * @inheritDoc
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}