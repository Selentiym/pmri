<?php

/**
 * This is the model class for table "{{client_phone}}".
 *
 * The followings are the available columns in table '{{client_phone}}':
 * @property string $mangoTalker
 * @property integer $id_phone
 */
class ClientPhone extends UModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{client_phone}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('mangoTalker, id_phone', 'required'),
			array('id_phone', 'numerical', 'integerOnly'=>true),
			array('mangoTalker', 'length', 'max'=>128),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('mangoTalker, id_phone', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'phone' => array(self::BELONGS_TO, 'UserPhone','id_phone')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'mangoTalker' => 'Mango Talker',
			'id_phone' => 'Id Phone',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('mangoTalker',$this->mangoTalker,true);
		$criteria->compare('id_phone',$this->id_phone);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ClientPhone the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
