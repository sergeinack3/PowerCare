{{*
 * @package Mediboard\jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
    <tr>
        <th>{{mb_label object=$situation field=invoicing_number}}</th>
        <td>{{mb_value object=$situation field=invoicing_number}}{{mb_value object=$situation field=invoicing_number_key}}</td>
    </tr>
    {{if $situation->substitute_number}}
        <tr>
            <th>{{mb_label object=$situation field=substitute_number}}</th>
            <td>{{mb_value object=$situation field=substitute_number}}</td>
        </tr>
    {{/if}}
    <tr>
        <th>{{mb_label object=$situation field=speciality_code}}</th>
        <td>{{mb_value object=$situation field=speciality_label}}
            ({{mb_value object=$situation field=speciality_code}})
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=convention_label}}</th>
        <td>{{mb_value object=$situation field=convention_label}}
            ({{mb_value object=$situation field=convention_code}})
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=structure_name}}</th>
        <td>{{mb_value object=$situation field=structure_name}}
            ({{mb_value object=$situation field=structure_identifier}})
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=price_zone_label}}</th>
        <td>{{mb_value object=$situation field=price_zone_label}}
            ({{mb_value object=$situation field=price_zone_code}})
        </td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=distance_allowance_label}}</th>
        <td>{{mb_value object=$situation field=distance_allowance_label}}
            ({{mb_value object=$situation field=distance_allowance_code}})
        </td>
    </tr>
    {{if $situation->approval_labels|@count}}
        {{foreach from=$situation->approval_labels key=index item=label}}
            <tr>
                <th>{{mb_label object=$situation field=approval_labels}}{{$index}}</th>
                <td>{{$label}} ({{$situation->approval_codes.$index}})</td>
            </tr>
        {{/foreach}}
    {{/if}}
    <tr>
        <th>{{mb_label object=$situation field=fse_signing_authorisation}}</th>
        <td>{{mb_value object=$situation field=fse_signing_authorisation}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=lot_signing_authorisation}}</th>
        <td>{{mb_value object=$situation field=lot_signing_authorisation}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=practice_mode}}</th>
        <td>{{mb_value object=$situation field=practice_mode}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=practice_status}}</th>
        <td>{{mb_value object=$situation field=practice_status}}</td>
    </tr>
    <tr>
        <th>{{mb_label object=$situation field=activity_sector}}</th>
        <td>{{mb_value object=$situation field=activity_sector}}</td>
    </tr>
</table>
