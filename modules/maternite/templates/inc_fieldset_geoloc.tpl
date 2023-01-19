{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<fieldset>
  <legend>{{tr}}CSejour-Geolocalisation{{/tr}}</legend>

  <table class="form me-no-box-shadow">
    <tr>
      {{me_form_field mb_object=$sejour mb_field=_unique_lit_id nb_cells=2}}
        {{mb_field object=$sejour field=_unique_lit_id hidden=true}}

        <input type="text" name="keywords" />
      {{/me_form_field}}
    </tr>
    <tr>
      {{me_form_field mb_object=$sejour mb_field=service_id nb_cells=2}}
        <select name="service_id" onchange="$V(this.form.keywords, ''); $V(this.form._unique_lit_id, '');">
          <option value="">&mdash; {{tr}}Choose{{/tr}}</option>

          {{foreach from=$services item=_service}}
            <option value="{{$_service->_id}}" {{if $sejour->service_id == $_service->_id}}selected{{/if}}>
                {{$_service->_view}}
            </option>
          {{/foreach}}
        </select>
      {{/me_form_field}}
    </tr>
  </table>
</fieldset>
