<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provides code to be executed during the module uninstallation
 *
 * @see uninstall_plugin()
 *
 * @package    mod_peer
 * @copyright  2015 Your Name <your@email.adress>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Custom uninstallation procedure
 */
// require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
// require_once(dirname(__FILE__).'/lib.php');
function xmldb_peer_uninstall() {
	global $DB;
	$uninstall_tables="DROP TABLE IF EXISTS peer_user_requests";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS peer_reviews";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS peer_user_standarddocs";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS rfr0";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS rfr1";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS rfr2";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS rfr3";
    $DB->execute($uninstall_tables);
    $uninstall_tables="DROP TABLE IF EXISTS rfr4";
    $DB->execute($uninstall_tables);
    return true;
}
