<?php

namespace Maris\Symfony\Geo\Toll;

/***
 * Эллипсоид земли.
 */
enum Ellipsoid
{
    /***
     * Всемирная геодезическая система 1966
     */
    case WGS_66;

    /***
     * Всемирная геодезическая система 1972
     */
    case WGS_72;

    /**
     * Геодезическая система отсчета 1980
     */
    case GRS_80;

    /***
     * Всемирная геодезическая система 1984
     */
    case WGS_84;

    /**
     * @return float
     */
    public function a():float
    {
        return match ($this){
            self::WGS_66 => 6378145.0,
            self::WGS_72 => 6378135.0,
            self::WGS_84, self::GRS_80 => 6378137.0,
        };
    }

    /**
     * @return float
     */
    public function b():float
    {
        return match ($this){
            self::WGS_66 => 6356759.7694887,
            self::WGS_72 => 6356750.5200161,
            self::GRS_80 => 6356752.3141403,
            self::WGS_84 => 6356752.3142452,
            #default => $this->a() * (1 - 1 / $this->f())
        };
    }

    /**
     * Обратное уплощение
     * @return float
     */
    public function reverseFlattening():float
    {
        return match ($this){
            self::WGS_66 => 298.25,
            self::WGS_72 => 298.26,
            self::GRS_80 => 298.25722210088,
            self::WGS_84 => 298.257223563,
        };
    }

    /**
     * Коэффициент уплощение земли.
     * @return float
     */
    public function flattening():float
    {
        return match ($this){
            self::WGS_66 => 0.0033528918692372,
            self::WGS_72 => 0.0033527794541675,
            self::GRS_80 => 0.0033528106811837,
            self::WGS_84 => 0.0033528106647475,
            //default => 1 / $this->reverseF()
        };
    }

    /**
     * @return float
     */
    public function r():float
    {
        return match ($this){
            self::WGS_66 => 6371016.5898296,
            self::WGS_72 => 6371006.8400054,
            self::GRS_80 => 6371008.7713801,
            self::WGS_84 => 6371008.7714151,
            #default => $this->a() * (1 - 1 / $this->f() / 3)
        };
    }

    public static function from( ?string $name , self $default = Ellipsoid::WGS_84 ):self
    {
        return match ($name){
            "WGS_66" => self::WGS_66 ,
            "WGS_72" => self::WGS_72,
            "GRS_80" => self::GRS_80,
            "WGS_84" => self::WGS_84,
            default => $default
        };
    }
}