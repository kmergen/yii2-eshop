<?php

namespace kmergen\eshop\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use kmergen\eshop\models\PaymentStatus;

/**
 * PaymentStatusSearch represents the model behind the search form of `kmergen\eshop\models\PaymentStatus`.
 */
class PaymentStateSearch extends PaymentStatus
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'payment_id'], 'integer'],
            [['status', 'provider_status', 'created_at', 'info'], 'safe'],
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
        $query = PaymentStatus::find();

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
            'payment_id' => $this->payment_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'provider_status', $this->provider_status])
            ->andFilterWhere(['like', 'info', $this->info]);

        return $dataProvider;
    }
}
