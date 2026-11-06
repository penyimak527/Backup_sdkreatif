<?php defined('BASEPATH') or exit('No direct script access allowed');

class Student_xlsx
{
    public function read($path)
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi ZIP PHP belum tersedia.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('File Excel tidak dapat dibuka.');
        }

        $shared = array();
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $doc = $this->xml($sharedXml);
            $xpath = $this->xpath($doc);
            foreach ($xpath->query('//m:si') as $item) {
                $value = '';
                foreach ($xpath->query('.//m:t', $item) as $text) {
                    $value .= $text->nodeValue;
                }
                $shared[] = $value;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new RuntimeException('Worksheet pertama tidak ditemukan.');
        }
        $doc = $this->xml($sheetXml);
        $xpath = $this->xpath($doc);
        $result = array();
        foreach ($xpath->query('//m:sheetData/m:row') as $row) {
            $values = array_fill(0, 9, '');
            foreach ($xpath->query('./m:c', $row) as $cell) {
                $reference = $cell->getAttribute('r');
                $column = $this->columnIndex($reference);
                if ($column < 0 || $column > 8) {
                    continue;
                }
                $type = $cell->getAttribute('t');
                if ($type === 'inlineStr') {
                    $value = '';
                    foreach ($xpath->query('.//m:t', $cell) as $text) {
                        $value .= $text->nodeValue;
                    }
                } else {
                    $node = $xpath->query('./m:v', $cell)->item(0);
                    $raw = $node ? $node->nodeValue : '';
                    $value = $type === 's' && isset($shared[(int) $raw]) ? $shared[(int) $raw] : $raw;
                }
                $values[$column] = trim((string) $value);
            }
            if (count(array_filter($values, static function ($value) { return $value !== ''; })) > 0) {
                $result[] = $values;
            }
        }
        $zip->close();
        return $result;
    }

    public function create($headers, $rows, $sheetName = 'Data Siswa')
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi ZIP PHP belum tersedia.');
        }
        $path = tempnam(sys_get_temp_dir(), 'siswa-xlsx-');
        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('File Excel sementara tidak dapat dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/><Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/><Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.$this->escape($sheetName).'" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF24548A"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf></cellXfs></styleSheet>');

        $allRows = array_merge(array($headers), $rows);
        $sheetRows = '';
        foreach ($allRows as $rowIndex => $row) {
            $number = $rowIndex + 1;
            $cells = '';
            foreach (array_values($row) as $column => $value) {
                $reference = $this->columnName($column).$number;
                $style = $number === 1 ? ' s="1"' : '';
                $cells .= '<c r="'.$reference.'" t="inlineStr"'.$style.'><is><t xml:space="preserve">'.$this->escape((string) $value).'</t></is></c>';
            }
            $height = $number === 1 ? ' ht="32" customHeight="1"' : '';
            $sheetRows .= '<row r="'.$number.'"'.$height.'>'.$cells.'</row>';
        }
        $lastRow = max(1, count($allRows));
        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols><col min="1" max="1" width="14" customWidth="1"/><col min="2" max="2" width="32" customWidth="1"/><col min="3" max="3" width="22" customWidth="1"/><col min="4" max="4" width="20" customWidth="1"/><col min="5" max="5" width="18" customWidth="1"/><col min="6" max="6" width="18" customWidth="1"/><col min="7" max="8" width="30" customWidth="1"/><col min="9" max="9" width="30" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:I'.$lastRow.'"/></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $now = gmdate('Y-m-d\TH:i:s\Z');
        $zip->addFromString('docProps/core.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"><dc:creator>SD Kreatif</dc:creator><dcterms:created xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:created></cp:coreProperties>');
        $zip->addFromString('docProps/app.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>SD Kreatif</Application></Properties>');
        $zip->close();
        return $path;
    }

    private function xml($contents)
    {
        $previous = libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        if (!$doc->loadXML($contents, LIBXML_NONET | LIBXML_COMPACT)) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
            throw new RuntimeException('Struktur XML pada file Excel tidak valid.');
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return $doc;
    }

    private function xpath($doc)
    {
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('m', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        return $xpath;
    }

    private function columnIndex($reference)
    {
        preg_match('/^[A-Z]+/i', $reference, $match);
        $letters = strtoupper(isset($match[0]) ? $match[0] : '');
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }
        return $index - 1;
    }

    private function columnName($index)
    {
        $name = '';
        for ($number = $index + 1; $number > 0; $number = intdiv($number - 1, 26)) {
            $name = chr((($number - 1) % 26) + 65).$name;
        }
        return $name;
    }

    private function escape($value)
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
