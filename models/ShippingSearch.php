<?php

namespace kmergen\eshop\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use kmergen\eshop\models\Shipping;

/**
 * ShippingSearch represents the model behind the search form of `kmergen\eshop\models\Shipping`.
 */
class ShippingSearch extends Shipping
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'order_id', 'shipping_address_id', 'shipping_company_id'], 'integer'],
            [['status', 'data'], 'safe'],
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
        $query = Shipping::find();

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
            'order_id' => $this->order_id,
            'shipping_address_id' => $this->shipping_address_id,
            'shipping_company_id' => $this->shipping_company_id,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'data', $this->data]);

        return $dataProvider;
    }
}
