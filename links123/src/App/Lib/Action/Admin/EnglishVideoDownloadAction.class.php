<?php

/**
 * 临时！英语角视频下载类，用于本地批量下载视频并修改excel文件
 * 注意：请不要上传到服务器
 * @author Adam $date2.13-07-18$
 */
class EnglishVideoDownloadAction extends CommonAction {

    const USER_AGENT = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:19.0) Gecko/20100101 Firefox/19.0 Chromium/18.0.1025.168 Chrome/18.0.1025.168 Safari/535.19';

    private $_supportWebsite = array(
        'iqiyi.com' => '_iqiyi',
        'cntv.cn' => '_cntv',
        'qq.com' => '_qq',
        'youku.com' => '_youku',
        'tudou.com' => '_tudou',
        'ku6.com' => '_ku6',
        'sina.com.cn' => '_sina',
        '56.com' => '_56',
        'letv.com' => '_letv',
        'sohu.com' => '_sohu',
        'ted.com' => '_ted',
        '163.com' => '_163',
        'umiwi.com' => '_umiwi',
        'about.com' => '_about',
        'videojug.com' => '_videojug',
        'kekenet.com' => '_kekenet',
        'youban.com' => '_youban',
        'hujiang.com' => '_hujiang',
        'literacycenter.net' => '_literacycenter',
        'peepandthebigwideworld.com' => '_peepandthebigwideworld'
    );

    //excel导入
    public function excel_insert() {
        if ($this->isPost()) {
            import("@.ORG.UploadFile");
            $upload = new UploadFile();
            //设置上传文件大小
            //$upload->maxSize = 3292200;
            //设置上传文件类型
            $upload->allowExts = explode(',', 'xlsx,xls');
            //设置附件上传目录
            $path = realpath('./Public/Uploads/uploads.txt');
            $upload->savePath = str_replace('uploads.txt', 'Excels', $path) . '/';
            //设置上传文件规则
            $upload->saveRule = uniqid;
            if (!$upload->upload()) {
                //捕获上传异常
                $this->ajaxReturn("", $upload->getErrorMsg(), false);
            } else {
                //取得成功上传的文件信息
                $uploadList = $upload->getUploadFileInfo();
            }

            //引入类
            error_reporting(E_ALL);
            date_default_timezone_set('Asia/Shanghai');

            vendor('PHPExcel.Classes.PHPExcel.IOFactory');
            @header('Content-type: text/html;charset=UTF-8');
            //读取excel;
            if ($uploadList[0]['extension'] == "xls") {
                $objReader = PHPExcel_IOFactory::createReader('Excel5');
            } else {
                $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            }
            $path = realpath('./Public/Uploads/uploads.txt');
            $dest = str_replace('uploads.txt', 'Excels/' . $uploadList[0]['savename'], $path);

            $objPHPExcel = $objReader->load($dest);
            $excelData = array(); //表格中的数据
            $mediaTextUrlArray = array(); //视频的网址数组
            //循环读取所有表,表迭代器
            foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
                foreach ($worksheet->getRowIterator() as $row) {
                    //行迭代器
                    $cellIterator = $row->getCellIterator();
                    $data = array(); //暂时保存数据的数组
                    $cellIterator->setIterateOnlyExistingCells(false); //单元格为空也迭代
                    foreach ($cellIterator as $cell) {
                        //单元格迭代器
                        if (!is_null($cell)) {
                            if ($cell->getColumn() == "A") {
                                $data['A'] = ftrim($cell->getCalculatedValue()); //名称
                            } else if ($cell->getColumn() == "B") {
                                $data['B'] = $cell->getCalculatedValue(); //语种，英音，美音
                            } else if ($cell->getColumn() == "C") {
                                $data['C'] = $cell->getCalculatedValue(); //类型，视频，音频
                            } else if ($cell->getColumn() == "D") {
                                $data['D'] = $cell->getCalculatedValue(); //目标，听力，说力
                            } else if ($cell->getColumn() == "E") {
                                $data['E'] = ftrim($cell->getCalculatedValue()); //等级名称
                            } else if ($cell->getColumn() == "F") {
                                $data['F'] = ftrim($cell->getCalculatedValue()); //科目名称
                            } else if ($cell->getColumn() == "G") {
                                $data['G'] = ftrim($cell->getCalculatedValue()); //媒体内容地址
                            } else if ($cell->getColumn() == "H") {
                                $data['H'] = ftrim($cell->getCalculatedValue()); //题目内容
                            } else if ($cell->getColumn() == "I") {
                                $data['I'] = ftrim($cell->getCalculatedValue()); //题目答案
                            } else if ($cell->getColumn() == "J") {
                                $data['J'] = ftrim($cell->getCalculatedValue()); //题目选项一
                            } else if ($cell->getColumn() == "K") {
                                $data['K'] = ftrim($cell->getCalculatedValue()); //题目选项二
                            } else if ($cell->getColumn() == "L") {
                                $data['L'] = ftrim($cell->getCalculatedValue()); //题目选项三
                            } else if ($cell->getColumn() == "M") {
                                $data['M'] = ftrim($cell->getCalculatedValue()); //题目选项四
                            } else if ($cell->getColumn() == "N") {
                                $data['N'] = ftrim($cell->getCalculatedValue()); //媒体内容
                            }
                        }
                    }
                    if (!empty($data['A']) && $data['A'] != "试题名称") {
                        $data['O'] = "12345.flv";
                    }
                    array_push($mediaTextUrlArray, empty($data['G']) ? "" : $data['G']);
                    array_push($excelData, $data);
                }
            }

            import("@.ORG.VideoBatchDownload");
            $videoBatchDownload = new VideoBatchDownload();
            $mediaTextUrlArray = array('http://www.peepandthebigwideworld.com/activities/anywhere-activities/whathappens/', 'http://www.peepandthebigwideworld.com/activities/anywhere-activities/whathappens/');
            $fileInfos = $videoBatchDownload->download($mediaTextUrlArray);
            dump($fileInfos);
            exit;

            //指定操作的excel工作薄
            $objPHPExcel->setActiveSheetIndex(0);
            //依次存入处理后的数据
            foreach ($excelData as $k => $v) {
                foreach ($v as $key => $value) {
                    $objPHPExcel->getActiveSheet()->setCellValue($key . ($k + 1), $value);
                }
            }
            //保存excel;
            if ($uploadList[0]['extension'] == "xls") {
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
            } else {
                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            }
            $objWriter->save($upload->savePath . "dealed_" . time() . '.' . $uploadList[0]['extension']);
        }
    }

}

?>
