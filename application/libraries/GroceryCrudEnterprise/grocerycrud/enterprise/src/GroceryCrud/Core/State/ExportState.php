<?php
namespace GroceryCrud\Core\State;

use GroceryCrud\Core\GroceryCrud as GCrud;
use GroceryCrud\Core\Render\RenderAbstract;
use GroceryCrud\Core\Model;
use \PHPExcel as PHPExcel;
use \PHPExcel_IOFactory as PHPExcel_IOFactory;

class ExportState extends DatagridState {

    const MAX_AMOUNT_OF_EXPORT = 1000;

    public function getStateParameters()
    {
        $stateParameters = parent::getStateParameters();

        $stateParameters->per_page = ExportState::MAX_AMOUNT_OF_EXPORT;

        return $stateParameters;
    }

    public function render()
    {
        $this->setInitialData();

        // Work-around: this line is going first as it seems that Grocery CRUD is changing the values
        // of the title later (at the getData)
        $relations1toMany = $this->gCrud->getRelations1toMany();
        $relationalData = [];
        $relationalFields = [];

        foreach ($relations1toMany as $fieldName => $relation) {
            $relationalData[$fieldName] = $this->transformRelationalData(
                $this->getRelationalData(
                    $relation->tableName,
                    $relation->titleField,
                    $relation->where,
                    $relation->orderBy
                )
            );
            $relationalFields[] = $fieldName;
        }

        $relationsNtoN = $this->gCrud->getRelationNtoN();
        $relationNtoNData = [];
        $relationNtoNFields = [];

        foreach ($relationsNtoN as $fieldName => $relation) {
            $relationNtoNData[$fieldName] = $this->transformRelationalData(
                    $this->getRelationalData(
                        $relation->referrerTable,
                        $relation->referrerTitleField,
                        $relation->where,
                        $relation->sortingFieldName
                    )
            );
            $relationNtoNFields[] = $fieldName;
        }

        $output = $this->getData();

        $results = $output->data;
        $results = $this->stripTags($this->enhanceColumnResults($results));
        $results = $this->filterByColumns($results);

        foreach ($results as &$row) {

            // ##### Relation 1 to N fields ##########
            foreach ($relationalFields as $fieldName) {
                if (!empty($row[$fieldName])) {
                    if (isset($relationalData[$fieldName][$row[$fieldName]])) {
                        $row[$fieldName] = $relationalData[$fieldName][$row[$fieldName]];
                    } else {
                        $row[$fieldName] = '[' . $row[$fieldName] . ']';
                    }

                }
            }

            // ##### Relation N to N fields ##########
            foreach ($relationNtoNFields as $fieldName) {
                if (!empty($row[$fieldName])) {
                    foreach ($row[$fieldName] as $key => $fieldData) {
                        if (isset($relationNtoNData[$fieldName][$fieldData])) {
                            $row[$fieldName][$key] = $relationNtoNData[$fieldName][$fieldData];
                        } else {
                            // Unset when we don't have value for the id as it is probably deleted
                            unset($row[$fieldName][$key]);
                        }
                    }
                }
            }
        }

        $columns = $this->transformFieldsList($this->gCrud->getColumns(), $this->gCrud->getUnsetColumns());

        $this->exportToExcel($results, $columns);
    }

    public function exportToExcel($data, $columns)
    {
        // Create new PHPExcel object
        $objPHPExcel = new \PHPExcel();

        // Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
            ->setLastModifiedBy("Maarten Balliauw")
            ->setTitle("Office 2007 XLSX Test Document")
            ->setSubject("Office 2007 XLSX Test Document")
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
            ->setKeywords("office 2007 openxml php")
            ->setCategory("Test result file");

        $columnsToExport = [];

        foreach ($columns as $column) {
            $columnsToExport[$column->name] = $column->displayAs;
        }

        $activeSheet = $objPHPExcel->setActiveSheetIndex(0);
        if (!empty($data)) {
            $column = 'A';
            foreach ($data[0] as $field_name => $field_value) {
                if ($field_name === StateAbstract::EXTRAS_FIELD_NAME) {
                    continue;
                }

                if (!isset($columnsToExport[$field_name])) {
                    continue;
                }

                $activeSheet->setCellValue($column . '1', $columnsToExport[$field_name]);
                $column = ++$column;
            }

            $row_number = 2;
            foreach ($data as $row) {
                $column = 'A';
                foreach ($row as $field_name => $field_value) {
                    if (!isset($columnsToExport[$field_name])) {
                        continue;
                    }

                    if (is_array($field_value)) {
                        $field_value = implode(',', $field_value);
                    }

                    $activeSheet->setCellValue($column . $row_number, $field_value);
                    $column = ++$column;
                }
                $row_number += 1;
            }
        }

        // Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('Simple');

        $subjectPlural = $this->gCrud->getSubjectPlural();

        $filename = !empty($subjectPlural) ? $subjectPlural : 'Spreadsheet';

        $filename .= '_' . date('Y-m-d');

        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        // If you're serving to IE over SSL, then the following may be needed
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }
}
