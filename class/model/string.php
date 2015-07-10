<?php

class Model_String {

    /**
     * 將傳入的阿拉伯數字轉為國字數字
     * @param int $numInp 阿拉數字
     * @return string
     */
    static function num2Zh($numInp) {
        $numInp = trim($numInp);
        $numInp = str_replace(' ', '', $numInp);

        $numInp = number_format((float) $numInp, 0, '', ''); //-- 轉成數字
        if ($numInp == 0) {
            return '零';
        }

        $zh = array('零', '壹', '貳', '參', '肆', '伍', '陸', '柒', '捌', '玖');
        $units = array('', '仟', '佰', '拾');
        $great_units = array('', '萬', '億', '兆');

        $num_len = strlen($numInp);
        $num_ary = str_split($numInp); //-- 轉成字元陣列

        $gu = (int) ($num_len / 4); //-- 最大單位
        $mod = $num_len % 4;  //-- 超過大單位的 數字長度
        $gu = ( $mod == 0 ) ? $gu - 1 : $gu;

        $numOut = ''; //-- 最終輸出字串
        $count = 1;
        $lastNum = '';
        foreach ($num_ary as $num) {
            $lastNum = $num;
            if ($lastNum != '0') {
                $numOut .= $zh[$num];
            }

            $tmp = ($count - $mod) % 4;
            $tmp = ( $tmp < 0 ) ? $tmp + 4 : $tmp;

            if ($count == $mod) {
                $numOut = self::_trimLastZero($numOut);

                //-- 加上大單位
                $numOut .= $great_units[$gu];
                $gu--;
            } else if ($tmp == 0) {
                //-- 最後字元為 '零' 時，去掉。
                if ($gu == 1) {
                    $numOut = self::_trimLastZero($numOut);
                }

                //-- 加上大單位
                $numOut .= $great_units[$gu];
                $gu--;
            } else if ($tmp > 0 && $num !== '0') {
                //-- 加上 仟佰拾 單位
                $numOut .= $units[$tmp];
            } else if ($tmp > 0 && $num === '0') {
                $numOut .= '零';
            }

            $count++;
        }

        //-- 去掉多餘的 '零'，只顯示一個
        $numOut = preg_replace('/(零)+/', '零', $numOut);
        $numOut = self::_trimLastZero($numOut);
        return $numOut;
    }

    //-- 去除尾數的 "零"
    protected static function _trimLastZero($numOut) {

        $final_str = mb_strlen($numOut, 'UTF-8') - 1;
        if (mb_substr($numOut, $final_str, 1, 'UTF-8') == '零') {
            $numOut = mb_substr($numOut, 0, $final_str, 'UTF-8');
        }
        return $numOut;
    }

}
