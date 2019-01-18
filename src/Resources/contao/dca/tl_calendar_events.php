<?php

/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2013 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  Leo Feyer 2005-2013
 * @author     Leo Feyer <https://contao.org>
 * @package    Calendar
 * @license    LGPL
 * @filesource
 */

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['startDate']['default'] = '';


$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['name'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['name'],
    'exclude'                 => true,
    'search'                  => true,
    'sorting'                 => true,
    'flag'                    => 1,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => array('type' => 'string', 'length' => 255, 'default' => '')
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['details'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['details'],
    'exclude'                 => true,
    'search'                  => true,
    'sorting'                 => true,
    'flag'                    => 1,
    'inputType'               => 'textarea',
    'eval'                    => array('mandatory'=>true),
    'sql'                     => "text NULL"
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['email'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['email'],
    'exclude'                 => true,
    'search'                  => true,
    'sorting'                 => true,
    'flag'                    => 1,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => array('type' => 'string', 'length' => 255, 'default' => '')
);

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['phone'] = array(
    'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['phone'],
    'exclude'                 => true,
    'search'                  => true,
    'sorting'                 => true,
    'flag'                    => 1,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255),
    'sql'                     => array('type' => 'string', 'length' => 255, 'default' => '')
);