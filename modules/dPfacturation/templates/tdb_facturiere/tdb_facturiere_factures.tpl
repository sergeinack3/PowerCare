{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=facturation template="tdb_cotation/tdb_cotation_filter" form_name="`$facture_switch`-filter"
             filter_callback="TdbFacturiere.refreshList(this.form, '`$facture_switch`-list', 0)" filtre_avance=true
             facture_class=$facture_switch}}
<div id="{{$facture_switch}}-list"></div>
