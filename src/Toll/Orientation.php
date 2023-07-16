<?php

namespace Maris\Symfony\Geo\Toll;

/**
 * Ориентация точки относительно линии.
 *
 */
enum Orientation:int
{
    /**
     * Точка коллинеарна вектору.
     */
    case COLLINEAR = 0;

    /**
     * Точка разводит вектор по часовой стрелки.
     */
    case CLOCKWISE = 1;

    /**
     * Точка разводит вектор против часовой стрелки.
     */
    case ANTI_CLOCKWISE = -1;

    /***
     * Указывает что точка разводит линию по часовой стрелке.
     * @return bool
     */
    public function isClockwise():bool
    {
        return $this === self::CLOCKWISE;
    }

    /**
     * Указывает что точка разводит линию против часовой стрелки.
     * @return bool
     */
    public function isAntiClockwise():bool
    {
        return $this === self::ANTI_CLOCKWISE;
    }

    /**
     * Указывает что точка не разводит линию (лежит на линии или коллинеарна ей).
     * @return bool
     */
    public function isCollinear():bool
    {
        return $this === self::COLLINEAR;
    }

    /**
     * Указывает что точка разводит линию в какую либо сторону (не лежит на линии и не коллинеарна ей).
     * @return bool
     */
    public function isNotCollinear():bool
    {
        return $this !== self::COLLINEAR;
    }

    /**
     * Указывает что точка разводит линию по часовой стрелке или коллинеарна ей.
     * @return bool
     */
    public function iscClockwiseOrCollinear():bool
    {
        return $this->isClockwise() || $this->isCollinear();
    }

    /**
     * Указывает что точка разводит линию против часовой стрелки или коллинеарна ей.
     * @return bool
     */
    public function iscAntiClockwiseOrCollinear():bool
    {
        return $this->isAntiClockwise() || $this->isCollinear();
    }

    /**
     * Преобразует значение к числу.
     * @return int<-1,1>
     */
    public function toInt():int
    {
        return $this->value;
    }

    /**
     * Создает объект ориентации из числа с плавающей точкой.
     * @param float $value
     * @return Orientation
     */
    public static function fromFloat( float $value ): self
    {
        return match (true){
            $value > 0  => self::CLOCKWISE,
            $value < 0  => self::ANTI_CLOCKWISE,
            default     => self::COLLINEAR
        };
    }
}
