<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

namespace Ox\Mediboard\Sample;

use Ox\Core\CSetup;

/**
 * @codeCoverageIgnore
 *
 * Each query should be in a different revision to avoid that failing query lock the setup.
 * For the creation of a table add the indexes directly in the CREATE TABLE statement. It avoid creating the table then
 * directly updating it.
 * When creating a table think of the different uses that will be made of it. Theses use cases will lead you to create
 * indexes. If queries on a table often use the 2 same fiels it is recommanded to create an index containing these two
 * fields.
 *
 * @link https://dev.mysql.com/doc/refman/8.0/en/mysql-indexes.html How MySQL Uses Indexes
 * @link https://dev.mysql.com/doc/refman/8.0/en/multiple-column-indexes.html Multiple-Column Indexes
 */
class CSetupSample extends CSetup
{
    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();

        $this->mod_name = "sample";
        $this->makeRevision("0.0");

        $this->setModuleCategory("autre", "autre");

        $this->makeRevision('0.01');

        /**
         * The table `sample_movie` will be requested with many filters.
         * The indexes must answer to the requests that will be made to reduce the number of lines parsed by MySQL.
         *
         * The filters that will be used are :
         *      - Order by release (desc) :
         *          - This is the default order for the listing of movies.
         *          - The `release` index will allow the optimisation of the order
         *      - Filter by category_id :
         *          - This will be a common filter to display all the movies from one or multiple choosen category.
         *          - The `category_id` index will answer to this filter.
         *       - Search films from keywords :
         *          - This will be a common search. This search have to be done on name and description to return the
         *            best results.
         *          - The FULLTEXT index `seeker` will allow this type of search. The definition in
         *              CSampleMovie::getProps of the prop seekable on the fields name and description (with the object
         *              spec seek = match) will allow the CStoredObject::seek function to use this index.
         *          - The index `name` allow the searchs of movies using only the name and without the match syntaxe.
         *
         * The indexes `director_id` and `creator_id` are created mainly to speed up the loadBackRefs to load all the
         * movies for a choosen director or created by a selected user.
         */
        $this->addQuery(
            "CREATE TABLE `sample_movie` (
                `sample_movie_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `release` DATE NOT NULL,
                `duration` TIME NOT NULL,
                `description` TEXT,
                `category_id` INT (11) UNSIGNED NOT NULL,
                `csa` ENUM ('10','12','16','18'),
                `director_id` INT (11) UNSIGNED NOT NULL,
                `creator_id` INT (11) UNSIGNED,
                `languages` TEXT DEFAULT 'fr',
                INDEX (`release`),
                INDEX (`category_id`),           
                INDEX (`director_id`),
                INDEX (`creator_id`),
                INDEX (`name`),
                FULLTEXT INDEX `seeker` (`name`, `description`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.02');

        /**
         * Nationalities will be requested from :
         *      - Their name in the autocomplete to set a nationlity to a person : the `name` index will be used.
         *      - Their primary key to load all nationalities from a group of person : the primary key will be used.
         */
        $this->addQuery(
            "CREATE TABLE `sample_nationality` (
                `sample_nationality_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `code` VARCHAR (5) NOT NULL,
                `flag` VARCHAR(10),
                INDEX (`name`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.03');

        /**
         * The table sample_person will be used in multiple use cases and request will be :
         *      - Search a person to add it to a movie or to set it as a movie director :
         *          - The search will concerne last_name and first_name.
         *          - The FULLTEXT index `seeker` will be use with a MATCH AGAINST for this request (autocomplete).
         *      - Sort the persons by activity_start date :
         *          - The order will be optimized using the index `activity_start`.
         *      - Get only the directors to set a movie director :
         *          - The returned result will have only persons with is_director = '1'.
         *          - The index `is_director` will be used to reduce the number of rows scanned.
         *
         * Be carefull when defining indexes on short enums (2, 3, 4 possibilities). The index will generally not be
         * used in first intention by MySQL if the number of rows for each possible value is even because it will not
         * restraint the scanned row count enought. In this table the index on `ìs_director` will only be used because
         * among all the persons only a little part will be director so the index will allow the scan of few rows.
         * This index will never be used to search persons that are NOT directors.
         */
        $this->addQuery(
            "CREATE TABLE `sample_person` (
                `sample_person_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `last_name` VARCHAR (255) NOT NULL,
                `first_name` VARCHAR (255) NOT NULL,
                `birthdate` CHAR (10),
                `sex` ENUM ('m','f'),
                `nationality_id` INT (11) UNSIGNED,
                `activity_start` DATE,
                `is_director` ENUM ('0','1') DEFAULT '0',
                INDEX (`last_name`),
                INDEX (`first_name`),
                INDEX (`nationality_id`),
                INDEX (`activity_start`),
                INDEX (`is_director`),
                FULLTEXT INDEX `seeker` (`last_name`, `first_name`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.04');

        /**
         * This table will be used for 2 kind of requests :
         *      - Search all the movies an actor has played in :
         *          - This is a loadBackRef from actor.
         *          - The `actor_id` index will be used.
         *      - Search all the actors from a movie :
         *          - This is a loadBackRef from movie.
         *          - The `movie_id` index will be used.
         *      - Search the main actor from a movie :
         *          - This request should not be used often but the index (`movie_id`, `is_main_actor`) will allow the
         *              scan of only one line to return the result (because only one actor can be the main actor of a
         *              movie).
         *      - Search for all the movies in which an actor is the main actor :
         *           - This request should not be used often but the index (`actor_id`, `is_main_actor`) will allow the
         *              scan of only n lines to return the result with n the number of movies in which the actor is the
         *              main actor.
         */
        $this->addQuery(
            "CREATE TABLE `sample_casting` (
                `sample_casting_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `actor_id` INT (11) UNSIGNED NOT NULL,
                `movie_id` INT (11) UNSIGNED NOT NULL,
                `is_main_actor` ENUM ('0','1') NOT NULL DEFAULT '0',
                INDEX (`movie_id`, `is_main_actor`),
                INDEX (`actor_id`, `is_main_actor`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.05');

        /**
         * Categories will be search by name (index `name`) or with their primary key.
         * An index on the field `active` would not speed up the queries because most of the categories should be
         * active and very few searches on non active categories should be done.
         */
        $this->addQuery(
            "CREATE TABLE `sample_category` (
                `sample_category_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `name` VARCHAR (255) NOT NULL,
                `color` VARCHAR (6),
                `active` ENUM ('0','1') DEFAULT '1',
                INDEX (`name`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.06');

        /**
         * Bookmark have two indexes `user_id` and `movie_id` to speed up the loadBackRefs.
         * Usages will be listing the bookmarked movies for a user (`user_id`) or counting the number of users that
         * have a specific movie bookmarked (`movie_id`).
         */
        $this->addQuery(
            "CREATE TABLE `sample_favorite` (
                `sample_favorite_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `movie_id` INT (11) UNSIGNED NOT NULL,
                INDEX (`user_id`),
                INDEX (`movie_id`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->makeRevision('0.07');

        $this->addQuery("DROP TABLE `sample_favorite`");

        $this->makeRevision('0.08');

        $this->addQuery(
            "CREATE TABLE `sample_bookmark` (
                `sample_bookmark_id` INT (11) UNSIGNED NOT NULL auto_increment PRIMARY KEY,
                `user_id` INT (11) UNSIGNED NOT NULL,
                `movie_id` INT (11) UNSIGNED NOT NULL,
                `datetime` DATETIME NOT NULL,
                INDEX (`user_id`, `datetime`),
                INDEX (`movie_id`)
              )/*! ENGINE=MyISAM */;"
        );

        $this->mod_version = '0.09';
    }
}
