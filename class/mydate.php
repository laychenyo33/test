<?php
class MYDATE {
    static function getFirstWeekAndWeeks($year=""){
        if(!$year) return;
        $firstDayTS=mktime(0,0,0,1,1,$year);
        if(date("N",$firstDayTS)==7){
             $firstDayTS=strtotime("next monday",$firstDayTS);
        }else{
             $firstDayTS=strtotime("last monday",$firstDayTS);
        }
        $lastDayTS = mktime(0,0,0,12,31,$year);
        if(date("N",$lastDayTS)!=7){
             $lastDayTS =strtotime("last sunday",$firstDayTS);
        }
        $lastDayWeek = date("W",$lastDayTS);
        return array("firstDayTS"=>$firstDayTS,"firstDay"=>date("Y-m-d",$firstDayTS),"weeks"=>$lastDayWeek);
}
    
    static  function getPeriodByWeeks($year="",$weeks=null){
        if(!$year)return;
        $weeks = $weeks?$weeks:date("W");
        $thisyear=self::getFirstWeekAndWeeks($year);
        $thisTS=strtotime("+".(7*($weeks-1))."days",$thisyear['firstDayTS']);
        $startDate=date("Y-m-d",$thisTS);
        $endDate=date("Y-m-d",strtotime("+6days",$thisTS));
        return array("start"=>$startDate,"end"=>$endDate,"TS"=>$thisTS);
    }    
    
    static function my_date_diff($newdate,$olddate){
        $fullYM['newdate'] = $newdate;
        $fullYM['olddate'] = $olddate;
        $newTS = strtotime($newdate);
        $oldTS = strtotime($olddate);
        if($newTS < $oldTS){
            $tmpTS = $newTS;
            $newTS = $oldTS;
            $oldTS = $tmpTS;
            $minus = true;
        }
        $newdate=explode("-",date("Y-m-d",$newTS));
        $olddate=explode("-",date("Y-m-d",$oldTS));
        $tmpY=$newdate[0]-$olddate[0];
        $tmpM=$newdate[1]-$olddate[1];
        $tmpD=$newdate[2]-$olddate[2];
        if($tmpD<0){
            $tmpD = date("t",$oldTS)-$olddate[2]+$newdate[2];
            $tmpM -=1;
            $newdate[1] -=1;
        }
        if($tmpM<0){
            $tmpM = 12-$olddate[1]+$newdate[1];
            $tmpY -= 1;
        }
        $fullYM['Y']=$tmpY;
        $fullYM['M']=$tmpM;
        $fullYM['D']=$tmpD;
        $fullYM['sign']=($minus)?"-":"";
        return $fullYM;
}
    
    static function istoday($y="",$m="",$d=""){
        if(!$y||!$m||!$d)return;
        $thestamp=mktime(0,0,0,$m,$d,$y);
        $today=mktime(0,0,0,date("m"),date("d"),date("Y"));
        if($thestamp==$today)return true;
        return false;
    }    
    static function getWeekOfYear($date){
        $ts = strtotime($date);
        if(date("N",$ts)!=7){
            $res['year'] = date("Y",strtotime("next sunday",$ts));
        }else{
            $res['year'] = date("Y",$ts);
        }
        $res['week'] = date("W",$ts);
        return $res;
    }
    static function getWeekDiff($date1,$date2,$abs=false){
        $week1 = self::getWeekOfYear($date1);
        $week2 = self::getWeekOfYear($date2);
        if($week1['year']==$week2['year']){ //同一年的比對
            $weeksDiff = $week1['week']-$week2['week'];
        }else{
            if($week1['year']<$week2['year']){ //後面的日期較大
                $tmp = $week1;
                $week1 = $week2;
                $week2 = $tmp; 
                $toMinus = true;
            }
            //計算$date2年度剩餘的週數
            $weeksInfoOfLater = self::getFirstWeekAndWeeks($week2['year']);
            $weeksDiff = $weeksInfoOfLater['weeks'] - $week2['week'];
            //計算新年度的週數
            $yearsDiff = $week1['year']-$week2['year'];
            $i=1;
            while( ($week2['year']+$i) <= $week1['year'] ){
                if($yearsDiff==$i){ //取$date1年度所屬週數，非整年度週數
                    $weeksDiff += $week1['week'];
                }else{ //與$date1比對超過一年時，取整年度週數
                    $tmpWeesInfo = self::getFirstWeekAndWeeks($week2['year']+$i);
                    $weeksDiff += $tmpWeesInfo['weeks'];
                }
                $i++;
            }
            //轉成負數
            if($toMinus){
                $weeksDiff *=-1;
            }
        }
        if($abs){
            return abs($weeksDiff);
        }else{
            return $weeksDiff;
        }
    }
}

?>
