{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="tbl">
    <tr>
        <th>{{tr}}Version-Organisation{{/tr}}</th>
        <td>{{$version->getOrganisations()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-CDC{{/tr}}</th>
        <td>{{$version->getCdc()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-CDC date{{/tr}}</th>
        <td>{{$version->getCdcDateString()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-Price|pl{{/tr}}</th>
        <td>{{$version->getPricesDateString()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-Mail{{/tr}}</th>
        <td>{{$version->getMail()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-Server{{/tr}}</th>
        <td>{{$version->getServerVersion()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-Daemon{{/tr}}</th>
        <td>{{$version->getDaemonVersion()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-CCAM server{{/tr}}</th>
        <td>{{$version->getCcamVersion()}}</td>
    </tr>

    <tr>
        <th>{{tr}}Version-Base API version{{/tr}}</th>
        <td>{{$version->getBaseApiVersion()}}</td>
    </tr>
</table>
