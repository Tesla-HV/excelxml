<?php

$mysqli = new \mysqli("localhost", "root", "", "dbname");
$mysqli->set_charset("utf8");

$query = "SELECT * FROM example";

$res = $mysqli->query($query);

if ($res === false) {
	header("Content-Type: text/plain; charset=utf-8");
	echo "MySQL error (".$mysqli->errno."): ".$mysqli->error."\n";
} else if ($res === true) {
	header("Content-Type: text/plain; charset=utf-8");
	echo "Query executed successfully\n";
} else {
	exportResultAsXml($res);
	$res->free();
}




function exportResultAsXml(\mysqli_result $res) {
	$fields = $res->fetch_fields();
	header("Content-Type: application/xml; charset=utf-8");
	$wr = new \XMLWriter();
	$wr->openUri("php://output");
	$wr->startDocument('1.0', 'UTF-8'); 
	$wr->startElementNs("ss", "Workbook", "urn:schemas-microsoft-com:office:spreadsheet");
		$wr->startElementNs("ss", "Styles", null);
			$wr->startElementNs("ss", "Style", null);
				$wr->writeAttributeNs("ss", "ID", null, "1");
				$wr->startElementNs("ss", "Font", null);
					$wr->writeAttributeNs("ss", "Bold", null, "1");
				$wr->endElement();
			$wr->endElement();
		$wr->endElement();
		$wr->startElementNs("ss", "Worksheet", null);
			$wr->writeAttributeNs("ss", "Name", null, "SQL Resultset");
			$wr->startElementNs("ss", "Table", null);
				$wr->startElementNs("ss", "Row", null);
					$wr->writeAttributeNs("ss", "StyleID", null, "1");
					foreach ($fields as $field) {
						$wr->startElementNs("ss", "Cell", null);
							$wr->startElementNs("ss", "Data", null);
								$wr->writeAttributeNs("ss", "Type", null, "String");
								$wr->text($field->name);
							$wr->endElement();
						$wr->endElement();
					}
				$wr->endElement();
				while (($row = $res->fetch_row()) !== null) {
					$wr->startElementNs("ss", "Row", null);
					foreach ($row as $i => $cell) {
						$wr->startElementNs("ss", "Cell", null);
							if ($cell === null) {
								$wr->startElementNs("ss", "Data", null);
									$wr->writeAttributeNs("ss", "Type", null, "Error");
									$wr->text("#NULL!");
								$wr->endElement();
							} else {
								$wr->startElementNs("ss", "Data", null);
									switch ($fields[$i]->type) {
										// Integer types
										case MYSQLI_TYPE_BIT:
										case MYSQLI_TYPE_TINY:
										case MYSQLI_TYPE_SHORT:
										case MYSQLI_TYPE_LONG:
										case MYSQLI_TYPE_LONGLONG:
										case MYSQLI_TYPE_INT24:
										case MYSQLI_TYPE_YEAR:
										case MYSQLI_TYPE_ENUM:
										// Floating-point types
										case MYSQLI_TYPE_DECIMAL:
										case MYSQLI_TYPE_NEWDECIMAL:
										case MYSQLI_TYPE_FLOAT:
										case MYSQLI_TYPE_DOUBLE:
											$wr->writeAttributeNs("ss", "Type", null, "Number");
											break;
										default:
											$wr->writeAttributeNs("ss", "Type", null, "String");
											break;
									}
									$wr->text($cell);
								$wr->endElement();
							}
						$wr->endElement();
					}
					$wr->endElement();
				}
			$wr->endElement();
		$wr->endElement();
	$wr->endElement();
	$wr->endDocument(); 
}

