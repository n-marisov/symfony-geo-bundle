<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Polygon;
use Maris\Symfony\Geo\Traits\GeometryRepositoryBoundsSelectableTrait;

/***
 * Класс репозиторий сущности Geometry.
 * @extends ServiceEntityRepository<Polygon>
 *
 * @method Polygon|null find($id, $lockMode = null, $lockVersion = null)
 * @method Polygon|null findOneBy(array $criteria, array $orderBy = null)
 * @method Polygon[]    findAll()
 * @method Polygon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PolygonRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Polygon::class );
    }

    /**
     * Выборка фигуры по границам.
     */
    use GeometryRepositoryBoundsSelectableTrait;

    /**
     * Сохраняет сущность.
     * @param Polygon $location
     * @param bool $flush
     * @return void
     */
    public function save( Polygon $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Polygon $location
     * @param bool $flush
     * @return void
     */
    public function remove( Polygon $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }


}