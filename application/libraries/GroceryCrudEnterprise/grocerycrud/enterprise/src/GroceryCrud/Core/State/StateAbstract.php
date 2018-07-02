<?php

namespace GroceryCrud\Core\State;

use GroceryCrud\Core\Exceptions\Exception;
use GroceryCrud\Core\GroceryCrud as GCrud;
use GroceryCrud\Core\Helpers\ArrayHelper;
use GroceryCrud\Core\Model;
use GroceryCrud\Core\GroceryCrud;
use GroceryCrud\Core\Error\ErrorMessageInteface;
use GroceryCrud\Core\Model\ModelFieldType;

class StateAbstract
{
    const EXTRAS_FIELD_NAME = 'grocery_crud_extras';
    const WITH_PRIMARY_KEY = 1;
    const WITH_TABLE_NAME = 1;

    public $config;

    /**
     * @var \GroceryCrud\Core\GroceryCrud
     */
    public $gCrud;

    public $filtersAnd = [];
    public $filtersOr = [];

    public $formatData = null;

    /**
     * @var array|null
     */
    protected $fieldTypes = null;

    function __construct(GCrud $gCrud)
    {
        $this->gCrud = $gCrud;
        $this->config = $this->getConfigParameters();
    }

    /**
     * @overide
     * @return object
     */
    public function getStateParameters()
    {
        return (object)array();
    }

    public function getConfigParameters()
    {
        $config = $this->gCrud->getConfig();

        // Fallback when the date_format is not at the list or when empty
        if (empty($config['date_format']) || !in_array($config['date_format'], ['uk-date', 'us-date', 'sql-date'])) {
            $config['date_format'] = 'uk-date';
        }

        return $config;
    }

    public function addcsrfToken($output) {
        $CsrfData = $this->getcsrfTokenData();
        if ($CsrfData !== null) {
            $output->csrfToken = $CsrfData;
        }

        return $output;
    }

    public function getSkin() {
        return strtolower($this->gCrud->getSkin());
    }

    public function getcsrfTokenData() {

        if ($this->gCrud->getcsrfTokenValue() !== null) {
            $tokenName = $this->gCrud->getcsrfTokenName();

            if (!empty($tokenName)) {
                return (object) [
                    'inputName' => $tokenName,
                    'inputValue' => $this->gCrud->getcsrfTokenValue()
                ];
            }
        }

        return null;
    }

    public function filterData($data) {
        $config = $this->getConfigParameters();

        if ($config['xss_clean']) {
            foreach ($data as $data_name => $data_value) {
                if (is_string($data_value)) {
                    $data[$data_name] = strip_tags($data_value);
                }
            }
        }

        return $data;
    }

    public function formatInputData($data) {
        $dateFields = array_merge($this->getColumnDateFields(), $this->getColumnDatetimeFields());

        foreach ($dateFields as $fieldName) {
            if (isset($data[$fieldName])) {
                $data[$fieldName] = $this->dateFormatToSql($data[$fieldName]);
            }
        }

        return $data;
    }

    public function setInitialData()
    {
        $this->setModel();

        $model = $this->gCrud->getModel();

        if ($this->gCrud->getDbTableName() !== null) {
            $model->setTableName($this->gCrud->getDbTableName());
        }

        $primaryKeys = $this->gCrud->getPrimaryKeys();
        if (!empty($primaryKeys)) {
               foreach ($primaryKeys as $tableName => $primaryKey) {
                   $model->setPrimaryKey($primaryKey, $tableName);
               }
        }
    }

    public function setColumns()
    {
        $allColumns = $this->getColumns(StateAbstract::WITH_PRIMARY_KEY);
        $this->gCrud->getModel()->setColumns($this->removeRelationalColumns($allColumns));
    }

    public function removeRelationalColumns($columns)
    {
        $relational_fields = array_keys($this->gCrud->getRelationNtoN());

        foreach ($columns as $rowNum => $columnName) {
            if (in_array($columnName, $relational_fields)) {
                unset($columns[$rowNum]);
            }
        }

        return $columns;
    }

