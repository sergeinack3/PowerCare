{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    window.print();
  });
</script>
<div class="header">
  <div class="me-float-left width50">
    <br/>
    <strong>{{if $praticien->isPraticien()}}Dr{{/if}} {{$praticien->_view}}</strong>
    <br />
    <strong>{{$etablissement->_view}}</strong>
      {{if $etablissement->adresse}}
        <br />
          {{mb_label object=$etablissement field=adresse}} :
          {{mb_value object=$etablissement field=adresse}}
          {{mb_value object=$etablissement field=cp}}
          {{mb_value object=$etablissement field=ville}}
        <br />
      {{/if}}
      {{if $etablissement->tel}}
          {{mb_label object=$etablissement field=tel}}: {{mb_value object=$etablissement field=tel}}
        <br />
      {{/if}}
      {{if $etablissement->mail}}
          {{mb_label object=$etablissement field=mail}}: {{$etablissement->mail}}
        <br />
      {{/if}}
  </div>
  <div class="me-float-right width50" style="text-align: end">
    <br />
      {{mb_label object=$patient field=nom_jeune_fille}} :
      {{mb_value object=$patient field=nom_jeune_fille}}
    <br/>
      {{mb_label object=$patient field=prenom}} :
      {{mb_value object=$patient field=prenom}}
    <br />
      {{mb_label object=$patient field=naissance}} :
      {{mb_value object=$patient field=naissance}}
    <br />
      {{mb_label object=$patient field=sexe}} :
      {{mb_value object=$patient field=sexe}}
    <br />
  </div>

<table id="vaccination_print" class="main tbl">
  <tr><th class="title" colspan="5">Liste des injections</th></tr>
  <tr>
    <th>{{tr}}Date{{/tr}}</th>
    <th>{{tr}}CInjection-speciality{{/tr}}</th>
    <th>{{tr}}CInjection-batch{{/tr}}</th>
    <th>{{tr}}CVaccin{{/tr}}</th>
    <th>{{tr}}CInjection-remarques{{/tr}}</th>
  </tr>
  {{foreach from=$injections item=_injection}}
    <tr>
      <td>{{$_injection->injection_date|date_format:$conf.date}}</td>
      {{if in_array($_injection->_id, $vaccinated)}}
        <td>{{$_injection->speciality}}</td>
        <td>{{$_injection->batch}}</td>
      {{else}}
        <td colspan="2">{{tr}}No{{/tr}} {{tr}}CVaccination-verb{{/tr}}</td>
      {{/if}}
      <td>
        <ul>
            {{foreach from=$_injection->_ref_vaccinations item=_vaccination}}
              <li>{{$_vaccination->_ref_vaccine->longname}}</li>
            {{/foreach}}
        </ul>
      </td>
      <td>{{$_injection->remarques}}</td>
    </tr>
  {{foreachelse}}
    <tr><td class="empty" colspan="5">{{tr}}common-msg-No result{{/tr}}</td></tr>
  {{/foreach}}
</table>
