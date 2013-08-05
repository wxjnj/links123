<?php
/**
 * PHPExcel
 *
 * Copyright (C) 2006 - 2011 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel
 * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version    1.7.6, 2011-02-27
 */

/** Error reporting */
error_reporting(E_ALL);

date_default_timezone_set('Europe/London');

/** PHPExcel_IOFactory */
require_once 'Classes/PHPExcel/IOFactory.php';

@header('Content-type: text/html;charset=UTF-8');

/*
$dest = trim($_GET['filepath']);

$reader = PHPExcel_IOFactory::createReader('Excel2007');
$PHPExcel = PHPExcel_IOFactory::load($dest);

$sheet = $PHPExcel->getSheet(0);//选择工作簿
$highestRow =$sheet->getHighestRow();//得到列表的行数
$highestColumn = $sheet->getHighestColumn();//得到列表列数
*/

echo date('H:i:s') . " Load from Excel5 template<br />";
$objReader = PHPExcel_IOFactory::createReader('Excel2007');

//$dest = "Tests/templates/matchtest.xlsx";
$dest = "demo.xlsx";

$objPHPExcel = $objReader->load($dest);

echo date('H:i:s') . " Iterate worksheets<br />";
foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
	echo '- ' . $worksheet->getTitle() . "<br />";
	
	
	
	// 连接，选择数据库
	$now = time();
	//$link = mysql_connect('localhost', 'huawr', '851220')
	//or die('Could not connect: ' . mysql_error());
	//echo 'Connected successfully<br />';
	//mysql_select_db('links') or die('Could not select database');
	
	foreach ($worksheet->getRowIterator() as $row) {
		echo '    - Row number: ' . $row->getRowIndex() . "<br />";
	
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell) {
			if (!is_null($cell)) {
				echo '        - Cell: ' . $cell->getCoordinate() . ' - ' . $cell->getCalculatedValue() . "<br />";
				$url = $cell->getCalculatedValue();
				$tag = substr($url,0,strpos($url,'.'));
				//$query = "insert into lnk_direct_links(tag,url,update_time) values('".$tag."','".$url."',".$now.")";
				//$result = mysql_query($query);// or die('Query failed: ' . mysql_error());
			}
		}
	}

	// 释放结果集
	//mysql_free_result($result);
	
	// 关闭连接
	//mysql_close($link);
}


// Echo memory peak usage
echo date('H:i:s') . " Peak memory usage: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB<br />";

// Echo done
echo date('H:i:s') . " Done writing file.<br />";