    /**
     * Usually we don't use "clever" functions. However here as the callback names are hardcoded for now, we
     * did create dynamic names as getters
     *
     * @param $inputStateParameters
     * @param $callbackTitle
     * @param $operationCallback
     * @return {Object}
     */
    public function stateOperationWithCallbacks($inputStateParameters, $callbackTitle, $operationCallback)
    {
        $callbackBeforeString = "getCallbackBefore" . $callbackTitle;
        $callbackBefore = $this->gCrud->$callbackBeforeString();
        $callbackOperationString = "getCallback" . $callbackTitle;
        $callbackOperation = $this->gCrud->$callbackOperationString();
        $callbackAfterString = "getCallbackAfter" . $callbackTitle;
        $callbackAfter = $this->gCrud->$callbackAfterString();

        $inputStateParameters = $this->beforeStateOperation($callbackBefore, $inputStateParameters);
        // If the callback will return false then do not continue
        if ($inputStateParameters === false || $inputStateParameters instanceof ErrorMessageInteface) {
            return $inputStateParameters;
        }

        $inputStateParameters = $this->stateOperation($callbackOperation, $inputStateParameters, $operationCallback);
        if ($inputStateParameters === false || $inputStateParameters instanceof ErrorMessageInteface) {
            return $inputStateParameters;
        }

        $inputStateParameters = $this->afterStateOperation($callbackAfter, $inputStateParameters);
        if ($inputStateParameters === false || $inputStateParameters instanceof ErrorMessageInteface) {
            return $inputStateParameters;
        }

        return $inputStateParameters;
    }

    public function getMultiselectFields() {

        $fieldTypes = $this->getFieldTypes();
        $multiselectFields = [];

        foreach ($fieldTypes as $fieldName => $fieldType) {
            if (in_array($fieldType->dataType, [
                GroceryCrud::FIELD_TYPE_MULTIPLE_SELECT_SEARCHABLE,
                GroceryCrud::FIELD_TYPE_MULTIPLE_SELECT_NATIVE
            ]) ) {
                $multiselectFields[] = $fieldName;
            }
        }

        return $multiselectFields;
    }

    public function enhanceFormOutputData($outputData, $primaryKeyValue) {
        $model = $this->gCrud->getModel();
        $relationNtoN = $this->gCrud->getRelationNtoN();
        $multiselectFields = $this->getMultiselectFields();
        $dateFields = array_merge($this->getColumnDateFields(), $this->getColumnDatetimeFields());

        foreach ($relationNtoN as $field) {
            $outputData[$field->fieldName] = $model->getRelationNtoNData($field, $primaryKeyValue);
        }

        foreach ($multiselectFields as $fieldName) {
            if (isset($outputData[$fieldName])) {
                $outputData[$fieldName] = !empty($outputData[$fieldName]) ? explode(",", $outputData[$fieldName]) : null;
            }
        }

        foreach ($dateFields as $fieldName) {
            if (isset($outputData[$fieldName])) {
                $outputData[$fieldName] = $this->sqlToDateFormat($outputData[$fieldName]);
            }
        }


        return $outputData;
    }

    public function setResponseStatusAndMessage($output, $callbackResult) {

        if ($callbackResult instanceof ErrorMessageInteface) {
            $output->message = $callbackResult->getMessage();
            $output->status  = 'failure';
        } else {
            $output->message = $callbackResult === false ? 'Unknown error' : 'Success';
            $output->status = $callbackResult === false ? 'failure' : 'success';
        }

        return $output;
    }

    public function hasErrorResponse($response) {
        return $response === false || $response instanceof ErrorMessageInteface;
    }

    public function stripTags($results)
    {
        $callbackColumns = $this->gCrud->getCallbackColumns();

        foreach ($results as &$result) {
            foreach ($result as $columnName => &$columnValue) {
                if (is_array($columnValue) || is_object($columnValue) || isset($callbackColumns[$columnName])) {
                    continue;
                }

                $columnValue = strip_tags($columnValue);
            }
        }

        return $results;
    }

