<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Core\Auth\Voters;

use Ox\Core\Kernel\Routing\RequestHelperTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * This Voter enables us to use public routes by voting "Yes" if the request is considered as public on Affirmative
 * Strategy.
 */
class PublicRequestVoter extends Voter
{
    use RequestHelperTrait;

    private const SUPPORTED_ATTRIBUTE = 'ROLE_API_USER';

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, $subject)
    {
        return $this->supportsAttribute($attribute) && $subject instanceof Request;
    }

    /**
     * @inheritDoc
     */
    public function supportsType(string $subjectType): bool
    {
        return parent::supportsType($subjectType)
            && (
                $subjectType === Request::class
                || is_subclass_of($subjectType, Request::class)
            );
    }

    public function supportsAttribute(string $attribute): bool
    {
        return $attribute === self::SUPPORTED_ATTRIBUTE;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token)
    {
        return $this->isRequestPublic($subject);
    }
}
