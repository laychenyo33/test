<?php
class Model_Order_Rid{
    
    // 訂單輸出
    public function output() {
        $db = App::getHelper('db');
        if($_REQUEST['rid']){
            //附加條件
            $searchfields = new searchFields_order();
            $and_str = $searchfields->find_multiple_search_value($and_str);
            $sql = "select * from " . $db->prefix("order_items"). " as oi left join " . $db->prefix("order")." as o on o.o_id = oi.o_id "
                    . "where o.del = '0' " . ($and_str?' and '.$and_str:'') . " order by o.o_createdate desc";
            $selectrs = $db->query($sql);
            $rsnum = $db->numRows($selectrs);

            if (!empty($rsnum)) {
                require_once '../class/phpexcel/PHPExcel.php';

                $xls = new PHPExcel();

                //宣告工作表
                $xls->setActiveSheetIndex(0);

                $xls->getActiveSheet()
                        ->setTitle("訂單資料") //頁籤名稱
                        ->setCellValue('A1', 'No.')
                        ->setCellValue('B1', '訂購日期')
                        ->setCellValue('C1', '訂單編號')
                        ->setCellValue('D1', '購買人')
                        ->setCellValue('E1', 'Market Taiwan RID number')
                        ->setCellValue('F1', '產品代碼')
                        ->setCellValue('G1', '產品品名')
                        ->setCellValue('H1', '網站零售價金額')
                        ->setCellValue('I1', '購買數量')
                        ->setCellValue('J1', '總價')
                ;

                $xls->getActiveSheet()->getColumnDimension('A')->setWidth(5);
                $xls->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $xls->getActiveSheet()->getColumnDimension('C')->setWidth(20);
                $xls->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $xls->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $xls->getActiveSheet()->getColumnDimension('F')->setWidth(15);
                $xls->getActiveSheet()->getColumnDimension('G')->setWidth(30);
                $xls->getActiveSheet()->getColumnDimension('H')->setWidth(20);
                $xls->getActiveSheet()->getColumnDimension('I')->setWidth(10);
                $xls->getActiveSheet()->getColumnDimension('J')->setWidth(20);
                $xls->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                $xls->getActiveSheet()->getStyle('A1:J1')->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                        ->getStartColor()->setRGB('00B0F0');

                $xls->getActiveSheet()->getStyle('A1:J1')->getFont()->getColor()->setRGB('FFFFFF');

                while ($row = $db->fetch_array($selectrs, 1)) {
                    $i++;
                    $c_num = $i + 1;

                    $xls->getActiveSheet()
                            ->setCellValue('A' . $c_num, $i)
                            ->setCellValue('B' . $c_num, $row["o_createdate"])
                            ->setCellValue('C' . $c_num, $row["o_id"])
                            ->setCellValue('D' . $c_num, $row["o_name"])
                            ->setCellValue('E' . $c_num, $row["rid"])
                            ->setCellValue('F' . $c_num, $row["p_id"])
                            ->setCellValue('G' . $c_num, $row["p_name"])
                            ->setCellValue('H' . $c_num, $row["price"])
                            ->setCellValue('I' . $c_num, $row["amount"])
                            ->setCellValue('J' . $c_num, $row["price"] * $row["amount"]);

                    $xls->getActiveSheet()->getStyle('H' . $c_num)->getNumberFormat()->setFormatCode('"NT$"#,##0_);\("NT$"#,##0\)');
                    $xls->getActiveSheet()->getStyle('J' . $c_num)->getNumberFormat()->setFormatCode('"NT$"#,##0_);\("NT$"#,##0\)');

                    $xls->getActiveSheet()->getStyle('A' . $c_num . ':J' . $c_num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $xls->getActiveSheet()->getStyle('A' . $c_num . ':J' . $c_num)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                }

                // 總計
                $origin_cnum = $c_num;
                ++$c_num;
                //$xls->getActiveSheet()->setCellValue('I'.++$c_num, '結算');
                $xls->getActiveSheet()->setCellValue('J' . $c_num, '=SUM(J2:J' . $origin_cnum . ')');

                ++$c_num;
                $xls->getActiveSheet()->setCellValue('H' . $c_num, '右欄填入佣金%');
                $xls->getActiveSheet()->getStyle('I' . $c_num)->getNumberFormat()->setFormatCode('0%');
                $xls->getActiveSheet()->setCellValue('J' . $c_num, '=I' . $c_num . '*J' . ($origin_cnum + 1));

                ++$c_num;
                $xls->getActiveSheet()->setCellValue('H' . $c_num, '營業稅');

                $xls->getActiveSheet()->setCellValue('I' . $c_num, 0.05, PHPExcel_Cell_DataType::TYPE_NUMERIC);
                $xls->getActiveSheet()->getStyle('I' . $c_num)->getNumberFormat()->setFormatCode('0%');

                $xls->getActiveSheet()->setCellValue('J' . $c_num, '=ROUND(I' . $c_num . '*J' . ($origin_cnum + 2) . ',0)');

                ++$c_num;
                $xls->getActiveSheet()->setCellValue('H' . $c_num, '應付佣金總數');
                $xls->getActiveSheet()->setCellValue('J' . $c_num, '=ROUND(SUM(J' . ($origin_cnum + 2) . ':J' . ($origin_cnum + 3) . '),0)');

                $styleArray = array(
                    'borders' => array(
                        'top' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN
                        ),
                        'bottom' => array(
                            'style' => PHPExcel_Style_Border::BORDER_DOUBLE
                        )
                    )
                );

                $xls->getActiveSheet()->getStyle('J' . $c_num)->applyFromArray($styleArray);

                $xls->getActiveSheet()->getStyle('H' . $origin_cnum . ':J' . $c_num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $xls->getActiveSheet()->getStyle('H' . $origin_cnum . ':J' . $c_num)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $output_status = true;

                //輸出
                if ($output_status) {
                    $xls->setActiveSheetIndex(0);

                    $savefilename = mb_convert_encoding("marketamerica-" . date("Y-m-d") . ".xls", "big5", 'utf8');
                    header('Content-Type: application/vnd.ms-excel');
                    header("Content-Disposition: attachment;filename=\"" . $savefilename . "\"");
                    header('Cache-Control: max-age=0');

                    $objWriter = PHPExcel_IOFactory::createWriter($xls, 'Excel5');
                    $objWriter->save('php://output');
                }
            } else {
                if(App::configs()->new_cart_path){
                    header("location: ".App::configs()->new_cart_path.'admin.php');
                }else{
                    header("location: ".App::configs()->manage_root.'order.php');
                }            
            }
        }
        exit;
    }

}
