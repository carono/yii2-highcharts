<?php


namespace carono\highcharts;

use yii\helpers\ArrayHelper;

class Highcharts extends \miloschuman\highcharts\Highcharts
{
    const FORMAT_TOOLTIP_PERCENT = 'percent';
    public $dataProvider;
    public $group;
    public $serialOptions = [];

    protected static function applyFormat($value, $format)
    {
        if (is_callable($format)) {
            return call_user_func($format, $value);
        } else {
            return call_user_func([\Yii::$app->formatter, 'as' . ucfirst($format)], $value);
        }
    }

    public function init()
    {
        if ($this->dataProvider) {
            $models = $this->dataProvider->models;
            $xAxis = ArrayHelper::getValue($this->options, 'xAxis.field');
            $yAxis = ArrayHelper::getValue($this->options, 'yAxis.field');
            $arr = explode(':', $xAxis);
            if (!$format = ArrayHelper::getValue($arr, 1)) {
                $format = ArrayHelper::getValue($this->options, 'xAxis.format');
            }
            $xAxis = ArrayHelper::getValue($arr, 0);
            $categories = ArrayHelper::map($models, $xAxis, $xAxis);
            if ($format) {
                array_walk($categories, function (&$data) use ($format) {
                    $data = self::applyFormat($data, $format);
                });
            }
            $categories = array_values(array_unique($categories));
            $models = ArrayHelper::map($models, 'id', function ($data) {
                return $data;
            }, $this->group);
            foreach ($models as $id => $data) {
                $series = new Series();
                $seriesOptions = ArrayHelper::getValue($this->serialOptions, reset($data)->{$this->group});
                $series->name = ArrayHelper::getValue($seriesOptions, 'name');
                $series->color = ArrayHelper::getValue($seriesOptions, 'color');
                $items = [];
                foreach ($data as $datum) {
                    $dataX = $datum->{$xAxis};
                    $key = $format ? self::applyFormat($dataX, $format) : $dataX;
                    $items[$key] = $datum->{$yAxis};
                }
                foreach ($categories as $category) {
                    $series->data[] = ArrayHelper::getValue($items, $category);
                }
                $this->options['series'][] = $series;
            }
            $this->options['xAxis']['categories'] = $categories;
        }
        foreach (ArrayHelper::getValue($this->options, 'series', []) as $key => $series) {
            if ($series instanceof Series) {
                $this->options['series'][$key] = $series->toArray();
            }
        }
        parent::init();
    }
}