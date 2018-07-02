<?php
namespace GroceryCrud\Core\State;

use GroceryCrud\Core\Exceptions\Exception;
use GroceryCrud\Core\GroceryCrud as GCrud;
use GroceryCrud\Core\Render\RenderAbstract;
use GroceryCrud\Core\Model;

class AjaxSearchState extends StateAbstract {

    /**
     * MainState constructor.
     * @param GCrud $gCrud
     */
    function __construct(GCrud $gCrud)
    {
        $this->gCrud = $gCrud;
    }

    public function getStateParameters()
    {
        return (object)array(
            'fieldName' => !empty($_POST['field_name']) ? $_POST['field_name'] : null,
            'searchValue' => !empty($_POST['search_value']) ? $_POST['search_value'] : ''
        );
    }

    public function render()
    {
        $stateParameters = $this->getStateParameters();
        $fieldName = $stateParameters->fieldName;
        $searchValue = $stateParameters->searchValue;

        if (!$fieldName) {
            throw new \Exception('field_name parameter is required');
        }

        $this->setModel();

        $relations = $this->gCrud->getRelations1toMany();

        if (isset($relations[$fieldName])) {
            $relation = $relations[$fieldName];
            $relationalData = $this->getRelationalData(
                $relation->tableName,
                $relation->titleField,
                [
                    $relation->titleField . ' LIKE ?' => '%' . $searchValue . '%'
                ],
                $relation->orderBy
            );
        }

        $output = (object) [
            'total_count' => count($relationalData),
            'items' => $relationalData
        ];

        $output = $this->addcsrfToken($output);

        $render = new RenderAbstract();

        $render->output = json_encode($output);
        $render->outputAsObject = $output;
        $render->isJSONResponse = true;

        return $render;

    }

}