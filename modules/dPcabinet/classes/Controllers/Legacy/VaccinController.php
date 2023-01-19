<?php

/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Cabinet\Controllers\Legacy;

use Exception;
use Ox\Core\CApp;
use Ox\Core\CLegacyController;
use Ox\Core\CView;
use Ox\Mediboard\Cabinet\Vaccination\Services\EtiquetteVaccinService;
use Ox\Mediboard\Hospi\CModeleEtiquette;
use Ox\Mediboard\Medicament\CMedicamentClasseATC;

class VaccinController extends CLegacyController
{
    /**
     * @throws Exception
     */
    public function autocompleteVaccination(): void
    {
        $this->checkPermRead();
        $keyword = CView::post("speciality", "str");
        CView::checkin();

        $products_load = CMedicamentClasseATC::getProduitsByATC("J07");

        $list_products = [];
        if ($keyword) {
            foreach ($products_load as $_produit) {
                if (str_contains($_produit->libelle, strtoupper($keyword))) {
                    $list_products[] = $_produit;
                }
            }
        } else {
            $list_products = $products_load;
        }

        $this->renderSmarty(
            'inc_list_vaccin_autocomplete',
            [
                'list_products' => $list_products,
                'keyword'       => $keyword,
            ]
        );
    }

    public function choiceNbEtiquette()
    {
        $this->checkPermRead();

        $injection_id = CView::get("injection_id", "ref class|CInjection");

        CView::checkin();

        $this->renderSmarty(
            "vaccination/vw_choice_nb_etiquette",
            [
                'injection_id' => $injection_id
            ]
        );
    }

    public function printEtiquette()
    {
        $this->checkPermRead();

        $object_class = CView::get("object_class", "str");
        $injection_id = CView::get("injection_id", "ref class|CInjection");
        $nb_etiquette = CView::get("nb_etiquette", "num default|1");

        $spec_params = [
            "str",
            "default" => []
        ];
        $params = CView::get("params", $spec_params);

        CView::checkin();

        $injection = $object_class::findOrFail($injection_id);
        $fields = [];
        $injection->completeLabelFields($fields, $params);

        $etiquetteVaccinService = new EtiquetteVaccinService();
        $etiquetteVaccinService->generateNbEtiquetteForVaccin($nb_etiquette, $fields, $params);

        CApp::rip();
    }
}
