<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 23.10.2016
 * Time: 10:59
 */
class DataFromDb extends DataFromCsvFile {
    public static $tableName;
    public static $className;
    /**
     * @arg object user - the user to get data for.
     * @return array - an array containing all call objects.
     */
    public function giveAllCalls($user) {
        return $user -> calls;
    }
    public function generateTimeRanges($from){
        $first = getdate($from);
        $start = $first;
        $start['mday'] = $first['mday'] - ($first['mday'] % 7) + 1;
        //$from = mktime($start);
        $from = mktime(0,0,0,$start['mon'], $start['mday'],$start['year']);
        return $from;
    }
    public function giveArrayKeys($from = '', $to = ''){

        if (!$from) {
            $command = Yii::app()->db->createCommand('SELECT MIN(`create_time`) FROM {{user}}');
            $earliest = $command -> queryScalar();
            $time = strtotime($earliest);
        } else {
            $time = $from;
        }
        if (!$to) {
            $to = time();
        }
        //echo $time;
        //Определили начальный момент.
        $lower = $this -> generateTimeRanges($time,$to);
        //echo "<br/>".$lower;
        $add = 604800;
        $rez = array();
        while ($lower < $to) {
            $rez[] = $lower;
            $lower += $add;
        }
        return $rez;
    }

    /**
     * @param User $user
     * @param integer $from
     * @param integer $to
     * @return array
     */
    public function giveWeekedStats($user, $from = null, $to = null){
        if (!$from) {
            $command = Yii::app()->db->createCommand('SELECT MIN(`create_time`) FROM {{user}}');
            $earliest = $command -> queryScalar();
            $time = strtotime($earliest);
        } else {
            $time = $from;
        }
        if (!$to) {
            $to = time();
        }
        //Определили начальный момент.
        $lower = $this -> generateTimeRanges($time);
        $add = 604800;
        //$calls = $this -> giveAllCalls($user);
        $rez = array();
        while ($lower < $to) {
            $higher = $lower + $add;
            if (strtotime($user -> create_time) > $higher) {
                $rez[] = -1;
            } else {
                $rez[] = $this -> countCallsInRange($lower, $higher, $user);
            }
            $lower = $higher;
        }
        return $rez;
    }
    /**
     * @arg object user - the user to get data for.
     * @return array - an array that is keyed by month numbers (1 - 12) and contains only specified user's call objects corresponding to the keying month.
     */
    public function giveMonthedCalls($user){
        $create_date_arr = getdate(strtotime($user -> create_time));

        $cur_date_arr = getdate();
        $endTime = time();
        //print_r($create_date_arr);
        //$calls = Setting::getDataObj() -> giveAllCalls($user);
        //echo count($calls);
        //Начинаем с того месяца, когда был создан пользователь.
        $month = $create_date_arr['mon'];
        $year_offset = ($create_date_arr['year'] - 2015) * 12;
        $time = mktime(0,0,0,$month,1,$create_date_arr['year']);//strtotime($user -> create_time);
        //Начинаем отсчет месяцев с месяцев 2015 года, тк система была написана в это время.
        $month += $year_offset;
        $rez = array();
        while($time < $endTime){
            $end = $this -> DateAdd('m',1,$time);
            $rez[$month] = $this -> giveCallsInRange($time, $end, $user);
            $time = $end;
            $month ++;
            if ($month > 100) {break;}
        }
        //ksort($rez);
        return $rez;
    }
    /**
     * @arg array calls - the array with calls to be classifyed and counted
     * @return array - an array array(<className> => <number of calls classified to be className>)
     */
    public function countArray($calls){
        $types = array_filter(CHtml::giveAttributeArray(CallType::model() -> findAll(),'string'));
        $rez = array();
        foreach($types as $type) {
            $rez[$type] = 0;
        }
        //print_r($rez);
        //echo "<br/>";
        foreach($calls as $call) {
            $rez[$call -> Classify()] ++;
        }
        $rez['common'] = count($calls);
        return $rez;
    }
    /**
     * @arg integer from - the lower boundary of a call time in second from 1 Jan 1970
     * @arg integer to  - the higher boundary of a call time in second from 1 Jan 1970
     * @arg object user - the user whose calls are to be returned
     * @return array - an array of call objects that correspond to the specified user and lie between the lower and higher boundary
     */
    public function giveCallsInRange($from, $to, $user){
        $criteria = Setting::getCallModel() -> giveCriteriaForTimePeriod($from, $to);
        if ($user -> id_type == UserType::model() -> getNumber('doctor')) {
            $criteria -> compare('id_user', $user -> id);
        } else {
            $user -> getChildren();
            //Временно заменяем поиск только по детям поиском по детям + себе
            //$criteria -> addInCondition('id_user', CHtml::giveAttributeArray($user -> children, 'id'));
            $id_arr = CHtml::giveAttributeArray($user -> children, 'id');
            $id_arr[] = $user -> id;
            $criteria -> addInCondition('id_user', array_merge(CHtml::giveAttributeArray($user -> children, 'id'),array($user -> id)));
        }
        return Setting::getCallModel() -> findAll($criteria);
    }
    /**
     * Старая функция. Работает с объектами из csv файла
     * @param User $user
     * @param integer $from
     * @param integer $to
     * @return array - an array array(<className> => <number of calls classified to be className>)
     */
    public function countCallsInRange($from, $to, User $user){
        //Если речь идет не о медпредах, то алгоритм обычный.
        //if ($user -> id_type != 2) {
        $conn = MysqlConnect::getConnection();
        $BaseSql = "SELECT `id_call_type`,COUNT(`id`) from `".static::$tableName."` WHERE `date` > FROM_UNIXTIME('{$from}') AND `date` < FROM_UNIXTIME('{$to}')";
        $append = true;
        $type = $user -> id_type;
        if ($user -> id_type == 2) {
            if ($children = $user -> getChildrenIdString()) {
                $Sql = $BaseSql . " AND `id_user` IN ({$user -> getChildrenIdString()})";
                $append = false;
            }
        }
        if ($append) {
            $Sql = $BaseSql . " AND `id_user` = '{$user -> id}'";
        }
        $Sql .= " GROUP BY `id_call_type`";
        $q = mysqli_query($conn, $Sql);
        //$username = $user -> username;
        //$children = $user -> children;
        $err = mysqli_error($conn);
        $translate = array(
            1 => 'verifyed',
            2 => 'missed',
            3 => 'cancelled',
            4 => 'side',
            5 => 'declined',
            6 => 'assigned'
        );
        $rez = array();
        //$rez = mysqli_fetch_array ($q);
        while ($temp = $q->fetch_array(MYSQLI_NUM)) {
            $rez [$translate[$temp[0]]] = $temp[1];
        }
        $total = array_sum($rez);
        $rez['common'] = $total;
        return $rez;
    }//*/



