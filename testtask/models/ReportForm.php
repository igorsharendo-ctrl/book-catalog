<?php

namespace app\models;

use yii\base\Model;

/**
 * Параметры отчёта «ТОП 10 авторов за год».
 */
class ReportForm extends Model
{
    /**
     * @var int
     */
    public $year;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->year === null) {
            $existing = array_keys(Book::getExistingYearOptions());
            $this->year = in_array(2024, $existing, true) ? 2024 : (int) reset($existing);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['year'], 'required'],
            [['year'], 'integer'],
            [['year'], 'in', 'range' => array_keys(Book::getExistingYearOptions()) ?: array_keys(Book::getYearOptions())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'year' => 'Год выпуска',
        ];
    }

    /**
     * Строки отчёта: автор и число книг за выбранный год.
     *
     * @return array
     */
    public function getRows()
    {
        if (!$this->validate()) {
            return [];
        }

        return Author::getTopByYear((int) $this->year, 10);
    }
}
