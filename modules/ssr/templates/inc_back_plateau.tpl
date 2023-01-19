{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=equipement ajax=1}}
{{mb_script module=ssr script=technicien ajax=1}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-back', true));
  Technicien.current_m = '{{$m}}';
</script>

<ul id="tabs-back" class="control_tabs">
  <li id="tab-equipements">
    {{assign var=count_equipements value=$plateau->_ref_equipements|@count}}
    <a href="#equipements" {{if !$count_equipements}}class="empty"{{/if}}>
      {{tr}}CPlateauTechnique-back-equipements{{/tr}}
      <small>({{$count_equipements}})</small>
    </a>
  </li>
  <li id="tab-techniciens">
    {{assign var=count_techniciens value=$plateau->_ref_techniciens|@count}}
    <a href="#techniciens" {{if !$count_techniciens}}class="empty"{{/if}}>
      {{tr}}CPlateauTechnique-back-techniciens{{/tr}}
      <small>({{$count_techniciens}})</small>
    </a>
  </li>
</ul>

<div id="equipements" style="display: none;">
  <a class="button new me-primary" href="#Edit-CEquipement-0" onclick="Equipement.edit('{{$plateau->_id}}', '0')">
    {{tr}}CEquipement-title-create{{/tr}}
  </a>
  <div id="edit-equipements"> 
    {{mb_include module=ssr template=inc_list_equipement}}
  </div>
</div>

<div id="techniciens" style="display: none;">
  <a class="button new me-primary" href="#Edit-CTechnicien-0" onclick="Technicien.edit('{{$plateau->_id}}', '')">
    {{tr}}CTechnicien-title-create{{/tr}}
  </a>
  <div id="edit-techniciens">
    {{mb_include module=ssr template=inc_list_technicien}}
  </div>
</div>