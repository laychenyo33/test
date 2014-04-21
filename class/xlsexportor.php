<?php
class XLSExportor{
    protected $titles;
    protected $dataFields;
    protected $file_name = "export";
    protected $sheet_title = "maindata";
    function __construct(){
        if(!class_exists("PHPExcel")){
            throw new Exception("Required Class PHPExcel doesn't exists!");
        }
    }
    //設定標題列
    function setTitle($titles){
        $this->titles = $titles;
    }
    //設定輸出資料
    function setData($data){
        $this->dataFields = $data;
    }
    //設定輸出檔名
    function setFilename($filename){
        $this->file_name = $filename;
    }
    function export(){
        $objPHPExcel = new PHPExcel(); 
        $objPHPExcel->setActiveSheetIndex(0);
        $this->column = "A";
        $row_no=1;
        //處理標題列
        if(is_array($this->titles) && count($this->titles)){
            for($i = 0; $i< count($this->titles); $i++){
                $objPHPExcel->getActiveSheet()->setCellValue($this->column.'1',$this->titles[$i]);
                $objPHPExcel->getActiveSheet()->getStyle($this->column.'1')->getFont()->setSize($this->font_size);
                $objPHPExcel->getActiveSheet()->getColumnDimension($this->column)->setWidth($this->set_width); 
                if($this->column == "A"){
                        $objPHPExcel->getActiveSheet()->getStyle($this->column.'1')->applyFromArray(
                            array(
                                'borders' => array( 
                                    'left'     => array( 
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                )
                            ) 
                        );
                }
                $objPHPExcel->getActiveSheet()->getStyle($this->column.'1')->applyFromArray(
                    array(
                        'borders' => array( 
                                'right'     => array( 
                                         'style' => PHPExcel_Style_Border::BORDER_THIN
                                 ),
                                'bottom'     => array( 
                                         'style' => PHPExcel_Style_Border::BORDER_THIN
                                 ),
                                'top'     => array( 
                                         'style' => PHPExcel_Style_Border::BORDER_THIN
                                 ),			
                        ),
                        'fill' => array(
                                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => 'cccccc')
                        ),
                        'alignment' => array(
                            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        )
                    ) 
                );
                $this->column++; 
            }
            $row_no++;
        }
        if(is_array($this->dataFields) && count($this->dataFields)){
            foreach ( $this->dataFields as $k => $row ) {
                $this->column = "A";
                for($i = 0; $i< count($row); $i++){
                        $objPHPExcel->getActiveSheet()->setCellValue($this->column.$row_no,$row[$i]);
                        $objPHPExcel->getActiveSheet()->getStyle($this->column.$row_no)->getFont()->setSize($this->font_size);
                        if($this->column == "A"){
                            $objPHPExcel->getActiveSheet()->getStyle($this->column.$row_no)->applyFromArray(
                                array(
                                    'borders' => array( 
                                        'left'     => array( 
                                             'style' => PHPExcel_Style_Border::BORDER_THIN
                                         )
                                    )
                                ) 
                            );
                        }
                        $objPHPExcel->getActiveSheet()->getStyle($this->column.$row_no)->applyFromArray(
                            array(
                                'borders' => array( 
                                    'right'     => array( 
                                         'style' => PHPExcel_Style_Border::BORDER_THIN
                                     ),
                                    'bottom'     => array( 
                                         'style' => PHPExcel_Style_Border::BORDER_THIN
                                     ),
                                )
                            ) 
                        );
                        $this->column++; 
                }
                $row_no++;

            }

            // Rename sheet 
            $objPHPExcel->getActiveSheet()->setTitle($this->sheet_title);

            //設定的欄位寬度(自動) 
            //$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(true);

            // Set active sheet index to the first sheet, so Excel opens this as the first sheet 
            $objPHPExcel->setActiveSheetIndex(0);

            // Export to Excel2007 (.xlsx) 匯出成2007

            //$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 
            //$objWriter->save('test.xlsx');

            // Export to Excel5 (.xls) 匯出成2003

            //$savefilename=iconv("utf8","big5",$this->file_name);
            $savefilename=  mb_convert_encoding($this->file_name,"big-5","utf-8");;
            header('Content-Type: application/vnd.ms-excel');
            header("Content-Disposition: attachment;filename=\"".$savefilename.".xls\"");
            header('Cache-Control: max-age=0');

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            $objWriter->save('php://output');
            exit;
        }
    }
}
//ob_end_flush();
?>