    public function getColumnDateFields() {
        $fieldTypes = $this->getFieldTypes();
        $dateTypeColumns = [];

        foreach($fieldTypes as $fieldName => $field) {
            if ($field->dataType == 'date') {
                $dateTypeColumns[] = $fieldName;
            }
        }

        return $dateTypeColumns;
    }

    public function getColumnDatetimeFields() {
        $fieldTypes = $this->getFieldTypes();
        $dateTypeColumns = [];

        foreach($fieldTypes as $fieldName => $field) {
            if (in_array($field->dataType, ['datetime', 'timestamp'])) {
                $dateTypeColumns[] = $fieldName;
            }
        }

        return $dateTypeColumns;
    }

    /**
     * @param string $dateString
     * @return string
     */
    public function sqlToDateFormat($dateString) {
        $formatDate = $this->getDateFormatData();

        if ($formatDate->dateNeedsFormatting) {
            return preg_replace($formatDate->sqlDateRegex, $formatDate->formatFromSql, $dateString);
        }

        return $dateString;
    }

    /**
     * @param string $dateString
     * @return string
     */
    public function dateFormatToSql($dateString) {
        $formatDate = $this->getDateFormatData();

        if ($formatDate->dateNeedsFormatting) {
            return preg_replace($formatDate->dateRegex, $formatDate->replacementString, $dateString);
        }

        return $dateString;
    }

    /**
     * @return object
     */
    public function getDateFormatData() {
        if ($this->formatData !== null) {
            return $this->formatData;
        }

        $config =  $this->getConfigParameters();
        $dateNeedsFormatting = $config['date_format'] != 'sql-date';
        $dateRegex = '';
        $replacementString = '';
        $sqlDateRegex = '/(\d{4})-(\d{1,2})-(\d{1,2})/';
        $formatFromSql = '';

        if ($dateNeedsFormatting) {
            $dateRegex = '/(\d{1,2})\/(\d{1,2})\/(\d{4})/';

            switch ($config['date_format']) {
                case 'uk-date':
                    $replacementString = '$3-$2-$1';
                    $formatFromSql = '$3/$2/$1';
                    break;

                case 'us-date':
                    $replacementString = '$3-$1-$2';
                    $formatFromSql = '$2/$3/$1';
                    break;
            }
        }

        $this->formatData = (object)[
            'dateNeedsFormatting' => $dateNeedsFormatting,
            'dateRegex' => $dateRegex,
            'replacementString' => $replacementString,
            'sqlDateRegex' => $sqlDateRegex,
            'formatFromSql' => $formatFromSql
        ];

        return $this->formatData;

    }

    public function enhanceColumnResults($results)
    {
        $primaryKeyName = $this->getPrimaryKeyName();

        $callbackColumns = $this->gCrud->getCallbackColumns();
        $actionButtons = $this->gCrud->getActionButtons();

        $dateColumns = array_merge($this->getColumnDateFields(), $this->getColumnDatetimeFields());
        $dateFormatData = $this->getDateFormatData();
        $dateNeedsFormatting = $dateFormatData->dateNeedsFormatting;
        $sqlDateRegex = $dateFormatData->sqlDateRegex;
        $formatFromSql = $dateFormatData->formatFromSql;

        $config = $this->getConfigParameters();

        $char_limiter = $config['column_character_limiter'];

        foreach ($results as &$result) {
            foreach ($result as $columnName => &$columnValue) {
                if (is_array($columnValue)) {
                    continue;
                }

                if (isset($callbackColumns[$columnName])) {
                    $columnValue = $callbackColumns[$columnName]($columnValue, $result);
                } else if ($dateNeedsFormatting && in_array($columnName, $dateColumns)) {
                    $columnValue = preg_replace($sqlDateRegex, $formatFromSql, $columnValue);
                } else {
                    $columnValue = strip_tags($columnValue);
                    if ($char_limiter > 0 && (mb_strlen($columnValue, 'UTF-8') > $char_limiter)) {
                        $columnValue = mb_substr($columnValue, 0 , $char_limiter - 1, 'UTF-8') . '...';
                    }
                }
            }

            $result[StateAbstract::EXTRAS_FIELD_NAME] = (object) array(
                'primaryKeyValue' => $result[$primaryKeyName]
            );

            foreach ($actionButtons as $actionButton) {
                if (!isset($result[StateAbstract::EXTRAS_FIELD_NAME]->actionButtons)) {
                    $result[StateAbstract::EXTRAS_FIELD_NAME]->actionButtons = [];
                }
                $callback = $actionButton->urlCallback;

                $result[StateAbstract::EXTRAS_FIELD_NAME]->actionButtons[] = (object)array(
                    'label' => $actionButton->label,
                    'iconCssClass' => $actionButton->iconCssClass,
                    'url' => $callback($result),
                    'newTab' => $actionButton->newTab
                );
            }
        }

        return $results;
    }

