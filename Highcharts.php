<?php


namespace carono\highcharts;

class Highcharts extends \miloschuman\highcharts\Highcharts
{
    const FORMAT_TOOLTIP_PERCENT = 'percent';

    public function init()
    {
        foreach ($this->options['series'] as $key => $series) {
            if ($series instanceof Series) {
                $this->options['series'][$key] = $series->toArray();
            }
        }
        parent::init();
    }
}