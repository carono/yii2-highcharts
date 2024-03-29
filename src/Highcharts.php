<?php


namespace carono\highcharts;

use yii\helpers\ArrayHelper;

class Highcharts extends \miloschuman\highcharts\Highcharts
{
    const FORMAT_TOOLTIP_PERCENT = 'percent';

    public $dataProvider;
    public $group;
    public $serialOptions = [];
    public $x;
    public $y;
    public $pk = 'id';

    protected static function applyFormat($value, $format)
    {
        if (\is_callable($format)) {
            return $format($value);
        }
        return \call_user_func([\Yii::$app->formatter, 'as' . ucfirst($format)], $value);
    }

    public function init()
    {
        if ($this->dataProvider) {
            $models = $this->dataProvider->models;
            $xAxis = $this->x ?: ArrayHelper::getValue($this->options, 'xAxis.field');
            $yAxis = $this->y ?: ArrayHelper::getValue($this->options, 'yAxis.field');
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
            $group = $this->group;

            $models = ArrayHelper::map($models, function ($data) {
                $key = [];
                foreach ((array)$this->pk as $pk) {
                    $key[] = ArrayHelper::getValue($data, $pk);
                }
                return implode('-', $key);
            }, function ($data) {
                return $data;
            }, $group);

            foreach ((array)$yAxis as $yAxis1) {
                foreach ($group ? $models : [$models] as $id => $data) {
                    $series = new Series();
                    $seriesOptions = ArrayHelper::getValue($this->serialOptions, ArrayHelper::getValue(reset($data), $group));
                    $series->name = ArrayHelper::getValue($seriesOptions, 'name', ($group ? ArrayHelper::getValue(current($data), $group) . ": " : '') . $yAxis1);
                    $series->color = ArrayHelper::getValue($seriesOptions, 'color');
                    $items = [];
                    foreach ($data as $datum) {
                        $dataX = ArrayHelper::getValue($datum, $xAxis);
                        $key = $format ? self::applyFormat($dataX, $format) : $dataX;
                        $items[$key] = ArrayHelper::getValue($datum, $yAxis1);
                    }
                    foreach ($categories as $category) {
                        $series->data[] = ArrayHelper::getValue($items, $category);
                    }
                    $this->options['series'][] = $series;
                }
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