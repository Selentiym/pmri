<?php
	class Call {
		public $id_address;
		public $array;
		public $State;
		public $dateString;
		public $report;
		public $i;
		public $j;
		public $fio;
		public $H;
		public $wishes;
		public $birth;
		public $number;
		public $clinic;
		public $price;
		public $mangoTalker;
		public $comment;
		public $research_type;
		public $id_error;
		public $IFromFile = false;
		public $assignDate;
		public $callTime;
		public function __construct($array = array(), $header = array()){
			$this->array = $array;
			if ($header) {
				$num = array_flip(array_map('trim',$header));
				$this -> dateString = $array[$num["Дата"]];
				$this -> State = $array[$num["sa"]];
				$this -> report = $array[$num["Отчет по звонку"]];
				$this -> research_type = $array[$num["Тип исследования"]];
				//$this -> i = $array[2];
				$data = trim($array[$num["Н"]]);
				//echo $data;
				if (preg_match('/id\d+/',$data)) {
					$this -> i = str_replace("id","",$data);
					$this -> IFromFile = true;
					//echo "i";
				} elseif (preg_match('/^\d+$/',$data)) {
					$this -> j = $data;
					//echo "j";
				} elseif (is_string($data)) {
					$this -> H = $data;
					//echo "H";
				}
				//$this -> j = $array[3];
				//$this -> H = $array[4];
				$this -> fio = $array[$num["ФИО"]];
				$this -> wishes = $array[$num["Пожелания клиента"]];
				$this -> birth = $array[$num["Дата рождения"]];
				$this -> number = $array[$num["Контактный телефон"]];
				$this -> clinic = $array[$num["Клиника"]];
				$this -> price = $array[$num["Цена"]];
				$this -> mangoTalker = $array[$num["MangoTalker номер"]];
				$this -> comment = $array[$num["Комментарий"]];
			} else {
				if (!empty($array)) {
					$data = $array[2];
					if (preg_match('/id\d+/',$data)) {
						$this -> i = str_replace("id","",$data);
					} elseif (preg_match('/^\d+$/',$data)) {
						$this -> j = $data;
					} elseif (is_string($data)) {
						$this -> H = $data;
					}
					$this -> dateString = $array[0];
					$this -> State = $array[12];
					$this -> report = $array[9];
					$this -> research_type = $array[1];
					//$this -> i = $array[2];
					//$this -> j = $array[3];
					$this -> fio = $array[4];
					//$this -> H = $array[4];
					$this -> wishes = $array[3];
					$this -> birth = $array[5];
					$this -> number = $array[6];
					$this -> clinic = $array[7];
					$this -> price = $array[8];
					$this -> mangoTalker = $array[10];
					$this -> comment = $array[11];
				}
			}
		}
		public function giveDate() {
			$dateArr = explode('.', $this -> dateString);
			if (count($dateArr) < 2) {
				$dateArr = explode('/', $this -> dateString);
			}
			if (count($dateArr) < 2) {
				$dateArr = explode('\\', $this -> dateString);
			}
			if (count($dateArr) < 2) {
				$dateArr = explode(',', $this -> dateString);
			}
			$rez['day'] = (int)$dateArr[0];
			$rez['mon'] = (int)$dateArr[1];
			//$rez['year'] = $dateArr[2];
			return $rez;
		}
		public function giveUnixTime(){
			$date = $this -> giveDate();
			return mktime(12,0,0,$date['mon'],$date['day'],$this -> getYear());
		}
		/**
		 * @return string - the report on the call
		 */
		public function giveReport(){
			if (preg_match('/[a-zA-Zа-яА-Я]/',$this -> report)){
				return $this -> report;
			} else {
				$arr = explode(',',$this -> report);
				$day_month = explode('/',$arr[2]);
				return 'Записан на '.$arr[0].':'.$arr[1].' '.$arr[2];
			}
		}
		/**
		 * @arg object user - the user that may be the master of this call
		 * @return boolean - whether the user is or is not the master
		 */
		public function BelongsTo($user){
			return ($user -> checkIIdentificator($this -> i) || ($user -> checkJIdentificator($this -> j)));
			//return true;
		}
		/**
		 * @return int - the year of the call
		 */
		public function getYear(){
			return Setting::getYear();
		}
		/**
		 * @return int - the time of call.
		 */
		public function giveCallTime(){
			if (!$this -> callTime) {
				$this -> callTime = strtotime("12:00:00 ".$this->dateString . '.' . $this->getYear());
			}
			return $this -> callTime;
		}
		/**
		 * @arg int $from - the earliest second call is ok
		 * @return boolean - whether the call is after $from
		 */
		public function checkLowerBoundary($from){
			$time = $this -> giveCallTime();
			return ($time >= $from);
		}
		/**
		 * @param int $to - the latest second call is ok
		 * @return boolean - whether the call is before $to
		 */
		public function checkUpperBoundary($to){
			$time = $this -> giveCallTime();
			return ($time <= $to);
		}
		/**
		 * @return string - the type of call. They are array('verifyed', 'missed', 'cancelled', 'side', 'declined', 'assigned')
		 */
		public function Classify(){
			//Если у звонка проставлен статус(поле SA), то смотрим на него.
			switch ($this ->State) {
				case 'Y':
					return 'verifyed';
				break;
				case 'N':
					return 'missed';
				break;
				case 'O':
					return 'cancelled';
				break;
			}
			//Если есть слово отмена в отчете, значит запись отменена.
			if (strstr($this -> report, 'отмена')) {
				return 'cancelled';
			}
			//Если есть слово спам в отчете, значит звонок нецелевой.
			if (strstr($this -> report, 'спам')) {
				return 'side';
			}
			//Если в поле "отчет" есть буквы, то НЕ записан.
			if (preg_match('/[a-zA-Zа-яА-Я]/',$this -> report)) {
				return 'declined';
			} else {
				return 'assigned';
			}
		}
		/**
		 * @return int - the id of the type of call.
		 */
		public function ClassifyId(){
			return CallType::model() -> findByAttributes(array('string' => $this -> Classify())) -> id;
		}
		/**
		 * @return BaseCall|false - the model of the call corresonding to this line in the csv file.
		 * If the DB record is not found false is returned
		 */
		public function record(){
			$criteria = new CDbCriteria;
			$this -> id_call_type = $this -> ClassifyId();
			//echo $this -> id_call_type;
			//Проблема, тк могут быть время может быть проставлено по записи.
			if (!(($this -> id_call_type == CallType::model() -> getNumber('verifyed'))||($this -> id_call_type == CallType::model() -> getNumber('assigned'))||($this -> id_call_type == CallType::model() -> getNumber('missed'))||($this -> id_call_type == CallType::model() -> getNumber('cancelled')))) {
				$criteria -> addCondition('TO_DAYS(date) = TO_DAYS(FROM_UNIXTIME('.$this -> giveUnixTime().'))');
				//echo "side zapis";
				/*$time = new DateTime();
				echo $this -> giveUnixTime();
				$time -> setTimestamp($this -> giveUnixTime());
				echo $time -> format(DateTime::W3C);*/
			} else {
				//echo "good zapis";
				$matches = array();
				preg_match('/(\d*[.\/,]\d*\W*\d*[.\/,]\d*)/',$this -> report, $matches);
				//var_dump($matches);
				$criteria -> compare('report', $matches[0], $partialMatch=true);
			}
			//
			//Временно заменяем проверку по дате на проверку по отчету.
			
			//$criteria -> params = array(':date' => $this -> giveUnixTime());
			$criteria -> compare('fio', $this -> fio);
			if ($this -> mangoTalker) {
				$criteria -> compare('mangoTalker', $this -> mangoTalker);
			}
			//echo $this -> research_type;
			if ($this -> research_type) {
				$criteria -> compare('research_type', $this -> research_type);
			}
			if ($this -> j) {
				$criteria -> compare('j', $this -> j);
			} else {
				$criteria -> addCondition('j IS NULL');
			}
			//echo $this -> fio."<br/>";
			
			/*echo "<br/>";
			print_r(getdate($this -> giveUnixTime()));
			echo "<br/>";*/
			if ($bcall = BaseCall::model() -> find($criteria)) {
				return $bcall;
			} else {
				return false;
			}
			//$record = BaseCall::model() -> findByAttributes(array());
		}
		/**
		 * @return object[User] - the owner of this record.
		 */
		public function giveOwner() {
			//Если задан номер направления, ищем, прежде всего, по нему.
			
			if ($this -> j) {
				//echo "j<br/>";
				$criteria = new CDbCriteria();
				//$criteria -> compare('jMin','<=:',$this -> j);
				$criteria -> addCondition('jMin <= '.$this -> j);
				//$criteria -> compare('jMax','>=:', $this -> j);
				$criteria -> addCondition('jMax >= '.$this -> j);
				$users = Range::model() -> giveDoctors($criteria);
				/*$criteria -> compare('id_type', UserType::model() -> getNumber('doctor'));
				$users = User::model() -> findAll($criteria);
				//Ищем не только по основному интервалу, но и по дополнительному.
				$criteria = new CDbCriteria();
				//$criteria -> compare('jMin','<=:',$this -> j);
				$criteria -> addCondition('jMin_add <= '.$this -> j);
				//$criteria -> compare('jMax','>=:', $this -> j);
				$criteria -> addCondition('jMax_add >= '.$this -> j);
				$criteria -> compare('id_type', UserType::model() -> getNumber('doctor'));
				$users = array_merge($users, User::model() -> findAll($criteria));*/
				
				/*foreach($users as $user) {
					echo "<br/>".$user -> username;
				}*/
				switch (count($users)) {
					case 0:
						$this -> id_error = CallError::model() -> giveText('invalid_j');
						//Нужно ничего не делать.
						//echo '';
					break;
					case 1:
						$this -> id_error = CallError::model() -> giveText('good');
						return current($users);
					break;
					default:
						//print_r($users);
						$this -> id_error = CallError::model() -> giveText('two_j');
						//echo "<br/><br/>".$this -> j;
						new CustomFlash('error','BaseCall','Multiple_j_assign','Номер направления'.$this -> j.' присвоен более чем одному простому доктору. Из-за этого невозможно определить принадлежность звонка.',true);
						return NULL;
				}
			}
			if (!$this -> IFromFile) {
				$this -> lookForIAttribute();
				//echo "123 - ".$this -> i."<br/>";
			}
			
			//Если задана линия, то ищем по ней с учетом адреса.
			if ($this -> i) {
				//Пользователю вконтакте номер 407-10-24
				if ($this -> i == 23) {
					return User::model() -> findByPk(554);
				}
				//Пользователю Сергей номера 241-10-52, 241-10-58, 241-10-63
				if (($this -> i == 27)||($this -> i == 29)||($this -> i == 28)) {
					return User::model() -> findByPk(659);
				}
				//echo "i<br/>";
				//нашли линию с юзерами, у которых есть она.
				$phone = UserPhone::model() -> findByAttributes(array('i' => $this -> i));
				//Если нашли телефон, то берем его обладателей.
				if ($phone) {
					$users = $phone -> regular_users;
				}
				//Если их не нащлось, говорим, что их нет.
				if ((!$users)||(count($users) == 0)) {
					$this -> id_error = CallError::model() -> giveText('invalid_i');
					return NULL;
				}
				//Если оператор указал, что данный пользователь не участвует в шахматке, то не проверяем аресов.
				if (!$this -> IFromFile) {
					//Если задан адрес, то проверяем по нему
					if ((trim($this -> H))||($this -> id_address)) {
						//echo "H:". $this -> H;
						//echo "id_address:". $this -> id_address;//*/
						if (!$this -> H) {
							$this -> H = UserAddress::model() -> findByPk($this -> id_address) -> address;
						}
						if (!$this -> id_address) {
							$this -> id_address = UserAddress::model() -> findByAttributes(array('address' => $this -> H)) -> id;
						}
						//Проверяем юзера на наличие нужного адреса, если объект адреса найден.
						if ($this -> id_address) {
							//echo $this -> id_address;
							$id = $this -> id_address;
							$users = array_filter($users, function ($user) use ($id){
								//print_r(CHtml::giveAttributeArray( $user -> address_array,'id'));
								return in_array($id, CHtml::giveAttributeArray($user -> address_array,'id'));
							});
						} else {
							//Выдаем пустой результат, если не смогли определить адрес.
							//new CustomFlash('error','Call','addressNotFound','Адрес не найден');
							$this -> id_error = CallError::model() -> giveText('not_found_a');
							return NULL;
						}
					} else {
						if (count($users)!=1) {
							$this -> id_error = CallError::model() -> giveText('not_found_a');
							return NULL;
							//Если не задано ничего, что связано с адресом, но задан номер (причем не в файле, а по mango), то не прицепляем простым юзерам.
							//$users = array();
						}
					}
				}
					
				//print_r($users);
				//echo count($users);
				/*foreach($users as $user) {
					echo "<br/>".$user -> username;
				}*/
				
				switch (count($users)) {
					case 0:
						$this -> id_error = CallError::model() -> giveText('invalid_a');
						/* Временно! Для добавления звонков, принадлежащих чисто медпреду в его статистику */
						
						/*$users = $phone -> main_users;
						//print_r($users); echo "<br/>";
						if (count($users) == 1) {
							new CustomFlash('warning','DataFromCsv','CallAddedOnlyToMD','Звонок добавлен только лишь медпреду, но не доктору.',true);
							return current($users);
						}*/
						//new CustomFlash('warning','DataFromCsv','CallAddedOnlyToMD','Звонок добавлен только лишь медпреду, но не доктору.',true);
						/* Конец временного участка */
						return NULL;
						//new CustomFlash('error','BaseCall','Multiple_phone_assign','',true);
					break;
					case 1:
						$this -> id_error = CallError::model() -> giveText('good');
						//echo current($users) -> fio;
						return current($users);
					break;
					default:
						$this -> id_error = CallError::model() -> giveText('two_i');
						new CustomFlash('error','BaseCall','Multiple_phone_assign','Номер с индентификатором '.$phone -> i.' присвоен более чем одному простому доктору с совпадающими адресами. Из-за этого невозможно определить принадлежность звонка.',true);
						return NULL;
				}
				//print_r($phone -> regular_users);
			}
			if (!$this -> H) {
				$this -> id_error = CallError::model() -> giveText('no_data');
			} else {
				$this -> id_error = CallError::model() -> giveText('only_h');
			}
			return NULL;
		}
		/**
		 * Checks the ClientPhone table and sets $this -> i if there is a record corresponding to user's mangoTalker.
		 */
		public function lookForIAttribute(){
			$CPhone = ClientPhone::model() -> findByAttributes(array('mangoTalker' => $this -> mangoTalker), array('with' => 'phone'));
			if ((!$CPhone)&&(preg_match('/^7812\d+/',$this -> mangoTalker))) {
				$CPhone = ClientPhone::model() -> findByAttributes(array('mangoTalker' => str_replace('7812','',$this -> mangoTalker)), array('with' => 'phone'));
			}
			
			if ($CPhone) {
				$this -> i = $CPhone -> phone -> i;
			} else {
				unset($this -> i);
			}
		}
		/**
		 * Sets the attributes of the given BaseCall model with own attributes.
		 * @param BaseCall $record - the record attributes of which are to be set
		 */
		public function setRecordAttributes(BaseCall $record){
			$record -> research_type = $this -> research_type;
			$record -> i = $this -> i ? $this -> i : NULL;
			$record -> j = $this -> j ? $this -> j : NULL;
			$record -> H = $this -> H ? $this -> H : NULL;
			
			$record -> wishes = $this -> wishes;
			$record -> fio = $this -> fio;
			$record -> birth = $this -> birth;
			$record -> number = $this -> number;
			$record -> clinic = $this -> clinic;
			$record -> price = $this -> price;
			$record -> report = $this -> report;
			$record -> mangoTalker = $this -> mangoTalker;
			$record -> comment = $this -> comment;
			$d = $this -> giveAssignDatePREG();
			$record -> date = $this -> giveAssignDatePREG();
			$time = $this -> giveCallTime();
			$record -> calledDate = $this -> giveCallTime();

			//Чтобы при обновлении статуса у нас вдруг не слетела привязка.
			//А вот если привязки и не было, то попробовать привязать стоит.
			$owner = false;
			if (!$record -> id_user) {
				$owner = $this->giveOwner();
			}
			
			$record -> id_error = $this -> id_error;
			
			//var_dump($owner);
			//Если не смогли найти владельца, то не заполняем это поле.
			$record -> id_user = $owner ? $owner -> id : NULL;
			$record -> id_call_type = $this -> ClassifyId();

			if (date("n",strtotime($this -> giveAssignDatePREG())) != date("n",strtotime($this -> giveCallTime()))) {
				$record -> prev_month = 1;
			}
			$record -> type = get_class($this);
		}
		/**
		 * Возвращаем время, на которое произошла запись.
		 * @return int
		 */
		public function giveAssignDatePREG(){
			if (!$this -> assignDate) {

				$del = "(\.|,|\/| |:|\\\\)";
				$dig = "(\d{1,2})";
				$pattern = '/' . $dig . $del . $dig . $del . $dig . $del . $dig . '/';
				preg_match('/' . $dig . $del . $dig . $del . $dig . $del . $dig . '/', $this -> report, $matches);
				if ($matches[0]) {
					$year = $this->getYear();
					//Поправка на смену года.
					if ($matches[7] < date("n",strtotime($this -> giveCallTime()))) {
						$year ++;
					}
					$this -> assignDate = mktime($matches[1], $matches[3], 0, $matches[7], $matches[5], $year);
				} else {
					$this -> assignDate = $this->giveCallTime();
				}
			}
			return $this -> assignDate;
		}
	}
?>