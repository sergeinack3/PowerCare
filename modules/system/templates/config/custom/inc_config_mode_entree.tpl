{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $is_last}}
  <select name="c[{{$_feature}}]" onchange="" {{if $is_inherited}}disabled{{/if}}>
    <option value="">&mdash; Choisissez un mode d'entrée</option>

    {{foreach from='Ox\Mediboard\PlanningOp\CModeEntreeSejour::listModeEntree'|static_call:null item=_mode_entree}}
      <option value="{{$_mode_entree->_id}}" {{if $_mode_entree->_id == $value}}selected{{/if}}>{{$_mode_entree}}</option>
    {{/foreach}}
  </select>
{{/if}}