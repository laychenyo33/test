<?php
class XLSExportor{
    protected $titles;
    protected $dataFields;
    protected $file_name = "export";
    protected $sheet_title = "maindata";
    protected $autoSize = true;
    protected $font_size = 12;
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
                //設標題值及欄位寬度
                if(is_string($this->titles[$i])){
                    $objPHPExcel->getActiveSheet()->setCellValue($this->column.'1',$this->titles[$i]);
                    if($this->autoSize){
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->column)->setAutoSize(true);                    
                    }
                }elseif(is_array($this->titles[$i])){
                    $objPHPExcel->getActiveSheet()->setCellValue($this->column.'1',$this->titles[$i]['data']);
                    if($this->titles[$i]['width']){
                        $objPHPExcel->getActiveSheet()->getColumnDimension($this->column)->setWidth($this->titles[$i]['width']); 
                    }
                }
                $objPHPExcel->getActiveSheet()->getStyle($this->column.'1')->getFont()->setSize($this->font_size);
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
                    if(is_array($row[$i])){
                        switch($row[$i]['type']){
                            case "image":
                                if(file_exists($row[$i]['data'])){
                                    $objDrawing = new PHPExcel_Worksheet_Drawing();
                                    $objDrawing->setName('avatar');
                                    $objDrawing->setDescription('avatar');
                                    $objDrawing->setPath($row[$i]['data']);
                                    $objDrawing->setWidth(70);
                                    $objDrawing->setCoordinates($this->column.$row_no)->setOffsetX(10)->setOffsetY(10);
                                    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
                                    $objPHPExcel->getActiveSheet()->getColumnDimension($this->column)->setWidth(13);
                                    $objPHPExcel->getActiveSheet()->getRowDimension($row_no)->setRowHeight($objDrawing->getHeight()-10);                                    
                                }
                                break;
                            default:
                                //$objPHPExcel->getActiveSheet()->setCellValue($this->column.$row_no,$row[$i]);
                                $objPHPExcel->getActiveSheet()->getCell($this->column.$row_no)->setValueExplicit($row[$i]['data'], $row[$i]['type']);
                                break;
                        }
                        if($row[$i]['wrap']){
                            $objPHPExcel->getActiveSheet()->getStyle($this->column.$row_no)->applyFromArray(
                                array(
                                    'alignment' => array(
                                        'wrap'   => true,
                                    ),
                                )
                            );
                        }
                    }else{
                        $objPHPExcel->getActiveSheet()->setCellValue($this->column.$row_no,$row[$i]);
                    }
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
                            ),
                            'alignment' => array(
                                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                                'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
                            ),                            
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
    //切換excel日期數據為可讀
    function getdatefromjd($val,$format="Y-m-d"){
        $jd = GregorianToJD(1, 1, 1970); 
        $gregorian = JDToGregorian($jd+intval($val)-25569);               
        return date($format,strtotime($gregorian));
    }    
    function setAutoSize($v=true){
        $this->autoSize = $v;
    }
    function setFontSize($s=10){
        $this->font_size = $s;
    }
}
//ob_end_flush();
?>