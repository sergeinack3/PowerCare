{{*
 * @package Mediboard\Reservation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  insertField = function(field) {
    var form = getForm("editConfig");
    var elt = window.text_focused;
    if (!elt) {
      elt = form.elements["reservation[text_mail]"];
    }
    var caret = elt.caret();
    var content = "[" + field + "]";
    
    elt.caret(caret.begin, caret.end, content + " ");
    elt.caret(elt.value.length);
  };
  
  Main.add(function() {
    var form = getForm("editConfig");
    form.elements["reservation[subject_mail]"].observe("focus", function(e) { window.text_focused = e.target; });
    form.elements["reservation[text_mail]"].observe("focus", function(e) { window.text_focused = e.target; });
    var tabs = Control.Tabs.create('tabs-configure', true);
    Configuration.edit('reservation', ['CGroups'], $('CConfigEtab'));
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config_resa">{{tr}}mod-dPreservation-config_resa{{/tr}}</a></li>
  <li><a href="#CConfigEtab">{{tr}}CConfigEtab{{/tr}}</a></li>
</ul>

<div id="config_resa" style="display: none;">
  <form name="editConfig" method="post" onsubmit="return onSubmitFormAjax(this);">
    {{mb_configure module=$m}}

    <table class="form">
      <tr>
        <th class="title" colspan="2">Planning</th>
      </tr>
      {{mb_include module=system template=inc_config_enum var=debut_planning values=$hours}}
      {{mb_include module=system template=inc_config_enum var=diff_hour_urgence values="12|24|36|48"}}

      <tr>
        <th></th>
        <td class="text">
          {{foreach from=$fields_email item=_field}}
            <button type="button" onclick="insertField('{{$_field}}')">{{$_field}}</button>
          {{/foreach}}
        </td>
      </tr>

      {{mb_include module=system template=inc_config_str var=subject_mail size=100}}
      {{mb_include module=system template=inc_config_str var=text_mail textarea=1}}
      {{mb_include module=system template=inc_config_bool var=use_color_patient}}
      {{mb_include module=system template=inc_config_bool var=other_display_plage}}
      {{mb_include module=system template=inc_config_bool var=ipp_patient_anonyme}}

      <tr>
        <th class="title" colspan="2">Affichage</th>
      </tr>
      {{mb_include module=system template=inc_config_bool var=display_dossierBloc_button}}
      {{mb_include module=system template=inc_config_bool var=display_facture_button}}

      <tr>
        <td class="button" colspan="2">
          <button class="modify">{{tr}}Save{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>
<div id="CConfigEtab" style="display: none;"></div>