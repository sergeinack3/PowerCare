<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Controllers;

use Ox\Core\CCanDo;
use Ox\Core\CView;
use Ox\Mediboard\Jfse\Domain\Formula\FormulaOperatorEnum;
use Ox\Mediboard\Jfse\Domain\Formula\FormulaService;
use Ox\Mediboard\Jfse\Exceptions\Formula\FormulaException;
use Ox\Mediboard\Jfse\Responses\SmartyResponse;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormula;
use Ox\Mediboard\Jfse\ViewModels\Formula\CFormulaOperand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FormulaController
 *
 * @package Ox\Mediboard\Jfse\Controllers
 */
final class FormulaController extends AbstractController
{
    /**
     * @var string[][]
     */
    public static $routes = [
        'operands/get'   => [
            'method'  => 'getOperands',
            'request' => 'emptyRequest',
        ],
        'formulas/get'   => [
            'method'  => 'getFormulas',
            'request' => 'emptyRequest',
        ],
        'formula/edit'   => [
            'method' => 'edit',
        ],
        'formula/store'  => [
            'method' => 'store',
        ],
        'formula/delete' => [
            'method' => 'delete',
        ],
    ];

    /** @var FormulaService */
    private $formula_service;

    /**
     * @return string
     */
    public static function getRoutePrefix(): string
    {
        return "formulas";
    }

    /**
     * FormulaController constructor.
     *
     * @param string $route
     */
    public function __construct(string $route)
    {
        parent::__construct($route);

        $this->formula_service = new FormulaService();
    }

    /**
     * @return SmartyResponse
     */
    public function getOperands(): SmartyResponse
    {
        $operands = [];
        $data     = $this->formula_service->listFormulaOperands();
        foreach ($data as $operand) {
            $operands[] = CFormulaOperand::getFromEntity($operand);
        }

        $vars = [
            'operands' => $operands,
        ];

        return new SmartyResponse('formula/operands', $vars);
    }

    /**
     * @return SmartyResponse
     */
    public function getFormulas(): SmartyResponse
    {
        $formulas = [];
        $data     = $this->formula_service->listFormulas();
        foreach ($data as $formula) {
            $formulas[] = CFormula::getFromEntity($formula);
        }

        $operands = [];
        $data     = $this->formula_service->listFormulaOperands();
        foreach ($data as $operand) {
            $operands[] = CFormulaOperand::getFromEntity($operand);
        }

        $vars = [
            "operands" => $operands,
            "formulas" => $formulas,
        ];

        return new SmartyResponse('formula/list', $vars);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function edit(Request $request): SmartyResponse
    {
        $operateur_values = FormulaOperatorEnum::toArray();
        $operands         = [];
        $data             = $this->formula_service->listFormulaOperands();
        foreach ($data as $operand) {
            $operands[] = CFormulaOperand::getFromEntity($operand);
        }

        $formule = new CFormula();
        $data    = [
            "operateurs" => array_flip($operateur_values),
            "operands"   => $operands,
            "formule"    => $formule,
        ];

        return new SmartyResponse('formula/edit', $data);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function editRequest(): Request
    {
        CCanDo::checkEdit();

        return new Request([], ["formula_id" => CView::post('idFormule', 'str')]);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function store(Request $request): SmartyResponse
    {
        $is_saved = $this->formula_service->save(
            $request->get("nomFormule"),
            $request->get("multiplicateur"),
            $request->get("plafond"),
            $request->get("operande1"),
            $request->get("operande2"),
            $request->get("operateur"),
            intval($request->get("idFormule"))
        );

        if (!$is_saved) {
            return SmartyResponse::message('CFormula-not updated', SmartyResponse::MESSAGE_WARNING);
        }

        return SmartyResponse::message('CFormula-updated', SmartyResponse::MESSAGE_SUCCESS);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function storeRequest(): Request
    {
        CCanDo::checkEdit();

        $data =
            [
                "idFormule"      => CView::post('formula_id', 'num'),
                "nomFormule"     => CView::post('label', 'str notNull'),
                "multiplicateur" => CView::post('multiplicateur', 'float notNull'),
                "plafond"        => CView::post('plafond', 'float notNull'),
                "operande1"      => CView::post('operande1', 'str notNull'),
                "operande2"      => CView::post('operande2', 'str notNull'),
                "operateur"      => CView::post('operateur', FormulaOperatorEnum::getProp()),
            ];

        return new Request([], $data);
    }

    /**
     * @param Request $request
     *
     * @return SmartyResponse
     */
    public function delete(Request $request): SmartyResponse
    {
        $service = new FormulaService();
        $service->delete($request->request->get('formula_id'));

        return SmartyResponse::message('CFormula-deleted', SmartyResponse::MESSAGE_SUCCESS);
    }

    /**
     * @return Request
     * @throws \Exception
     */
    public function deleteRequest(): Request
    {
        CCanDo::checkEdit();

        $formula_id = CView::post('formula_id', 'num notNull');

        return new Request([], ['formula_id' => $formula_id]);
    }
}
