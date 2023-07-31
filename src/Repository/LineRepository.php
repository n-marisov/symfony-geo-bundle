<?php

namespace Maris\Symfony\Geo\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Maris\Symfony\Geo\Entity\Line;
use Maris\Symfony\Geo\Traits\GeometryRepositoryBoundsSelectableTrait;

/***
 * Класс репозиторий сущности Geometry.
 * @extends ServiceEntityRepository<Line>
 *
 * @method Line|null find($id, $lockMode = null, $lockVersion = null)
 * @method Line|null findOneBy(array $criteria, array $orderBy = null)
 * @method Line[]    findAll()
 * @method Line[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LineRepository extends ServiceEntityRepository
{
    public function __construct( ManagerRegistry $registry )
    {
        parent::__construct( $registry, Line::class );
    }

    /**
     * Выборка фигуры по границам.
     */
    use GeometryRepositoryBoundsSelectableTrait;

    /**
     * Сохраняет сущность.
     * @param Line $location
     * @param bool $flush
     * @return void
     */
    public function save( Line $location , bool $flush = false ):void
    {
        $this->getEntityManager()->persist($location);
        if($flush)
            $this->getEntityManager()->flush();
    }

    /**
     * Удаляет сущность.
     * @param Line $location
     * @param bool $flush
     * @return void
     */
    public function remove( Line $location , bool $flush = false ):void
    {
        $this->getEntityManager()->remove($location);
        if($flush)
            $this->getEntityManager()->flush();
    }


}