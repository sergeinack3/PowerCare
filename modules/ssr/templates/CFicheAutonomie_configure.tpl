{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=class value=CFicheAutonomie}}

<form name="EditConfig-{{$class}}" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_configure module=$m}}

  <table class="form">
    {{if "forms"|module_active}}
      <tr>
        <th></th>
        <td>
          <div class="small-warning">
            Le module <em>Formulaires</em> doit être actif pour que <em>{{tr}}config-ssr-CFicheAutonomie-use_ex_form{{/tr}}</em> soit pris en compte
          </div>
        </td>
      </tr>
    {{/if}}

    {{mb_include module=system template=inc_config_bool var=use_ex_form}}

    <tr>
      <td class="button" colspan="2">
        <button class="modify">{{tr}}Save{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
