<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Geometry;
use Maris\Symfony\Geo\Traits\GeometryRepositoryBoundsSelectableTrait;

/***
 * Класс репозиторий сущности Geometry.
 * @extends ServiceEntityRepository<Geometry>
 *
 * @method Geometry|null find($id, $lockMode = null, $lockVersion = null)
 * @method Geometry|null findOneBy(array $criteria, array $orderBy = null)
 * @method Geometry[]    findAll()
 * @method Geometry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeometryRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Geometry::class );
    }

    /**
     * Выборка фигуры по границам.
     */
    use GeometryRepositoryBoundsSelectableTrait;

    /**
     * Сохраняет сущность.
     * @param Geometry $location
     * @param bool $flush
     * @return void
     */
    public function save( Geometry $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Geometry $location
     * @param bool $flush
     * @return void
     */
    public function remove( Geometry $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }
}