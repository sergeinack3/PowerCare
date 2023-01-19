{{*
 * @package Mediboard\astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=list_category value='Ox\Mediboard\Astreintes\CCategorieAstreinte::getPrefCategories'|static_call:null}}

{{mb_include module=astreintes template=inc_pref spec=dynamic_enum var=categorie-astreintes values=$list_category use_locale=false}}
