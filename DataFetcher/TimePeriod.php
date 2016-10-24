<?php

namespace OpositaTest\Bundle\ReportBundle\DataFetcher;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Sylius\Bundle\ReportBundle\DataFetcher\TimePeriod as BaseTimePeriod;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
abstract class TimePeriod extends BaseTimePeriod
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Devuelve una cadena concatenada de todos los tipos de agrupación pasados
     * por la configuración.
     *
     * @param array $configuration
     * @return string
     */
    protected function getGroupBy(array $configuration = [])
    {
        $groupBy = '';

        foreach ($configuration['groupBy'] as $groupByElement) {
            $groupBy = $groupByElement.'(date)'.' '.$groupBy;
        }

        $groupBy = substr($groupBy, 0, -1);
        $groupBy = str_replace(' ', ', ', $groupBy);

        return $groupBy;
    }

    protected function addTimePeriodQueryBuilder(QueryBuilder $queryBuilder, array $configuration = [])
    {
        $groupBy = $this->getGroupBy($configuration);

        $queryBuilder
            ->andWhere($queryBuilder->expr()->gte('o.completed_at', ':from'))
            ->andWhere($queryBuilder->expr()->lte('o.completed_at', ':to'))
            ->setParameter('from', $configuration['start']->format('Y-m-d H:i:s'))
            ->setParameter('to', $configuration['end']->format('Y-m-d H:i:s'))
            ->groupBy($groupBy)
            ->orderBy($groupBy)
        ;

        return $queryBuilder;
    }

    /**
     * Obtiene los resultados pasados en $datas y los devuelve en forma de promedio
     * agrupándolos en fecha segun el formato (presentationFormat) que se provea en
     * $configuration
     *
     * @param array $datas
     * @param array $configuration
     * @return array
     */
    protected function getMediaResults(array $datas = [], array $configuration = [])
    {
        if (empty($datas)) {
            return [];
        }

        $labels = array_keys($datas[0]);

        $datesMedia = [];
        foreach($datas as $data)
        {
            $date = new \DateTime($data[$labels[0]]);
            $dateFormated = $date->format($configuration['presentationFormat']);

            $currentDateMedia = isset($datesMedia[$dateFormated])?$datesMedia[$dateFormated]:array('quantity' => 0, 'media' => 0);

            $currentDateMedia['quantity'] = $currentDateMedia['quantity']+1;
            $currentDateMedia['media'] = $currentDateMedia['media']+$data[$labels[1]];

            $datesMedia[$dateFormated] = $currentDateMedia;
        }

        $fetched = [];
        foreach($datesMedia as $date => $dateMedia)
        {
            $fetched[] = [
                $labels[0] => $date,
                $labels[1] => round($dateMedia['media']/$dateMedia['quantity'], 1)
            ];
        }

        return $fetched;
    }
}