    /**
     * @param array $results
     * @return array
     */
    public function filterByColumns($results)
    {
        $columnNames = $this->getColumns();
        $finalResults = [];

        foreach ($results as $row) {
            $tmpRow = [];
            foreach ($row as $fieldName => $fieldValue) {
                if (in_array($fieldName, $columnNames)) {
                    $tmpRow[$fieldName] = $fieldValue;
                }
            }
            $finalResults[] = $tmpRow;
        }

        return $finalResults;

    }

    public function getColumns($extra = null) {
        $columns = $this->gCrud->getColumns();
        $unsetColumns = $this->gCrud->getUnsetColumns();

        if (empty($columns)) {
            $columns = $this->getColumnNames();
        }

        foreach ($unsetColumns as $columnName) {
            $columns = ArrayHelper::array_reject($columns, function ($value) use ($columnName) {
                return $value === $columnName;
            });
        }

        if ($extra === StateAbstract::WITH_PRIMARY_KEY) {
            $primaryKey = $this->getPrimaryKeyName();
            if (!in_array($primaryKey, $columns)) {
                array_unshift($columns, $primaryKey);
            }
        }

        return $columns;
    }

    public function getRelations1ToN()
    {
        $relations1ToN = $this->gCrud->getDbRelations1ToN();

        foreach ($relations1ToN as $num_row => $relation) {
            $relations1ToN[$num_row]->relationPrimaryKey = $this->getPrimaryKeyName($relation->tableName);
        }

        return $relations1ToN;
    }

    public function getRelationsNToN()
    {
        $relationsNToN = $this->gCrud->getDbRelationsNToN();

        foreach ($relationsNToN as &$relation) {
            $relation->referrerPrimaryKeyField = $this->getPrimaryKeyName($relation->referrerTable);
        }

        return $relationsNToN;
    }

    public function beforeStateOperation($stateCallback, $stateParameters) {
        if ($stateCallback === null) {
            return $stateParameters;
        }
        return $stateCallback($stateParameters);
    }

    public function stateOperation($stateCallback, $stateParameters, $operationCallback) {
        if ($stateCallback === null) {
            return $operationCallback($stateParameters);
        }

        return $stateCallback($stateParameters);
    }

    public function afterStateOperation($stateCallback, $stateParameters) {
        if ($stateCallback === null) {
            return $stateParameters;
        }
        return $stateCallback($stateParameters);
    }

    public function getColumnNames() {
        $config = $this->getConfigParameters();
        $model = $this->gCrud->getModel();

        $cachedString = $this->getUniqueCacheName(self::WITH_TABLE_NAME) .'+columnNames';
        if ($config['backend_cache'] && $this->isInCache($cachedString)) {
            $columnNames = json_decode($this->getCacheItem($cachedString));
        } else {
            $columnNames = $model->getColumnNames();

            if ($config['backend_cache']) {
                $this->gCrud->getCache()->setItem($cachedString, json_encode($columnNames));
            }
        }

        foreach ($this->gCrud->getRelationNtoN() as $fieldName => $fieldInfo) {
            $columnNames[] = $fieldName;
        }

        return $columnNames;

    }

