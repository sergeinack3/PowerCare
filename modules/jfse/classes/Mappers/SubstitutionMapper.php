<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Jfse\Mappers;

use Ox\Core\CMbArray;
use Ox\Mediboard\Jfse\Api\Response;
use Ox\Mediboard\Jfse\Domain\Substitute\Session;
use Ox\Mediboard\Jfse\Domain\Substitute\Substitute;

final class SubstitutionMapper extends AbstractMapper
{
    /**
     * @param Response $response
     *
     * @return Substitute[]
     */
    public static function getSubstitutesFromResponse(Response $response): array
    {
        $data = $response->getContent();

        $substitutes = [];
        if ($data = CMbArray::get($response->getContent(), 'lstMedecinRemplacant', null)) {
            foreach ($data as $datum) {
                $substitutes[] = Substitute::hydrate([
                    'id'               => (int)CMbArray::get($datum, 'id'),
                    'user_id'          => (int)CMbArray::get($datum, 'idJfse'),
                    'last_name'        => CMbArray::get($datum, 'nom'),
                    'first_name'       => CMbArray::get($datum, 'prenom'),
                    'invoicing_number' => CMbArray::get($datum, 'noFacturation'),
                    'national_id'  => CMbArray::get($datum, 'noRPPS'),
                    'situation_id'     => (int)CMbArray::get($datum, 'noSituation'),
                    'sessions'         => self::getSessionsFromResponse(CMbArray::get($datum, 'lstCalendriers', []))
                ]);
            }
        }

        return $substitutes;
    }

    /**
     * @param array $data
     *
     * @return Session[]
     */
    private static function getSessionsFromResponse(array $data): array
    {
        $sessions = [];
        foreach ($data as $datum) {
            $sessions[] = Session::hydrate([
                'id' => (int)CMbArray::get($datum, 'id'),
                'begin_date' => self::toDateTimeOrNull($datum, 'debut'),
                'end_date' => self::toDateTimeOrNull($datum, 'fin'),
                'monday' => CMbArray::get($datum, 'lundi'),
                'tuesday' => CMbArray::get($datum, 'mardi'),
                'wednesday' => CMbArray::get($datum, 'mercredi'),
                'thursday' => CMbArray::get($datum, 'jeudi'),
                'friday' => CMbArray::get($datum, 'vendredi'),
                'saturday' => CMbArray::get($datum, 'samedi'),
                'sunday' => CMbArray::get($datum, 'dimanche'),
            ]);
        }

        return $sessions;
    }
}
