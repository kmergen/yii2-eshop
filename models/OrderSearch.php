<?php

namespace kmergen\eshop\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use kmergen\eshop\models\Order;

/**
 * OrderSearch represents the model behind the search form of `kmergen\eshop\models\Order`.
 */
class OrderSearch extends Order
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'invoice_address_id'], 'integer'],
            [['status', 'ip', 'notes', 'created_at', 'updated_at'], 'safe'],
            [['total'], 'number'],
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
        $query = Order::find();

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
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'total' => $this->total,
            'invoice_address_id' => $this->invoice_address_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', $this->data])
            ->andFilterWhere(['like', 'ip', $this->ip])
            ->andFilterWhere(['like', 'notes', $this->notes]);

        return $dataProvider;
    }
}