    public function setFilters($searchArray)
    {
        if (!empty($searchArray['_gcrud_search_all'])) {
            $searchText = $searchArray['_gcrud_search_all'];
            $columns = $this->getColumns();
            $columnTypes = $this->getFieldTypes();

            foreach ($columns as $column_name) {
                // Not all the field types are searchable with "like" operation and more specifically the date and datetime
                // is throwing and error: "Illegal mix of collations for operation 'like' while searching a non english char"
                if (!in_array($columnTypes[$column_name]->dataType, ['datetime', 'date', 'timestamp'])) {
                    $this->filtersOr[$column_name] = $searchText;
                }
            }

        } else {
            foreach ($searchArray as $fieldName => $fieldValue) {
                $this->filtersAnd[$fieldName] = $fieldValue;
            }
        }
    }

    public function getPrimaryKeyName($dbTableNameRaw = null)
    {
        $config = $this->getConfigParameters();
        $primaryKeyName = null;

        $dbTableName = $dbTableNameRaw !== null ? $dbTableNameRaw : $this->gCrud->getDbTableName();
        $cacheUniqueId = $this->getUniqueCacheName() . '+' . $dbTableName;

        if (!$config['backend_cache'] || $this->gCrud->getCache()->getItem($cacheUniqueId . '+primaryKeyField') === null) {
            $primaryKeyName = $this->gCrud->getModel()->getPrimaryKeyField($dbTableName);

            if ($config['backend_cache']) {
                $this->gCrud->getCache()->setItem($cacheUniqueId . '+primaryKeyField', $primaryKeyName);
            }
        } else {
            $primaryKeyName = $this->gCrud->getCache()->getItem($cacheUniqueId . '+primaryKeyField');
        }

        return $primaryKeyName;
    }

    public function removePrimaryKeyFromList($fieldList)
    {
        if(($key = array_search($this->getPrimaryKeyName(), $fieldList)) !== false) {
            unset($fieldList[$key]);
        }

        return $fieldList;
    }

    public function getUniqueCacheName($extraInfo = null)
    {
        $model = $this->gCrud->getModel();

        $finalString = $model->getDbUniqueId();

        if ($extraInfo === self::WITH_TABLE_NAME) {
            $finalString .= '+' . $this->gCrud->getDbTableName();
        }

        return $finalString;
    }

    /**
     * @param string $stringWithFields
     * @return array
     */
    public function getFieldsArray($stringWithFields) {

        preg_match_all("{([^{}]+)}", $stringWithFields, $matches);

        $fields = [];
        foreach ($matches[0] as $match) {
            if (strstr($stringWithFields, '{' . $match . '}')) {
                $fields[] = $match;
            }
        }

        return $fields;
    }

    /**
     * @param string $stringWithFields
     * @return array
     */
    public function getMatchesAsArray($stringWithFields) {

        preg_match_all("{([^{}]+)}", $stringWithFields, $matches);

        return $matches[0];
    }

    public function transformDataWithMultipleFields($data, $stringWithFields) {
        $matches = $this->getMatchesAsArray($stringWithFields);

        $finalData = [];

        foreach ($data as $row) {
            $tmp = (object)[];
            $tmp->id = $row->id;
            $tmp->title = '';

            foreach ($matches as $match) {
                if (isset($row->title[$match])) {
                    $tmp->title .= $row->title[$match];
                } else {
                    $tmp->title .= $match;
                }
            }

            $finalData[] = $tmp;
        }

        return $finalData;
    }

    public function transformRelationalData($data) {
        $finalData = [];
        foreach ($data as $row) {
            $finalData[$row->id] = $row->title;
        }

        return $finalData;
    }

