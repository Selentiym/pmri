<?php

/**
 * This is the model class for table "{{exp_experiments}}".
 *
 * The followings are the available columns in table '{{exp_experiments}}':
 * @property integer $id
 * @property integer $id_enter
 * @property double $price
 * @property string $date
 * @property string $theme
 * @property integer $isMobile
 * @property integer $formSent
 */
class GlobalExperiment extends landingDataModel
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{exp_experiments}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id_enter, price, date', 'required'),
			array('id_enter, isMobile, formSent', 'numerical', 'integerOnly'=>true),
			array('price', 'numerical'),
			array('theme', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, id_enter, price, date, theme, isMobile, formSent', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'id_enter' => 'Id Enter',
			'price' => 'Price',
			'date' => 'Date',
			'theme' => 'Theme',
			'isMobile' => 'Is Mobile',
			'formSent' => 'Form Sent',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('id_enter',$this->id_enter);
		$criteria->compare('price',$this->price);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('theme',$this->theme,true);
		$criteria->compare('isMobile',$this->isMobile);
		$criteria->compare('formSent',$this->formSent);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return GlobalExperiment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
