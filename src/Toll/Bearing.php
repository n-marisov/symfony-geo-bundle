<?php

namespace Maris\Symfony\Geo\Toll;

/**
 * Азимуты между двумя точками.
 */
class Bearing
{
    /**
     * Прямой азимут
     * @var float|null
     */
    public ?float $initial = null;

    /**
     * Конечный азимут
     * @var float|null
     */
    public ?float $final = null;

    /**
     * @return float|null
     */
    public function getInitial(): ?float
    {
        return $this->initial;
    }

    /**
     * @param float|null $initial
     * @return $this
     */
    public function setInitial( ?float $initial ): self
    {
        $this->initial = $initial;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getFinal(): ?float
    {
        return $this->final;
    }

    /**
     * @param float|null $final
     * @return $this
     */
    public function setFinal( ?float $final ): self
    {
        $this->final = $final;
        return $this;
    }



}