    public function getRelationalData($tableName, $titleField , $where, $orderBy) {
        $model = $this->gCrud->getModel();

        // In case we have already cached the primary key for the relational data
        $model->setPrimaryKey($this->getPrimaryKeyName($tableName), $tableName);

        $hasMultipleFields = strstr($titleField, "{");

        $columns = $hasMultipleFields
            ? $this->getFieldsArray($titleField)
            : $titleField;

        if ($orderBy !== null) {
            $orderBy = $orderBy;
        } else {
            $orderBy = is_array($columns) ? $columns[0] : $columns;
        }

        $data = $model->getRelationData(
            $tableName,
            $columns,
            $where,
            $orderBy
        );

        if ($hasMultipleFields) {
            $data = $this->transformDataWithMultipleFields($data, $titleField);
        }

        return $data;
    }

    /**
     * @return array|Model\ModelFieldType[]
     */
    public function getFieldTypes()
    {
        if ($this->fieldTypes !== null) {
            return $this->fieldTypes;
        }

        $config = $this->getConfigParameters();
        $model = $this->gCrud->getModel();

        $dbTableName = $this->gCrud->getDbTableName();
        $cacheUniqueId = $this->getUniqueCacheName(self::WITH_TABLE_NAME);

        // Not in the cache? Then create it!
        if (!$config['backend_cache'] || $this->gCrud->getCache()->getItem($cacheUniqueId . '+fieldTypes') === null) {
            $fieldTypes = $model->getFieldTypes($dbTableName);

            if ($config['backend_cache']) {
                $this->gCrud->getCache()->setItem($cacheUniqueId . '+fieldTypes', json_encode($fieldTypes));
            }
        } else {
            $fieldTypes = (array)json_decode($this->gCrud->getCache()->getItem($cacheUniqueId . '+fieldTypes'));
        }

        $relations = $this->gCrud->getRelations1toMany();
        $requiredFields = $this->gCrud->getRequiredFields();

        foreach ($fieldTypes as $fieldName => $fieldType) {
            if (isset($relations[$fieldName])) {

                $relation = $relations[$fieldName];

                $relationalData = $this->getRelationalData(
                    $relation->tableName,
                    $relation->titleField,
                    $relation->where,
                    $relation->orderBy
                );

                $fieldTypes[$fieldName]->permittedValues = $relationalData;
                $fieldTypes[$fieldName]->dataType = 'relational';
            }

            $fieldTypes[$fieldName]->isRequired = in_array($fieldName, $requiredFields);
        }

        foreach ($this->gCrud->getRelationNtoN() as $fieldName => $relation) {
            $fieldTypes[$fieldName] = (object)array(
                'dataType' => 'relational_n_n',
                'isNullable' => false,
                'permittedValues' => $this->getRelationalData(
                    $relation->referrerTable,
                    $relation->referrerTitleField,
                    $relation->where,
                    $relation->sortingFieldName
                )
            );
        }

        $manualFieldTypes = $this->gCrud->getFieldTypes();
        foreach ($manualFieldTypes as $fieldName => $filedTypeInfo) {
            if (isset($fieldTypes[$fieldName])) {
                $fieldTypes[$fieldName]->dataType = $filedTypeInfo->dataType;

                if ($filedTypeInfo->permittedValues !== null) {
                    $fieldTypes[$fieldName]->permittedValues = $filedTypeInfo->permittedValues;
                }

                if ($filedTypeInfo->options !== null) {
                    $fieldTypes[$fieldName]->options = $filedTypeInfo->options;
                }

                if (is_array($fieldTypes[$fieldName]->options) && isset($fieldTypes[$fieldName]->options['defaultValue'])) {
                    $fieldTypes[$fieldName]->defaultValue = $fieldTypes[$fieldName]->options['defaultValue'];
                }
            }
        }

        $texteditorFields = $this->gCrud->getTextEditorFields();
        foreach ($texteditorFields as $fieldName => $fieldValue) {
            if (isset($fieldTypes[$fieldName])) {
                $fieldTypes[$fieldName]->dataType = 'texteditor';
            }
        }

        $readOnlyFields = $this->gCrud->getReadOnlyFields();

        foreach ($readOnlyFields as $fieldName) {
            if (isset($fieldTypes[$fieldName])) {
                $fieldTypes[$fieldName]->isReadOnly = true;
            }
        }

        foreach ($fieldTypes as $fieldName => &$field) {
            $field->isReadOnly = property_exists($field, 'isReadOnly') ? $field->isReadOnly : false;
        }

        $this->fieldTypes = $fieldTypes;

        return $fieldTypes;
    }

