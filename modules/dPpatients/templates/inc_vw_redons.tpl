{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$sejour->_ref_redons}}
  <h2>
    {{tr}}CRedon.none{{/tr}}
  </h2>
  {{mb_return}}
{{/if}}

<script>
  Main.add(function() {
    Control.Tabs.create('redons_tabs', true);

    $('me-redons-actions').addClassName('displayed');
  });
</script>

<table class="main">
  <tr>
    <td class="narrow" style="width: 100px;">
      <ul id="redons_tabs" class="control_tabs_vertical">
        {{foreach from=$sejour->_ref_redons item=_redon}}
        <li>
          <a class="me-text-align-left" href="#redon_{{$_redon->_id}}">
            <button type="button" class="fas fa-cog notext me-small" onclick="Redon.editRedon('{{$_redon->_id}}');"></button>
            {{tr}}CRedon.constante_medicale.{{$_redon->constante_medicale}}{{/tr}}
          </a>
        </li>
        {{/foreach}}
      </ul>
    </td>
      <td>
          {{foreach from=$sejour->_ref_redons item=_redon}}
              {{assign var=redon_id value=$_redon->_id}}
              {{assign var=releve   value=$releves.$redon_id}}
            <div id="redon_{{$_redon->_id}}" style="display: none;">
                {{mb_include module=patients template=inc_vw_redon redon=$_redon}}
            </div>
          {{/foreach}}
      </td>
  </tr>
  <tr id="me-redons-actions" class="me-bottom-actions">
    <td colspan="2">
      <div class="me-text-align-center">
        <button class="new me-primary oneclick" onclick="Redon.submitAllForms();">{{tr}}Create{{/tr}}</button>
      </div>
    </td>
  </tr>
</table>


