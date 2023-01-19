{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main form" style="height: 100%;">
  <tr>
  {{foreach name=classes from=$classes item=_class}}
    <th style="width: 25%; vertical-align: middle; text-align: center; height: 10%; {{if !$smarty.foreach.classes.last}}  border-right: dashed #F35044 2px;{{/if}}">
      <h2>{{tr}}{{$_class}}{{/tr}}</h2>
    </th>
  {{/foreach}}
  </tr>

  <tr>
    {{foreach name=classes from=$classes item=_class}}
      {{assign var=_value value="admin CRGPDConsent $_class enable"|gconf}}

      <td class="text compact" style="padding: 0 10px; {{if !$smarty.foreach.classes.last}}  border-right: dashed #F35044 2px;{{/if}}">
        <div style="background-color: rgba(70,130,180,0.11); border-radius: 3px; padding: 5px; font-size: 1.2em; height: 100%;">
          <h3>{{tr}}config-admin-CRGPDConsent-{{$_class}}-enable{{/tr}}</h3>
          <p>
            {{tr}}config-admin-CRGPDConsent-{{$_class}}-enable.{{$_value}}{{/tr}}
          </p>
        </div>
      </td>
    {{/foreach}}
  </tr>

  <tr>
    {{foreach name=classes from=$classes item=_class}}
      <td class="text compact" style="padding: 0 10px; {{if !$smarty.foreach.classes.last}}  border-right: dashed #F35044 2px;{{/if}}">
        <div style="background-color: rgba(70,130,180,0.11); border-radius: 3px; padding: 5px; font-size: 1.2em; height: 100%;">
          <h3>{{tr}}common-Description{{/tr}}</h3>
          <p>
            {{mb_include module=admin template=inc_vw_rgpd_document manager=$manager object_class=$_class stylized=false}}
          </p>
        </div>
      </td>
    {{/foreach}}
  </tr>
</table>