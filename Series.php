<?php


namespace carono\highcharts;


use yii\base\Object;
use yii\data\DataProviderInterface;
use yii\helpers\ArrayHelper;

class Series extends Object
{
    public $name;
    /**
     * @var DataProviderInterface
     */
    public $dataProvider;
    public $data;
    public $field;
    public $color;

    public function toArray()
    {
        $result = ['name' => $this->name];
        $data = [];
        if ($this->data) {
            $data = $this->data;
            if ($data && is_array(reset($data)) && $this->field) {
                $data = ArrayHelper::getColumn($data, $this->field);
            }
        } elseif ($this->dataProvider) {
            $items = $this->dataProvider->getModels();
            $data = ArrayHelper::getColumn($items, $this->field);

        }
        foreach ($data as &$datum) {
            if (is_numeric($datum)) {
                $datum = floatval($datum);
            }
        }
        $result['data'] = $data;
        $result['color'] = $this->color;
        return $result;
    }
}