<?php
/**
 * Servicio para controlar y gestionar los filtros y orden de un listado
 *
 * @author Bartolomé Rojas Toledo
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\Request;

class FilterService
{
    private $request = null;
    public array $order = [];
    public array $filters = [];
    public int $page = 1;
    public int $limit = 25;

    /**
     * Esta propiedad solo se rellenará en el caso de que el sistema de orden esté contenido en una variable única llamada current_request,
     * dentro de la petición que se realiza en el momento.
     * @var string
     *
     *
     */
    public $currentRequest = null;


    private string $stringQuery = "";
    private string $stringPath = "";

    public function __construct(Request $request)
    {
        $queryParams = $request->query->all();

        $this->request = $request;
        $this->order = @$queryParams['filter_order'] ?: [];
        $this->currentRequest = @$queryParams['current_request'] ?: null;
        $this->filters = @$queryParams['filter_filters'] ?: [];
        $this->page = @$queryParams['page'] ?: 1;
        $this->limit = @$queryParams['limit'] ?: 25;
        $pathPatterns = explode("?", $this->request->getUri());
        $this->stringPath = $pathPatterns[0];
        $this->stringQuery = $pathPatterns[1] ?? "";
        $this->parseStringRequest();
    }

    /**
     * En el caso de tener una query actual enviada por get,
     * esta función formatea y coloca cada parámetro de la petición
     * actual en las propiedades correspondientes, de manera que se mantengan
     * los filtros y órdenes actuales.
     */
    private function parseStringRequest(): void
    {
        if ($this->currentRequest != null and strlen($this->currentRequest) > 0) {
            $tempArray = [];
            parse_str(urldecode($this->currentRequest), $tempArray);
            $this->order = $tempArray["filter_order"] ?? $this->order;
            $this->limit = $tempArray["limit"] ?? $this->limit;
            $this->page = $tempArray["page"] ?? $this->page;
        } else {
            $this->currentRequest = $this->stringQuery;
        }
    }


    private function getPath(): string
    {
        return $this->stringPath;
    }

    public function getQueryString(): string
    {
        return $this->stringQuery;
    }


    /**
     * Función que hace un stringify de los parámetros
     * para devolver un string con la query
     * @param $param
     * @return string
     */
    public function buildQuery($param): string
    {
        return http_build_query($param);
    }


    /**
     * Devuelve el array con los parámetros de la query actual.
     * @return array
    */
    public function getAll(): array
    {
        return [
            "filter_order" => $this->order,
            "filter_filters" => $this->filters,
            "limit" => $this->limit,
            "page" => $this->page,
        ];
    }

    public function getAllOrders()
    {
        return $this->order;
    }

    private function addOrder($field, $order, &$currentRequest): void
    {
        $exist = false;
        foreach ($currentRequest["filter_order"] as $index => $orderField) {
            if ($orderField["field"] == $field) {
                $exist = true;
                $currentRequest["filter_order"][$index]["order"] = $order;
            }
        }
        if (!$exist) {
            $currentRequest["filter_order"] = [];

            $currentRequest["filter_order"][] = [
                "field" => $field,
                "order" => $order
            ];
        }
    }

    public function addOrderValue($field, $order): void
    {
        $this->order = [["field" => $field, 'order' => $order]];
    }

    public function getCurrentRequestParams(): array
    {
        return [
            "filter_order" => $this->order,
            "filter_filters" => $this->filters,
            "limit" => $this->limit,
            "page" => $this->page,
        ];
    }

    public function addFilter($filterName, $value): static
    {
        $this->filters[$filterName] =  $value;
        return $this;
    }


    public function getCurrentRequest()
    {
        return $this->currentRequest;
    }

    /**
     * Recupera un order específico si existe en los órdenes establecidos
     * @param $fieldName
     * @return bool|array
     */
    public function getOrder($fieldName): bool|array
    {
        foreach ($this->order as $orderField) {
            if ($orderField["field"] == $fieldName) {
                return $orderField;
            }
        }
        return false;
    }


    /**
     * Comprueba si en la request existe este campo ordenado.
     * @param $fieldName
     * @return bool
     */
    public function isOrdered($fieldName): bool
    {
        $parameters = $this->getAll();
        if ($parameters and isset($parameters['filter_order']) and count($parameters['filter_order']) > 0) {
            foreach ($parameters['filter_order'] as $order) {
                if ($order["field"] == $fieldName) return true;
            }
        }
        return false;
    }

    /**
     * Comprueba si ya existe un orden para ese campo y retorna el orden contrario,
     * Se puede utilizar para mostrar los iconos ordenar en una dirección y otra.
     */
    public function getInversedOrder($fieldName): string
    {
        $order = $this->getOrder($fieldName);
        if ($order) {
            if ($order["order"] == "desc") return "asc";
            if ($order["order"] == "asc") return "desc";
        }
        return "asc";
    }

    /**
     * Obtiene el path completo actual con todos los parámetros enviados port get.
     */
    public function getUri(): string
    {
        return $this->request->getUri();
    }


    /**
     * Recibe un array con los parámetros que hay que generar la query del enlace
     * @param $completeRequest
     * @return string
     */
    public function getCompletePath($completeRequest): string
    {
        return $this->getPath() . "?" . $this->buildQuery($completeRequest);
    }


    /**
     * Genera el link para cambiar un orden del filtro.
     * @param $field
     * @param $order
     * @return string
     */
    public function orderBy($field, $order): string
    {
        $completeRequest = $this->futureOrderRequest($field, $order);
        // Simulamos como quedaría el array después de añadirle el orden
        return $this->getCompletePath($completeRequest);
    }


    public function limitBy($newLimit): string
    {
        $completeRequest = $this->getCurrentRequestParams();
        $completeRequest["limit"] = $newLimit < 0 ? 50 : $newLimit;
        $completeRequest["page"] = 1;
        return $this->getCompletePath($completeRequest);
    }

    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    public function pageBy($page): string
    {
        $completeRequest = $this->getCurrentRequestParams();
        $completeRequest["page"] = $page < 0 ? 1 : $page;
        return $this->getCompletePath($completeRequest);
    }

    public function getFilters(){
        return $this->filters;
    }

    public function getOrders(){
        return $this->order;
    }


    /**
     * Coge los parámetros actuales de la petición y modifica el orden para
     * que se pueda generar un link con el orden cambiado
     * @param $fieldName
     * @param $order
     * @return array
     */
    public function futureOrderRequest($fieldName, $order): array
    {
        $currentRequest = $this->getCurrentRequestParams();
        $this->addOrder($fieldName,$order, $currentRequest);
        return $currentRequest;
    }

    public function filterField($fieldName): string
    {
        return "filter_filters[$fieldName]";
    }

    public function filterFormField(): string
    {
        return '<input type="hidden" name="current_request" value="'.$this->getCurrentRequest().'">';
    }


    /**
     * Recupera el valor de un campo filtrado
     * @param $fieldName
     * @return mixed
     */
    public function getFilterValue($fieldName): mixed
    {
        foreach ($this->filters as $indexName => $filter) {
            if($indexName == $fieldName) {
                return $filter;
            }
        }
        return null;
    }


    public function getNewFilter(): FilterService
    {
        return new FilterService((new Request()));
    }

}