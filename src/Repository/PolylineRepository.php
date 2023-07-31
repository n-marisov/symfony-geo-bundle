<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Polyline;
use Maris\Symfony\Geo\Traits\GeometryRepositoryBoundsSelectableTrait;

/***
 * Класс репозиторий сущности Polyline.
 * @extends ServiceEntityRepository<Polyline>
 *
 * @method Polyline|null find($id, $lockMode = null, $lockVersion = null)
 * @method Polyline|null findOneBy(array $criteria, array $orderBy = null)
 * @method Polyline[]    findAll()
 * @method Polyline[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PolylineRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Polyline::class );
    }

    /**
     * Выборка фигуры по границам.
     */
    use GeometryRepositoryBoundsSelectableTrait;

    /**
     * Сохраняет сущность.
     * @param Polyline $location
     * @param bool $flush
     * @return void
     */
    public function save( Polyline $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Polyline $location
     * @param bool $flush
     * @return void
     */
    public function remove( Polyline $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }


}