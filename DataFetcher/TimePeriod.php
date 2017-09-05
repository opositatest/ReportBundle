<?php

namespace Opos\Bundle\ReportBundle\DataFetcher;

use Sylius\Bundle\ReportBundle\DataFetcher\TimePeriod as BaseTimePeriod;

/**
 * @author Odiseo Team <team@odiseo.com.ar>
 */
abstract class TimePeriod extends BaseTimePeriod
{

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
