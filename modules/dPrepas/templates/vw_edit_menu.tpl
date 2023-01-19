{{*
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main">
  <tr>
    <td colspan="2">
      <form name="FrmTypeVue" method="get">
        <input type="hidden" name="m" value="{{$m}}" />
        <select name="typeVue" onchange="this.form.submit();">
          <option value="0" {{if $typeVue == 0}}selected{{/if}}>{{tr}}CMenu-msg-typevue{{/tr}}</option>
          <option value="1" {{if $typeVue == 1}}selected{{/if}}>{{tr}}CPlat-msg-typevue{{/tr}}</option>
          <option value="2" {{if $typeVue == 2}}selected{{/if}}>{{tr}}CTypeRepas-msg-typevue{{/tr}}</option>
        </select>
      </form>
      <br />
    </td>
  </tr>
  {{if $typeVue==2}}
    {{mb_include module=repas template=inc_vw_edit_typerepas}}
  {{elseif $typeVue==1}}
    {{mb_include module=repas template=inc_vw_edit_plats}}
  {{else}}
    {{mb_include module=repas template=inc_vw_edit_menu}}
  {{/if}}
</table>