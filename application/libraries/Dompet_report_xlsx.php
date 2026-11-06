<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dompet_report_xlsx
{
    public function create(
        $title,
        $headers,
        $rows,
        $totals,
        $filter_summary,
        $sheet_name = 'Laporan Dompet'
    ) {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi ZIP PHP belum tersedia.');
        }

        $path = tempnam(sys_get_temp_dir(), 'dompet-report-');
        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('File Excel sementara tidak dapat dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->content_types());
        $zip->addFromString('_rels/.rels', $this->root_relationships());
        $zip->addFromString(
            'xl/workbook.xml',
            $this->workbook($this->valid_sheet_name($sheet_name))
        );
        $zip->addFromString(
            'xl/_rels/workbook.xml.rels',
            $this->workbook_relationships()
        );
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString(
            'xl/worksheets/sheet1.xml',
            $this->worksheet($title, $headers, $rows, $totals, $filter_summary)
        );
        $zip->addFromString('docProps/core.xml', $this->core_properties());
        $zip->addFromString('docProps/app.xml', $this->app_properties());
        $zip->close();

        return $path;
    }

    private function worksheet($title, $headers, $rows, $totals, $filter_summary)
    {
        $sheet_rows = '';
        $sheet_rows .= $this->merged_text_row(1, $title, 1, 30);
        $sheet_rows .= $this->merged_text_row(
            2,
            'Dompet Digital SD Kreatif Muhammadiyah 1 Lumajang',
            2,
            22
        );
        $sheet_rows .= $this->merged_text_row(
            3,
            'Dibuat pada '.date('d-m-Y H:i:s'),
            3,
            20
        );
        $sheet_rows .= $this->merged_text_row(
            4,
            'Filter: '.implode(' | ', (array) $filter_summary),
            3,
            25
        );
        $sheet_rows .= '<row r="5" ht="8" customHeight="1"></row>';

        $header_cells = '';
        foreach (array_values($headers) as $column => $header) {
            $reference = $this->column_name($column).'6';
            $header_cells .= $this->text_cell($reference, $header, 4);
        }
        $sheet_rows .= '<row r="6" ht="32" customHeight="1">'
            .$header_cells
            .'</row>';

        $row_number = 7;
        foreach ($rows as $row) {
            $style_text = $row_number % 2 === 0 ? 6 : 5;
            $style_money = $row_number % 2 === 0 ? 8 : 7;
            $cells = '';

            foreach (array_values($row) as $column => $value) {
                $reference = $this->column_name($column).$row_number;
                if (in_array($column, [0, 4, 5, 6], true)) {
                    $cells .= $this->number_cell(
                        $reference,
                        $value,
                        $column === 0 ? $style_text : $style_money
                    );
                } else {
                    $cells .= $this->text_cell($reference, $value, $style_text);
                }
            }

            $sheet_rows .= '<row r="'.$row_number.'" ht="23" customHeight="1">'
                .$cells
                .'</row>';
            $row_number++;
        }

        $total_row = $row_number;
        $sheet_rows .= '<row r="'.$total_row.'" ht="27" customHeight="1">'
            .$this->text_cell('A'.$total_row, 'TOTAL', 9)
            .$this->number_cell('E'.$total_row, $totals[0] ?? 0, 10)
            .$this->number_cell('F'.$total_row, $totals[1] ?? 0, 10)
            .$this->number_cell('G'.$total_row, $totals[2] ?? 0, 10)
            .$this->text_cell('H'.$total_row, 'Jumlah Data', 9)
            .$this->number_cell('I'.$total_row, count($rows), 9)
            .'</row>';

        $last_data_row = max(6, $row_number - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0" showGridLines="0">'
            .'<pane ySplit="6" topLeftCell="A7" activePane="bottomLeft" state="frozen"/>'
            .'</sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="20"/>'
            .'<cols>'
            .'<col min="1" max="1" width="7" customWidth="1"/>'
            .'<col min="2" max="2" width="23" customWidth="1"/>'
            .'<col min="3" max="3" width="32" customWidth="1"/>'
            .'<col min="4" max="4" width="24" customWidth="1"/>'
            .'<col min="5" max="7" width="20" customWidth="1"/>'
            .'<col min="8" max="8" width="22" customWidth="1"/>'
            .'<col min="9" max="9" width="23" customWidth="1"/>'
            .'</cols>'
            .'<sheetData>'.$sheet_rows.'</sheetData>'
            .'<autoFilter ref="A6:I'.$last_data_row.'"/>'
            .'<mergeCells count="5">'
            .'<mergeCell ref="A1:I1"/>'
            .'<mergeCell ref="A2:I2"/>'
            .'<mergeCell ref="A3:I3"/>'
            .'<mergeCell ref="A4:I4"/>'
            .'<mergeCell ref="A'.$total_row.':D'.$total_row.'"/>'
            .'</mergeCells>'
            .'<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            .'<pageSetup orientation="landscape" paperSize="9" fitToWidth="1" fitToHeight="0"/>'
            .'</worksheet>';
    }

    private function merged_text_row($number, $value, $style, $height)
    {
        return '<row r="'.$number.'" ht="'.$height.'" customHeight="1">'
            .$this->text_cell('A'.$number, $value, $style)
            .'</row>';
    }

    private function text_cell($reference, $value, $style)
    {
        return '<c r="'.$reference.'" t="inlineStr" s="'.$style.'">'
            .'<is><t xml:space="preserve">'.$this->escape($value).'</t></is>'
            .'</c>';
    }

    private function number_cell($reference, $value, $style)
    {
        $number = is_numeric($value) ? (float) $value : 0;

        return '<c r="'.$reference.'" s="'.$style.'"><v>'
            .$number
            .'</v></c>';
    }

    private function styles()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="&quot;Rp&quot; #,##0"/></numFmts>'
            .'<fonts count="4">'
            .'<font><sz val="10"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><sz val="16"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FF123D66"/><sz val="11"/><name val="Calibri"/></font>'
            .'<font><b/><color rgb="FFFFFFFF"/><sz val="10"/><name val="Calibri"/></font>'
            .'</fonts>'
            .'<fills count="5">'
            .'<fill><patternFill patternType="none"/></fill>'
            .'<fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF174D84"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFEAF2FB"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF7FAFD"/><bgColor indexed="64"/></patternFill></fill>'
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFD8E3EF"/></left><right style="thin"><color rgb="FFD8E3EF"/></right><top style="thin"><color rgb="FFD8E3EF"/></top><bottom style="thin"><color rgb="FFD8E3EF"/></bottom><diagonal/></border>'
            .'</borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="11">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="3" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            .'<xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function content_types()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            .'</Types>';
    }

    private function root_relationships()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            .'</Relationships>';
    }

    private function workbook($sheet_name)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$this->escape($sheet_name).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbook_relationships()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function core_properties()
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            .'<dc:creator>SD Kreatif</dc:creator>'
            .'<dc:title>Laporan Dompet Digital</dc:title>'
            .'<dcterms:created xsi:type="dcterms:W3CDTF">'.$now.'</dcterms:created>'
            .'</cp:coreProperties>';
    }

    private function app_properties()
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            .'<Application>SD Kreatif</Application>'
            .'</Properties>';
    }

    private function valid_sheet_name($name)
    {
        $name = preg_replace('~[\\\\/?*\[\]:]~', '-', (string) $name);
        $name = trim($name);

        return substr($name === '' ? 'Laporan Dompet' : $name, 0, 31);
    }

    private function column_name($index)
    {
        $name = '';
        for ($number = $index + 1; $number > 0; $number = intdiv($number - 1, 26)) {
            $name = chr((($number - 1) % 26) + 65).$name;
        }

        return $name;
    }

    private function escape($value)
    {
        return htmlspecialchars(
            (string) $value,
            ENT_XML1 | ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
