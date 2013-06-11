<?php
/*
 * This file is part of MySmarkEDU.
 *
 * MySmarkEDU is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * MySmarkEDU is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with MySmarkEDU. If not, see <http://www.gnu.org/licenses/>.
 */

$capabilities = $block_smartblock_capabilities = array( 
    'block/smartblock:supervise' => array(
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'guest'          => CAP_PREVENT,
            'student'        => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'coursecreator'  => CAP_PREVENT,
            'admin'          => CAP_ALLOW
            ),
        'archetypes' => array(
            'user'           => CAP_PREVENT,
            'guest'          => CAP_PREVENT,
            'student'        => CAP_PREVENT,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'        => CAP_ALLOW
        )
    ),
    'block/smartblock:vote' => array(
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'legacy' => array(
            'user'           => CAP_PREVENT,
            'guest'          => CAP_PREVENT,
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'admin'          => CAP_PREVENT
            ),
        'archetypes' => array(
            'user'           => CAP_PREVENT,
            'guest'          => CAP_PREVENT,
            'student'        => CAP_ALLOW,
            'teacher'        => CAP_PREVENT,
            'editingteacher' => CAP_PREVENT,
            'manager'        => CAP_PREVENT
        )
    ),
    'block/smartblock:edit' => array(
        'captype'      => 'write',
        'contextlevel' => CONTEXT_COURSE,
            'legacy' => array(
            'guest'          => CAP_PREVENT,
            'student'        => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'coursecreator'  => CAP_ALLOW,
            'admin'          => CAP_ALLOW
            ),
        'archetypes' => array(
            'user'           => CAP_PREVENT,
            'guest'          => CAP_PREVENT,
            'student'        => CAP_PREVENT,
            'teacher'        => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager'        => CAP_ALLOW
        )
    )
);
?>
