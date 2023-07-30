<?php

namespace Maris\Symfony\Geo\Calculator;


use Location\Exception\NotConvergingException;
use Maris\Symfony\Geo\Entity\Location;
use Maris\Symfony\Geo\Interfaces\LocationAggregateInterface as LocationAggregate;
use Maris\Symfony\Geo\Toll\Bearing;

/**
 * Калькулятор Эллипсоидной земли.
 */
class EllipsoidalCalculator extends GeoCalculator
{

    protected const M_2_PI = M_PI * 2;
    protected const M_3_PI = M_PI * 3;
    protected int $iterationsCount = 200;


    /**
     * Вычисляет расстояние между точками
     * @inheritDoc
     */
    public function getDistance( Location|LocationAggregate $start, Location|LocationAggregate $end ): float
    {
        return $this->inverse( $start, $end )["distance"];
    }

    /**
     * Вычисляет начальный азимут между точками.
     * @param Location|LocationAggregate $start
     * @param Location|LocationAggregate $end
     * @return float
     */
    public function getInitialBearing( Location|LocationAggregate $start, Location|LocationAggregate $end ): float
    {
        return $this->getFullBearing( $start, $end )->getInitial();
    }

    /***
     * Вычисляет конечный азимут между точками.
     * @param Location|LocationAggregate $start
     * @param Location|LocationAggregate $end
     * @return float
     */
    public function getFinalBearing( Location|LocationAggregate $start, Location|LocationAggregate $end ): float
    {
        return $this->getFullBearing( $start, $end )->getFinal();
    }

    /***
     * Вычисляет азимуты между двумя точками.
     * @param Location|LocationAggregate $start
     * @param Location|LocationAggregate $end
     * @return Bearing
     */
    public function getFullBearing( Location|LocationAggregate $start, Location|LocationAggregate $end ): Bearing
    {
        return $this->inverse( $start, $end )["bearing"];
    }

    /**
     * Вычисляет ряд А
     * @param float $k
     * @return float
     */
    protected function calcA( float $k ):float
    {
        return (1 +  $k ** 2 / 4) / (1-$k);
    }

    /***
     * Вычисляет ряд В
     * @param float $k
     * @return float
     */
    protected function calcB( float $k ):float
    {
        return $k * ( 1 - 3 * $k ** 2 / 8);
    }

    /**
     * Вычисляет коэффициент для расчета рядов А и В
     * @param float $uSq
     * @return float
     */
    protected function calcK( float $uSq ):float
    {
        return ( ($s = sqrt(1 + $uSq )) - 1 ) / ( $s + 1 );
    }

    /**
     * Вычисляет параметр С.
     * @param float $cosSquAlpha
     * @return float
     */
    protected function calcC( float $cosSquAlpha ):float
    {
        return $this->ellipsoid->flattening() / 16 * $cosSquAlpha *
            ( 4 + $this->ellipsoid->flattening()  * (4 - 3 * $cosSquAlpha) );
    }
    /**
     * Вычисляет U в квадрате.
     * @param $cosSquareAlpha
     * @return float
     */
    protected function calcUSquare( $cosSquareAlpha ):float
    {
        $squareB = $this->ellipsoid->b() ** 2 ;
        return $cosSquareAlpha * ($this->ellipsoid->a() ** 2 - $squareB) / $squareB;
    }

