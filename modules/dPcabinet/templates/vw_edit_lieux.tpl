{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=lieu ajax=1}}
{{if "doctolib"|module_active && "doctolib staple_authentification client_access_key_id"|gconf}}
  {{mb_script module=doctolib script=calls_to_doctolib ajax=$ajax}}
{{/if}}

<script>
  Main.add(function () {
    Lieu.loadLieux();
  })
</script>

<table class="main">
  {{if $can->edit}}
    <tr>
      <td id="search_lieux">
        <form method="post" name="changePrat" action="?" target="" onsubmit="return Lieu.loadLieux(this);">
          {{me_form_field mb_object=$assoc mb_field="praticien_id"}}
            <select name="praticien_id" style="width: 15em;" onchange="Lieu.loadLieux(this.value)">
              <option value="">&mdash; {{tr}}Choose{{/tr}}</option>
              {{mb_include module=mediusers template=inc_options_mediuser list=$listPraticien selected=$praticien_id}}
            </select>
          {{/me_form_field}}
        </form>
      </td>
    </tr>
  {{/if}}
  <tr>
    <td>
      <button type="button" class="new"
              onclick="Lieu.editLieux(null, {{if $can->edit}}$V(getForm('changePrat').praticien_id){{/if}})">
        {{tr}}CLieuConsult-action-create{{/tr}}
      </button>
    </td>
  </tr>
  <tr>
    <td id="lieux">
    </td>
  </tr>
</table>