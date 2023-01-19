<?php

/**
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Astreintes\Tests\Fixtures;

use Ox\Core\CMbDT;
use Ox\Core\CMbObject;
use Ox\Core\CModelObjectException;
use Ox\Mediboard\Astreintes\CCategorieAstreinte;
use Ox\Mediboard\Astreintes\CPlageAstreinte;
use Ox\Mediboard\Etablissement\CGroups;
use Ox\Mediboard\Mediusers\CMediusers;
use Ox\Tests\Fixtures\Fixtures;
use Ox\Tests\Fixtures\FixturesException;

/**
 * Fixtures for Astreintes Module
 */
class AstreintesFixtures extends Fixtures
{
    public const TAG_CAT_LOREM   = "CAT_LOREM";
    public const TAG_COLOR_LOREM = "fedcba";

    public const TAG_CAT_IPSUM   = "CAT_IPSUM";
    public const TAG_COLOR_IPSUM = "abdcef";

    public const TAG_PONCT_ADMIN = "AST_PONCT_ADMIN";
    public const TAG_USER_ADMIN  = "USER_ADMIN";

    public const TAG_PONCT_INFO = "AST_PONCT_INFO";
    public const TAG_USER_INFO  = "USER_INFO";
    public const TAG_COLOR_INFO = "123456";

    public const TAG_PONCT_MED = "AST_PONCT_MED";
    public const TAG_USER_MED  = "USER_MED";
    public const TAG_COLOR_MED = "111111";

    public const TAG_REGU_PARAMED = "AST_REGU_PARAMED";
    public const TAG_USER_PARAMED = "USER_PARAMED";

    public const TAG_REGU_TECH = "AST_REGU_TECH";
    public const TAG_USER_TECH = "USER_TECH";

    public const TAG_GROUP = "TAG_ETAB_ASTREINTE";

    /** @var CGroups */
    public $group;

    /** @var array */
    public $list_users;

    /** @var CCategorieAstreinte */
    public $cat_lorem;

    /** @var CCategorieAstreinte */
    public $cat_ipsum;

