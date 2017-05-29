<?php

namespace ParsingBundle\Service;


class AvitoDateParser
{
    /**
     * @param string $semanticDate
     *
     * @return \DateTime|null
     */
    public function __invoke($semanticDate)
    {
        $semanticDate = mb_strtolower(trim($semanticDate));

        $todayOrTomorrowDate = $this->parseTodayOrTomorrowDate($semanticDate);

        if ($todayOrTomorrowDate instanceof \DateTime) {
            return $todayOrTomorrowDate;
        }

        $monthBasedDate = $this->parseMonthBasedDate($semanticDate);

        if ($monthBasedDate instanceof \DateTime) {
            return $monthBasedDate;
        }

        $yearBasedDate = $this->parseYearBasedDate($semanticDate);

        if ($yearBasedDate instanceof \DateTime) {
            return $yearBasedDate;
        }

        return null;
    }

    public function parseMonthBasedDate($semanticDate)
    {
        if (0 === preg_match('/^(\d{1,2})\s(\w+)\s(\d{1,2})\:(\d{1,2})$/u', $semanticDate, $matches)) {
            return null;
        }

        list ($ignored, $day, $month, $hour, $minute) = $matches;

        $monthsMap = $this->getMonthsMap();

        if (!array_key_exists($month, $monthsMap)) {
            return null;
        }

        $month = $monthsMap[$month];

        $date = new \DateTime('today');

        $date->setDate($date->format('Y'), $month, (int)$day);
        $date->setTime((int)$hour, (int)$minute);

        return $date;
    }

    private function parseYearBasedDate($semanticDate)
    {
        if (0 === preg_match('/^(\d{1,2})\s(\w+)\s(\d{4})$/u', $semanticDate, $matches)) {
            return null;
        }

        list ($ignored, $day, $month, $year) = $matches;

        $monthsMap = $this->getMonthsMap();

        if (!array_key_exists($month, $monthsMap)) {
            return null;
        }

        $month = $monthsMap[$month];

        $date = new \DateTime('today');

        $date->setDate($year, $month, (int)$day);

        return $date;
    }


    private function parseTodayOrTomorrowDate($semanticDate)
    {
        if (0 === preg_match('/^(вчера|сегодня)\s(\d{1,2})\:(\d{1,2})$/u', $semanticDate, $matches)) {
            return null;
        }

        list ($ignored, $day, $hour, $minute) = $matches;

        $date = new \DateTime($day === 'сегодня' ? 'today' : 'tomorrow');

        $date->setTime((int)$hour, (int)$minute);

        return $date;
    }

    private function getMonthsMap()
    {
        return [
            'январь' => 1,
            'февраль' => 2,
            'март' => 3,
            'апрель' => 4,
            'май' => 5,
            'июнь' => 6,
            'июль' => 7,
            'август' => 8,
            'сентябрь' => 9,
            'октябрь' => 10,
            'ноябрь' => 11,
            'декабрь' => 12,
            'января' => 1,
            'февраля' => 2,
            'марта' => 3,
            'апреля' => 4,
            'мая' => 5,
            'июня' => 6,
            'июля' => 7,
            'августа' => 8,
            'сентября' => 9,
            'октября' => 10,
            'ноября' => 11,
            'декабря' => 12,
        ];
    }
}