    public function giveMonthName($n){
        $arr = $this -> giveMonthNamesArray();
        return $arr[(($n - 1 )% 12) +1];
    }
    public function giveMonthNamesArray() {
        return array(
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь'
        );
    }
    public function TransformGivenGetArrayToTimeRange($get, $user){
        $from = (int)$get['from'] ? (int)$get['from'] : strtotime($user -> create_time);
        //$to = (int)$get['to'] ? (int)$get['to'] : time();
        $to = (int)$get['to'];
        return array('from' => $from, 'to' => $to);
    }
    //helper
    public function DateAdd($interval, $number, $date) {
        $date_time_array = getdate($date);
        $hours = $date_time_array['hours'];
        $minutes = $date_time_array['minutes'];
        $seconds = $date_time_array['seconds'];
        $month = $date_time_array['mon'];
        $day = $date_time_array['mday'];
        $year = $date_time_array['year'];

        switch ($interval) {

            case 'yyyy':
                $year+=$number;
                break;
            case 'q':
                $year+=($number*3);
                break;
            case 'm':
                $month+=$number;
                break;
            case 'w':
                $day+=$number;
                break;
            case 'ww':
                $day+=($number*7);
                break;
        }
        $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
        return $timestamp;
    }
    public static function model(){
        return new self;
    }
}