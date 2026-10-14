<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Поиск и фильтрация книг в каталоге.
 */
class BookSearch extends Book
{
    /**
     * @var string фильтр по ФИО автора
     */
    public $authorName;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'issue_year'], 'integer'],
            [['title', 'isbn', 'authorName'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'authorName' => 'Автор',
        ]);
    }

    /**
     * Поставщик данных для GridView.
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Book::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['issue_year' => SORT_DESC, 'title' => SORT_ASC],
                'attributes' => [
                    'id' => [
                        'asc' => ['{{%book}}.id' => SORT_ASC],
                        'desc' => ['{{%book}}.id' => SORT_DESC],
                    ],
                    'title' => [
                        'asc' => ['{{%book}}.title' => SORT_ASC],
                        'desc' => ['{{%book}}.title' => SORT_DESC],
                    ],
                    'issue_year' => [
                        'asc' => ['{{%book}}.issue_year' => SORT_ASC],
                        'desc' => ['{{%book}}.issue_year' => SORT_DESC],
                    ],
                    'isbn' => [
                        'asc' => ['{{%book}}.isbn' => SORT_ASC],
                        'desc' => ['{{%book}}.isbn' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->authorName !== null && $this->authorName !== '') {
            $query->innerJoinWith(['authors' => static function ($authorQuery) {
                $authorQuery->orderBy([]);
            }])->distinct();
            $query->andFilterWhere(['like', '{{%author}}.full_name', $this->authorName]);
        }

        $query->andFilterWhere([
            '{{%book}}.id' => $this->id,
            '{{%book}}.issue_year' => $this->issue_year,
        ]);

        $query->andFilterWhere(['like', '{{%book}}.title', $this->title])
            ->andFilterWhere(['like', '{{%book}}.isbn', $this->isbn]);

        return $dataProvider;
    }
}
