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
 * Unit tests for some mod LINK lib stuff.
 *
 * @package    mod_link
 * @category   phpunit
 * @copyright  2012 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


/**
 * mod_link tests
 *
 * @package    mod_link
 * @category   phpunit
 * @copyright  2011 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_link_lib_testcase extends basic_testcase {

    /**
     * Prepares things before this test case is initialised
     * @return void
     */
    public static function setUpBeforeClass() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/link/locallib.php');
    }

    /**
     * Tests the link_appears_valid_link function
     * @return void
     */
    public function test_link_appears_valid_link() {
        $this->assertTrue(link_appears_valid_link('http://example'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com'));
        $this->assertTrue(link_appears_valid_link('http://www.exa-mple2.com'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com/~nobody/index.html'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com#hmm'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com/#hmm'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com/žlutý koníček/lala.txt#hmmmm'));
        $this->assertTrue(link_appears_valid_link('http://www.example.com/index.php?xx=yy&zz=aa'));
        $this->assertTrue(link_appears_valid_link('https://user:password@www.example.com/žlutý koníček/lala.txt'));
        $this->assertTrue(link_appears_valid_link('ftp://user:password@www.example.com/žlutý koníček/lala.txt'));

        $this->assertFalse(link_appears_valid_link('http:example.com'));
        $this->assertFalse(link_appears_valid_link('http:/example.com'));
        $this->assertFalse(link_appears_valid_link('http://'));
        $this->assertFalse(link_appears_valid_link('http://www.exa mple.com'));
        $this->assertFalse(link_appears_valid_link('http://www.examplé.com'));
        $this->assertFalse(link_appears_valid_link('http://@www.example.com'));
        $this->assertFalse(link_appears_valid_link('http://user:@www.example.com'));

        $this->assertTrue(link_appears_valid_link('lalala://@:@/'));
    }
}