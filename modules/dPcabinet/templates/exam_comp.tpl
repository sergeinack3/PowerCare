{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  {{foreach from=$consult->_types_examen key=_type item=_examens}}
    <tr>
      <th class="category" colspan="5">
        {{tr}}CExamComp.realisation.{{$_type}}{{/tr}}
      </th>
    </tr>
    {{foreach from=$_examens item=_examen}}
      {{assign var=examen_guid value=$_examen->_guid}}
      <tr>
        <td class="narrow">
          <form name="Del-{{$examen_guid}}" action="?m=dPcabinet" method="post">
            <input type="hidden" name="m" value="dPcabinet" />
            <input type="hidden" name="del" value="0" />
            <input type="hidden" name="dosql" value="do_examcomp_aed" />
            {{mb_key object=$_examen}}
            {{mb_key object=$consult}}
            {{mb_field object=$_examen field=fait hidden=1}}
            <input type="hidden" name="callback" value="callbackExamComp" />
            <button class="trash notext not-printable" type="button" onclick="ExamComp.del(this.form)">
              {{tr}}Delete{{/tr}}
            </button>
          </form>
        </td>
        <td class="text" style="width:50%;">{{$_examen}}</td>
        <td class="narrow">{{mb_label class=CExamComp field=labo}}</td>
        <td colspan="2">
          <form name="labo-{{$examen_guid}}" action="?" method="post">
            {{mb_key   object=$_examen}}
            {{mb_class object=$_examen}}
            {{mb_field object=$_examen field="labo" rows="1" form="labo-$examen_guid" aidesaisie="validateOnBlur: 0" onchange="return onSubmitFormAjax(this.form);"}}
          </form>
        </td>
      </tr>
      <tr>
        <td colspan="2"></td>
        <td class="narrow">{{mb_label class=CExamComp field=date_bilan}}</td>
        <td>
          <form name="date_bilan-{{$examen_guid}}" action="?" method="post">
            {{mb_key   object=$_examen}}
            {{mb_class object=$_examen}}
            {{mb_field object=$_examen field=date_bilan form="date_bilan-$examen_guid" register=true onchange="return onSubmitFormAjax(this.form);"}}
          </form>
        </td>
        <td class="narrow">
          {{if !$_examen->fait}}
            <button class="tick not-printable" type="button" onclick="ExamComp.toggle(getForm('Del-{{$_examen->_guid}}'));">{{tr}}Done{{/tr}}</button>
          {{else}}
            <button class="cancel notext not-printable" type="button" onclick="ExamComp.toggle(getForm('Del-{{$_examen->_guid}}'));">{{tr}}Cancel{{/tr}}</button>
          {{/if}}
        </td>
      </tr>
    {{/foreach}}
  {{/foreach}}
</table>