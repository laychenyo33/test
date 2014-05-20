<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AllpayInfo
 *
 * @author Administrator
 */
class AllpayInfo {
    //put your code here
    static $map = array(
        'RtnCode'  => '交易狀態',
        'RtnMsg'   => '交易訊息',
        'TradeNo'  => '歐付寶交易編號',
        'TradeAmt' => '總金額',
        'PaymentDate' => '付款時間',
        'PaymentType' => '付款類型',
        'PaymentTypeChargeFee' => '手續費',
        'TradeDate'  => '訂單成立時間',
        'BankCode'   => '銀行代碼',
        'vAccount'   => 'ATM 虛擬帳號',
        'PaymentNo'  => 'CVS 繳費代碼',
        'Barcode1'   => 'CVS 條碼第一段號',
        'Barcode2'   => 'CVS 條碼第二段號',
        'Barcode3'   => 'CVS 條碼第三段號',
        'ExpireDate' => '繳費期限',
    );
}
