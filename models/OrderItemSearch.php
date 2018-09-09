<?php

namespace kmergen\eshop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use kmergen\eshop\models\OrderItem;

/**
 * OrderItemSearch represents the model behind the search form of `kmergen\eshop\models\OrderItem`.
 */
class OrderItemSearch extends OrderItem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'article_id', 'qty'], 'integer'],
            [['title', 'sku', 'data'], 'safe'],
            [['sell_price'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = OrderItem::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'order_id' => $this->order_id,
            'article_id' => $this->article_id,
            'qty' => $this->qty,
            'sell_price' => $this->sell_price,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'sku', $this->sku])
            ->andFilterWhere(['like', 'data', $this->data]);

        return $dataProvider;
    }
}
