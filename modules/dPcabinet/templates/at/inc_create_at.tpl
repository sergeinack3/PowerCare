{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "cerfa General use_cerfa"|gconf && "cerfa"|module_active}}
  {{mb_script module=cerfa script=Cerfa register=true}}
{{/if}}
{{mb_script module=cabinet script=accident_travail ajax=true}}

{{assign var=uid value='-'|uniqid}}

<script>
  Main.add(function() {
    var form = getForm('createAT{{$uid}}');
    AccidentTravail.initialize(form, '{{$uid}}');
  });
</script>

<form name="createAT{{$uid}}" method="post" action="?"
      onsubmit="return onSubmitFormAjax(this, {onComplete: function() {
          Control.Modal.close();
        }});">
  {{mb_class object=$accident_travail}}
  {{mb_key object=$accident_travail}}
  {{mb_field object=$accident_travail field=object_id    hidden=true}}
  {{mb_field object=$accident_travail field=object_class hidden=true}}
  <input type="hidden" name="datetime_at_mp" value="now" />

  <div id="at_context{{$uid}}" class="at_view">
    {{mb_include module=cabinet template=at/inc_context at=$accident_travail}}
  </div>
  <div id="at_duration{{$uid}}" class="at_view" style="display: none;">
    {{mb_include module=cabinet template=at/inc_duration at=$accident_travail}}
  </div>
  <div id="at_patient_situation{{$uid}}" class="at_view" style="display: none;">
    {{mb_include module=cabinet template=at/inc_patient_situation at=$accident_travail}}
  </div>
  <div id="at_sorties{{$uid}}" class="at_view" style="display: none;">
    {{mb_include module=cabinet template=at/inc_sorties_autorisees at=$accident_travail}}
  </div>
  <div id="at_summary{{$uid}}" class="at_view" style="display: none;">
    {{mb_include module=cabinet template=at/inc_summary at=$accident_travail}}
  </div>
</form>