    /**
     * @throws FixturesException
     * @throws CModelObjectException
     */
    public function load(): void
    {
        $this->generateUsersAstreinte();
        $this->generateCategoriesAstreinte(self::TAG_CAT_LOREM, self::TAG_COLOR_LOREM);
        $this->generateCategoriesAstreinte(self::TAG_CAT_IPSUM, self::TAG_COLOR_IPSUM);
        $this->generateAstreinteAdmin();
        $this->generateAstreinteInfo();
        $this->generateAstreinteMed();
        $this->generateAstreinteParamed();
        $this->generateAstreinteTech();
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    public function generateCategoriesAstreinte(string $type, string $color): CCategorieAstreinte
    {
        /** @var CCategorieAstreinte $cat_ast */
        $cat_ast           = CMbObject::getSampleObject(CCategorieAstreinte::class);
        $cat_ast->color    = $color;
        $cat_ast->name     = $type;
        $cat_ast->group_id = CGroups::getCurrent()->_id;
        $this->store($cat_ast, $type);

        return $type === self::TAG_CAT_LOREM
            ? $this->cat_lorem = $cat_ast
            : $this->cat_ipsum = $cat_ast;
    }

    /**
     * @param string $tag
     *
     * @return CMediusers
     * @throws FixturesException
     */
    protected function generateUsersAstreinte(): void
    {
        $tags  = [
            self::TAG_USER_ADMIN,
            self::TAG_USER_INFO,
            self::TAG_USER_MED,
            self::TAG_USER_PARAMED,
            self::TAG_USER_TECH,
        ];
        $users = $this->getUsers(5);
        foreach ($users as $index => $_user) {
            $_user->_user_astreinte = "1234";
            $this->store($_user, $tags[$index]);
            $this->list_users[$tags[$index]] = $_user->_id;
        }
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    protected function generateAstreinteAdmin(): void
    {
        /** @var CPlageAstreinte $ast */
        $ast                   = CMbObject::getSampleObject(CPlageAstreinte::class);
        $date                  = CMbDT::date("first day of january");
        $ast->start            = CMbDT::dateTime("00:00:00", $date);
        $ast->end              = CMbDT::dateTime("+1 week 23:59:59", $date);
        $ast->user_id          = $this->list_users[self::TAG_USER_ADMIN];
        $ast->group_id         = CGroups::getCurrent()->_id;
        $ast->type             = CPlageAstreinte::TYPES_ASTREINTES[0];
        $ast->choose_astreinte = CPlageAstreinte::CHOICES_ASTREINTES[0];
        $ast->phone_astreinte  = "1234";
        $ast->categorie        = $this->cat_lorem->_id;
        $this->store($ast, self::TAG_PONCT_ADMIN);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    protected function generateAstreinteInfo(): void
    {
        /** @var CPlageAstreinte $ast */
        $ast                   = CMbObject::getSampleObject(CPlageAstreinte::class);
        $date                  = CMbDT::date("first day of january");
        $ast->start            = CMbDT::dateTime("first day of january 00:00:00", $date);
        $ast->end              = CMbDT::dateTime("last day of january 23:59:59", $date);
        $ast->user_id          = $this->list_users[self::TAG_USER_INFO];
        $ast->group_id         = CGroups::getCurrent()->_id;
        $ast->type             = CPlageAstreinte::TYPES_ASTREINTES[1];
        $ast->choose_astreinte = CPlageAstreinte::CHOICES_ASTREINTES[0];
        $ast->phone_astreinte  = "1234";
        $ast->color            = self::TAG_COLOR_INFO;
        $ast->categorie        = $this->cat_lorem->_id;

        $this->store($ast, self::TAG_PONCT_INFO);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    protected function generateAstreinteMed(): void
    {
        /** @var CPlageAstreinte $ast */
        $ast                   = CMbObject::getSampleObject(CPlageAstreinte::class);
        $date                  = CMbDT::date("first day of january");
        $ast->start            = CMbDT::dateTime("first day of january 00:00:00", $date);
        $ast->end              = CMbDT::dateTime("+1 year 23:59:59", $date);
        $ast->user_id          = $this->list_users[self::TAG_USER_MED];
        $ast->group_id         = CGroups::getCurrent()->_id;
        $ast->type             = CPlageAstreinte::TYPES_ASTREINTES[2];
        $ast->choose_astreinte = CPlageAstreinte::CHOICES_ASTREINTES[0];
        $ast->phone_astreinte  = "1234";
        $ast->color            = self::TAG_COLOR_MED;
        $ast->categorie        = $this->cat_ipsum->_id;
        $this->store($ast, self::TAG_PONCT_MED);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    protected function generateAstreinteParamed(): void
    {
        /** @var CPlageAstreinte $ast */
        $ast                   = CMbObject::getSampleObject(CPlageAstreinte::class);
        $date                  = CMbDT::date("first day of january");
        $ast->start            = CMbDT::dateTime("today 00:00:00", $date);
        $ast->end              = CMbDT::dateTime("+2 weeks 23:59:59", $date);
        $ast->user_id          = $this->list_users[self::TAG_USER_PARAMED];
        $ast->group_id         = CGroups::getCurrent()->_id;
        $ast->type             = CPlageAstreinte::TYPES_ASTREINTES[3];
        $ast->choose_astreinte = CPlageAstreinte::CHOICES_ASTREINTES[0];
        $ast->phone_astreinte  = "1234";
        $ast->categorie        = $this->cat_ipsum->_id;
        $this->store($ast, self::TAG_REGU_PARAMED);
    }

    /**
     * @throws CModelObjectException
     * @throws FixturesException
     */
    protected function generateAstreinteTech(): void
    {
        /** @var CPlageAstreinte $ast */
        $ast                   = CMbObject::getSampleObject(CPlageAstreinte::class);
        $date                  = CMbDT::date("first day of february");
        $ast->start            = CMbDT::dateTime("first day of february 00:00:00", $date);
        $ast->end              = CMbDT::dateTime("+1 week 23:59:59", $date);
        $ast->user_id          = $this->list_users[self::TAG_USER_TECH];
        $ast->group_id         = CGroups::getCurrent()->_id;
        $ast->type             = CPlageAstreinte::TYPES_ASTREINTES[4];
        $ast->choose_astreinte = CPlageAstreinte::CHOICES_ASTREINTES[0];
        $ast->phone_astreinte  = "1234";
        $this->store($ast, self::TAG_REGU_TECH);
    }
}