    /**
     * @param float $B
     * @param float $sinSigma
     * @param float $cosSigma
     * @param float $cos2SigmaM
     * @return float
     */
    protected function calcDeltaSigma(float $B, float $sinSigma, float $cosSigma, float $cos2SigmaM):float
    {
        return $B * $sinSigma * ($cos2SigmaM + $B / 4
                * ($cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM) - $B / 6
                    * $cos2SigmaM * (-3 + 4 * $sinSigma * $sinSigma)
                    * (-3 + 4 * $cos2SigmaM * $cos2SigmaM)
                )
            );
    }

    /**
     * Обратная задача
     * @param Location|LocationAggregate $start
     * @param Location|LocationAggregate $end
     * @return array
     */
    public function inverse( Location|LocationAggregate $start, Location|LocationAggregate $end ):array
    {
        $start = $this->pointToLocation( $start );
        $end = $this->pointToLocation( $end );

        $startLat = deg2rad( $start->getLatitude() );
        $endLat = deg2rad($end->getLatitude());
        $startLon = deg2rad( $start->getLongitude() );
        $endLon = deg2rad($end->getLongitude());

        $f = $this->ellipsoid->flattening();

        $L = $endLon - $startLon;

        $tanU1 = (1 - $f) * tan($startLat);
        $cosU1 = 1 / sqrt(1 + $tanU1 * $tanU1);
        $sinU1 = $tanU1 * $cosU1;
        $tanU2 = (1 - $f) * tan($endLat);
        $cosU2 = 1 / sqrt(1 + $tanU2 * $tanU2);
        $sinU2 = $tanU2 * $cosU2;

        $lambda = $L;

        $iterations = 0;

        do {
            $sinLambda = sin($lambda);
            $cosLambda = cos($lambda);
            $sinSqSigma = ($cosU2 * $sinLambda) * ($cosU2 * $sinLambda)
                + ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda) * ($cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda);
            $sinSigma = sqrt($sinSqSigma);

            if ($sinSigma == 0) {
                return [
                    "distance" => 0,
                    "bearing" => (new Bearing())->setInitial( 0 )->setFinal( 0 )
                ];
            }

            $cosSigma = $sinU1 * $sinU2 + $cosU1 * $cosU2 * $cosLambda;
            $sigma = atan2($sinSigma, $cosSigma);
            $sinAlpha = $cosU1 * $cosU2 * $sinLambda / $sinSigma;
            $cosSquAlpha = 1 - $sinAlpha * $sinAlpha;

            /**
             * Устанавливаем на 0 на случай экваториальных линий
             */
            $cos2SigmaM = 0;
            if ($cosSquAlpha !== 0.0) {
                $cos2SigmaM = $cosSigma - 2 * $sinU1 * $sinU2 / $cosSquAlpha;
            }


            $C = $this->calcC( $cosSquAlpha );

            $lambdaP = $lambda;
            $lambda = $L + (1 - $C) * $f * $sinAlpha
                * ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM * $cos2SigmaM)));
            $iterations++;
        } while ($this->iterationsOk( $iterations, $lambda , $lambdaP));

        if ($iterations >= 200) {
            throw new NotConvergingException('Inverse EllipsoidalCalculator Formula did not converge');
        }

        $uSq = $this->calcUSquare( $cosSquAlpha );
        $K = $this->calcK( $uSq );
        $A = $this->calcA( $K );
        $B = $this->calcB( $K );

        $distance = $this->ellipsoid->b() * $A * ( $sigma - $this->calcDeltaSigma( $B, $sinSigma, $cosSigma, $cos2SigmaM ));

        $a1 = atan2($cosU2 * $sinLambda, $cosU1 * $sinU2 - $sinU1 * $cosU2 * $cosLambda);
        $a2 = atan2($cosU1 * $sinLambda, -$sinU1 * $cosU2 + $cosU1 * $sinU2 * $cosLambda);

        $a1 = fmod($a1 + 2 * M_PI, 2 * M_PI);
        $a2 = fmod($a2 + 2 * M_PI, 2 * M_PI);

        return [
            "distance" => $distance,
            "bearing" => (new Bearing())->setInitial( rad2deg($a1) )->setFinal( rad2deg($a2) )
        ];
    }

    /**
     * Реализация прямой задачи.
     * @param Location|LocationAggregate $start
     * @param float $bearing
     * @param float $distance
     * @return array
     */
    public function direct( Location|LocationAggregate $start , float $bearing, float $distance ):array
    {
        $start = $this->pointToLocation( $start );

        $phi1 = deg2rad( $start->getLatitude() );
        $lambda1 = deg2rad( $start->getLongitude() );
        $alpha1 = deg2rad( $bearing );

        $sinAlpha1 = sin( $alpha1 );
        $cosAlpha1 = cos( $alpha1 );

        $tanU1 = (1 - $this->ellipsoid->flattening()) * tan($phi1);
        $cosU1 = 1 / sqrt(1 + $tanU1 * $tanU1);
        $sinU1 = $tanU1 * $cosU1;
        $sigma1 = atan2($tanU1, $cosAlpha1);
        $sinAlpha = $cosU1 * $sinAlpha1;
        $cosSquAlpha = 1 - $sinAlpha * $sinAlpha;


        $K = $this->calcK( $this->calcUSquare( $cosSquAlpha ) );
        $A = $this->calcA( $K );
        $B = $this->calcB( $K );


        $sigmaS = $distance / ( $this->ellipsoid->b() * $A );

        do{
            $cos2SigmaM = cos(2 * $sigma1 + ($sigma ?? $sigma = $sigmaS) );
            $sinSigma = sin($sigma);
            $cosSigma = cos($sigma);
            $deltaSigma = $this->calcDeltaSigma( $B,  $sinSigma,  $cosSigma,  $cos2SigmaM );
            $sigmaS = $sigma;
            $sigma = $distance / ($this->ellipsoid->b() * $A) + $deltaSigma;
        }while( $this->iterationsOk( $i ?? $i = 0, $sigma, $sigmaS  ) );



        $tmp = $sinU1 * $sinSigma - $cosU1 * $cosSigma * $cosAlpha1;
        $phi2 = atan2(
            $sinU1 * $cosSigma + $cosU1 * $sinSigma * $cosAlpha1,
            (1 - $this->ellipsoid->flattening()) * sqrt($sinAlpha * $sinAlpha + $tmp * $tmp)
        );
        $lambda = atan2($sinSigma * $sinAlpha1, $cosU1 * $cosSigma - $sinU1 * $sinSigma * $cosAlpha1);

        $C = $this->calcC( $cosSquAlpha );

        $L = $lambda
            - (1 - $C) * $this->ellipsoid->flattening() * $sinAlpha
            * ($sigma + $C * $sinSigma * ($cos2SigmaM + $C * $cosSigma * (-1 + 2 * $cos2SigmaM ** 2)));

        $lambda2 = fmod($lambda1 + $L + self::M_3_PI, self::M_2_PI ) - M_PI;

        $alpha2 = atan2( $sinAlpha, -$tmp );
        $alpha2 = fmod($alpha2 + self::M_2_PI , self::M_2_PI );

        return [
            "end" => new Location( rad2deg( $phi2 ), rad2deg( $lambda2 ) ),
            "final" => rad2deg( $alpha2 )
        ];

    }

    /***
     * Вспомогательная функция определяющая нужно ли продолжать цикл.
     * @param int $i Текущая итерация.
     * @param float $sigma
     * @param float $sigmaS
     * @return bool
     */
    protected function iterationsOk( int $i, float $sigma , float $sigmaS ):bool
    {
        return $this->iterationsCount > $i && abs($sigma - $sigmaS) > 1e-12;
    }

    /**
     * @inheritDoc
     */
    public function getDestination( Location|LocationAggregate $location, float $initialBearing, float $distance ): Location
    {
        return $this->direct( $location, $initialBearing, $distance)["end"];
    }
}