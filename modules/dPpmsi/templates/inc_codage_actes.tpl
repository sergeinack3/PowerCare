{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=read_only value=false}}
{{mb_default var=show_ccam value=true}}
{{mb_default var=show_ngap value=true}}
{{mb_default var=modal     value=0}}

{{assign var=obj_guid value=$subject->_guid}}

<table class="main layout">
  {{if !$read_only && ($show_ccam || $show_ngap)}}
    <script type="text/javascript">
      Main.add(function() {
        var form = getForm('filterActs-{{$obj_guid}}');
        var url = new Url('mediusers', 'ajax_users_autocomplete');
        url.addParam('edit', '1');
        url.addParam('prof_sante', '1');
        url.addParam('input_field', '_executant_view');
        url.autoComplete(form._executant_view, null, {
          minChars: 0,
          method: 'get',
          select: 'view',
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            var form = getForm('filterActs-{{$obj_guid}}');
            $V(form._executant_view, selected.down('.view').innerHTML);
            $V(form.executant_id, selected.getAttribute('id').split('-')[2]);
          }
        });

        Calendar.regField(form.elements['date_min']);
      });
    </script>

    {{if 'Ox\Mediboard\Ccam\CCodable::hasBillingPeriods'|static_call:$subject}}
      <tr>
        <td colspan="2">
            {{mb_include module=ccam template=inc_billing_periods codable=$subject}}
        </td>
      </tr>
    {{/if}}

    <tr>
      <td>
        <form name="filterActs-{{$obj_guid}}" method="POST" action="?" onsubmit="return PMSI.filterActs(this);">
          <input type="hidden" name="subject_guid" value="{{$subject->_guid}}">
          <fieldset>
            <legend>Filtres sur les actes</legend>
              <table class="form me-no-box-shadow">
                <tr>
                  <th>
                    <label for="filterActs-{{$obj_guid}}__executant_view">{{tr}}CActeCCAM-executant_id{{/tr}}</label>
                  </th>
                  <td>
                    <input type="hidden" name="executant_id" id="filterActs-{{$obj_guid}}_executant_id" value="">
                    <input type="text" name="_executant_view" id="filterActs-{{$obj_guid}}__executant_view" class="autocomplete" value=""/>
                  </td>
                  <th>
                    <label for="filterActs-{{$obj_guid}}_date_min">{{tr}}Date{{/tr}}</label>
                  </th>
                  <td>
                    <input type="hidden" name="date_min" id="filterActs-{{$obj_guid}}_date_min" value="">
                  </td>
                  <th>
                    <label for="filterActs-{{$obj_guid}}_facturable">{{tr}}CActeCCAM-facturable{{/tr}}</label>
                  </th>
                  <td>
                    <select name="facturable" id="filterActs-{{$obj_guid}}_facturable">
                      <option value="">&mdash; {{tr}}Select{{/tr}}</option>
                      <option value="1">{{tr}}Yes{{/tr}}</option>
                      <option value="0">{{tr}}No{{/tr}}</option>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td class="button" colspan="6">
                    <button type="button" class="fa fa-filter me-primary" onclick="this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
                    <button type="button" class="cancel me-tertiary" onclick="PMSI.emptyActFilters(this.form)">{{tr}}Empty{{/tr}}</button>
                  </td>
                </tr>
              </table>
          </fieldset>
        </form>
      </td>
    </tr>
  {{/if}}
  {{if $show_ccam}}
    <tr>
      <td id="codes_ccam_{{$obj_guid}}">
        <script>
          Main.add(function() {
            PMSI.reloadActesCCAM('{{$obj_guid}}', "{{$read_only}}", '{{$modal}}', null, 0);
          });
        </script>
      </td>
    </tr>
  {{/if}}
  {{if $show_ngap}}
    <tr>
      <td>
        <table class="tbl">
          {{if $subject->_class == 'CSejour' && !'dPccam codage allow_ngap_cotation_sejour'|gconf}}
            <tr>
              <td class="text" colspan="20">
                <div class="small-info">
                    {{tr}}CSejour-msg-cotation_ngap_forbidden{{/tr}}
                </div>
              </td>
            </tr>
          {{/if}}
          <tr>
            <th class="title" colspan="20">Actes NGAP</th>
          </tr>
        </table>
      </td>
    </tr>
    <tr>
      <td id="actes_ngap_{{$subject->_guid}}" data-object_id="{{$subject->_id}}" data-object_class="{{$subject->_class}}" data-display="pmsi">
        {{mb_script module=ccam script=actes_ngap ajax=1}}

        {{assign var=_is_dentiste value=0}}
        {{assign var=nb_actes_ngap value=0}}
        {{assign var=page value=0}}
        {{if $subject->_ref_actes_ngap|is_countable}}
          {{assign var=nb_actes_ngap value=$subject->_ref_actes_ngap|@count}}
        {{/if}}
        {{mb_include module=cabinet template=inc_codage_ngap object=$subject target='actes_ngap_'|cat:$subject->_guid display='pmsi'}}
      </td>
    </tr>
  {{/if}}
</table>
