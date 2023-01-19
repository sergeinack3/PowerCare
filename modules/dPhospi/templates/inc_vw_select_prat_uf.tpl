{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="praticien_id" style="width: 15em;"
        onchange="AffectationUf.onSubmitRefresh(this.form, this.options[this.selectedIndex]);">
  <option value="" {{if !$praticien->_id}}selected{{/if}}>
    &mdash; {{tr}}CMediusers-select-professionnel{{/tr}}</option>
  {{mb_include module=mediusers template=inc_options_mediuser selected=$prat_placement->_id list=$praticiens}}
</select>
