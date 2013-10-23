<?php
class MYDATE {
    static function getFirstWeekAndWeeks($year=""){
        if(!$year) return;
        $firstDayTS=mktime(0,0,0,1,1,date("Y"));
        if(strftime("%U",$firstDayTS)==0){
             $firstDayTS=strtotime("next sunday",$firstDayTS);
        }else{
             $firstDayTS=strtotime("last sunday",$firstDayTS);
        }
        $lastDayWeek=strftime("%U",mktime(0,0,0,12,31,date("Y")));
        return array("firstDayTS"=>$firstDayTS,"firstDay"=>date("Y-m-d",$firstDayTS),"weeks"=>$lastDayWeek);
    }
    
    static  function getPeriodByWeeks($year="",$weeks){
        if(!$year||!$weeks)return;
        $thisyear=getFirstWeekAndWeeks($year);
        $thisTS=strtotime("+".(7*($weeks-1))."days",$thisyear['firstDayTS']);
        $startDate=date("Y-m-d",$thisTS);
        $endDate=date("Y-m-d",strtotime("+6days",$thisTS));
        return array("start"=>$startDate,"end"=>$endDate,"TS"=>$thisTS);
    }    
    
    static function my_date_diff($newdate,$olddate){
        if(strtotime($newdate)<strtotime($olddate)){
            $tmpdate=$newdate;
            $newdate=$olddate;
            $olddate=$newdate;
        }
        $newdate=explode("-",$newdate);
        $olddate=explode("-",$olddate);
        if(!checkdate($newdate[1],$newdate[2],$newdate[0])||!checkdate($olddate[1],$olddate[2],$olddate[0]))die();
        $tmpY=$newdate[0]-$olddate[0];
        $tmpM=$newdate[1]-$olddate[1];
        $tmpD=$newdate[2]-$olddate[2];
        $tmpY=($tmpM<0)?$tmpY-1:$tmpY;
        $tmpM=($tmpM<0)?12+$tmpM-(($tmpD<0)?1:0):$tmpM-(($tmpD<0)?1:0);
        $fullYM['Y']=$tmpY;
        $fullYM['M']=$tmpM;
        return $fullYM;
    }
    
    static function istoday($y="",$m="",$d=""){
        if(!$y||!$m||!$d)return;
        $thestamp=mktime(0,0,0,$m,$d,$y);
        $today=mktime(0,0,0,date("m"),date("d"),date("Y"));
        if($thestamp==$today)return true;
        return false;
    }    
    
}

?>