    public function getDateFormat() {
        $config = $this->getConfigParameters();

        return $config['date_format'];
    }

    public function setModel()
    {
        $model = $this->gCrud->getModel();
        if ($model === null) {
            $config = $this->gCrud->getDatabaseConfig();
            if ($config === null) {
                throw new Exception('You need to add a configuration file first');
            }
            $model = new Model($config);
            $this->gCrud->setModel($model);
        }
    }

    public function getEditFields()
    {
        return $this->transformFieldsList($this->gCrud->getEditFields(), $this->gCrud->getUnsetEditFields());
    }

    public function getCloneFields()
    {
        return $this->transformFieldsList($this->gCrud->getCloneFields(), $this->gCrud->getUnsetCloneFields());
    }

    public function getAddFields()
    {
        return $this->transformFieldsList($this->gCrud->getAddFields(), $this->gCrud->getUnsetAddFields());
    }

    public function getFilteredData($fields, $data, $readOnlyFields = [])
    {
        $finalData = [];

        $relationNtoNfields = $this->gCrud->getRelationNtoN();
        $fieldTypes = $this->getFieldTypes();

        foreach ($fields as $field) {
            if (array_key_exists($field->name, $data) && !in_array($field->name, $readOnlyFields)) {
                if (!isset($relationNtoNfields[$field->name])) {
                    $finalData[$field->name] = $this->filterValue($fieldTypes, $field->name, $data[$field->name]);
                }
            }
        }

        return $finalData;
    }

    public function filterValue($fieldTypes, $fieldName, $fieldValue) {
        if( isset($fieldTypes[$fieldName]) &&
            $fieldTypes[$fieldName]->isNullable === true &&
            $fieldValue === ''
        ) {
            return null;
        }

        return $fieldValue;
    }

    public function getRelationNtoNData($fields, $data)
    {
        $relationNtoNData = [];

        $relationNtoNfields = $this->gCrud->getRelationNtoN();

        foreach ($fields as $field) {
            if (array_key_exists($field->name, $data)) {
                if (isset($relationNtoNfields[$field->name])) {
                    $relationNtoNData[$field->name] = $data[$field->name];
                }
            }
        }

        return $relationNtoNData;
    }

    public function getFieldTypesAddForm() {
        $fieldTypesAddForm = $this->gCrud->getFieldTypesAddForm();
        $callbackAddFields  = $this->gCrud->getCallbackAddFields();

        foreach ($callbackAddFields as $fieldName => $callback) {
            $fieldTypeModel = new ModelFieldType();
            $fieldTypeModel->dataType = GroceryCrud::FIELD_TYPE_BACKEND_CALLBACK;
            $fieldTypeModel->defaultValue = $callback();

            $fieldTypesAddForm[$fieldName] = $fieldTypeModel;
        }

        return $fieldTypesAddForm;
    }

    public function getFieldTypesEditForm() {
        $fieldTypesEditForm = $this->gCrud->getFieldTypesEditForm();
        $callbackEditFields  = $this->gCrud->getCallbackEditFields();

        foreach ($callbackEditFields as $fieldName => $callback) {
            $fieldTypeModel = new ModelFieldType();
            $fieldTypeModel->dataType = GroceryCrud::FIELD_TYPE_BACKEND_CALLBACK;

            $fieldTypesEditForm[$fieldName] = $fieldTypeModel;
        }

        return $fieldTypesEditForm;
    }

    public function getFieldTypeColumns() {
        $fieldTypeColumns = $this->gCrud->getFieldTypeColumns();
        $callbackColumns  = $this->gCrud->getCallbackColumns();

        foreach ($callbackColumns as $fieldName => $callback) {
            $fieldTypeModel = new ModelFieldType();
            $fieldTypeModel->dataType = GroceryCrud::FIELD_TYPE_CALLBACK_COLUMN;

            $fieldTypeColumns[$fieldName] = $fieldTypeModel;
        }

        return $fieldTypeColumns;
    }

