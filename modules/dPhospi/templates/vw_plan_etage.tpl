{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=drag_chambre ajax=true}}

<style>
  td.conteneur_chambres_non_places {
    width: 150px;
  }

  table#table_grille {
    table-layout: fixed;
  }

  #grille td.conteneur-chambre {
    background-color: white;
    height: 60px;
  }

  .chambre {
    text-align: center;
    height: 60px;
    padding: 1px;
    box-shadow: 0 0 0 1px silver;
  }

  .chambre a {
    text-shadow: 0 0 0 transparent,
    -1px 0 .0px rgba(255, 255, 255, .7),
    0 1px .0px rgba(255, 255, 255, .7),
    1px 0 .0px rgba(255, 255, 255, .7),
    0 -1px .0px rgba(255, 255, 255, .7);
  }

  #list-chambres-non-placees {
    min-height: 200px;
  }

  #list-chambres-non-placees div.chambre {
    height: 20px;
    width: 150px;
    margin-top: 2px;
    background-color: #DDDDDD;
  }
</style>

<div id="plan_etage_service">
  {{mb_include module=hospi template=vw_plan_etage_service}}
</div>