    public function getFieldTypesReadForm() {
        $fieldTypesReadForm = $this->gCrud->getFieldTypesReadForm();
        $callbackReadFields  = $this->gCrud->getCallbackReadFields();

        foreach ($callbackReadFields as $fieldName => $callback) {
            $fieldTypeModel = new ModelFieldType();
            $fieldTypeModel->dataType = GroceryCrud::FIELD_TYPE_BACKEND_CALLBACK;

            $fieldTypesReadForm[$fieldName] = $fieldTypeModel;
        }

        return $fieldTypesReadForm;
    }


    public function getFieldTypesCloneForm() {
        $fieldTypesCloneForm = $this->gCrud->getFieldTypesCloneForm();
        /*$callbackCloneFields  = $this->gCrud->getCallbackCloneFields();

        foreach ($callbackCloneFields as $fieldName => $callback) {
            $fieldTypeModel = new ModelFieldType();
            $fieldTypeModel->dataType = GroceryCrud::FIELD_TYPE_BACKEND_CALLBACK;

            $fieldTypesCloneForm[$fieldName] = $fieldTypeModel;
        }*/

        return $fieldTypesCloneForm;
    }

    public function transformFieldsList($simpleList, $unsetFields) {
        $transformedList = [];
        $displayAs = $this->gCrud->getDisplayAs();

        if (empty($simpleList)) {
            $simpleList = $this->removePrimaryKeyFromList($this->getColumnNames());
        }

        if (!empty($unsetFields)) {
            foreach ($unsetFields as $unsetFieldName) {
                $simpleList = ArrayHelper::array_reject($simpleList, function ($value) use ($unsetFieldName) {
                    return $unsetFieldName === $value;
                });
            }
        }

        foreach ($simpleList as $fieldName) {
            $transformedList[] = (object)array(
                'name' => $fieldName,
                'displayAs' => !empty($displayAs[$fieldName]) ? $displayAs[$fieldName] : ucfirst(str_replace('_',' ', $fieldName))
            );
        }

        return $transformedList;
    }

    public function isInCache($itemAsString) {
        return $this->gCrud->getCache()->getItem($itemAsString) !== null;
    }

    public function getCacheItem($itemAsString) {
        return $this->gCrud->getCache()->getItem($itemAsString);
    }

    public function getUniqueId() {
        return $this->gCrud->getLayout()->getUniqueId();
    }

    public function getValidationRules()
    {
        $validator = $this->gCrud->getValidator();
        $validationRules = $this->gCrud->getValidationRules();
        foreach ($validationRules as $rule) {
            $validator->set_rule($rule['fieldName'], $rule['rule'], $rule['parameters']);
        }

        $displayAs = $this->gCrud->getDisplayAs();
        $uniqueFields = $this->gCrud->getUniqueFields();

        if (!empty($uniqueFields)) {
            $this->gCrud->getModel()->setPrimaryKey($this->getPrimaryKeyName());
            $uniqueCallback = function ($field, $value) {
                $stateParameters = $this->getStateParameters();
                $primaryKeyValue = !empty($stateParameters->primaryKeyValue) ? $stateParameters->primaryKeyValue : null;
                return $this->gCrud->getModel()->isUnique($field, $value, $primaryKeyValue);
            };
            $uniqueCallback = \Closure::bind($uniqueCallback, $this);
            $validator->setUniqueCallback($uniqueCallback);
        }

        foreach ($displayAs as $fieldName => $display) {
            $validator->set_label($fieldName, $display);
        }

        $requiredFields = $this->gCrud->getRequiredFields();
        foreach ($requiredFields as $fieldName) {
            $validator->set_rule($fieldName, 'required');
        }

        foreach ($uniqueFields as $fieldName) {
            $validator->set_rule($fieldName, 'unique');
        }

        $validator->set_data($this->getStateParameters()->data);

        return $validator;
    }
}