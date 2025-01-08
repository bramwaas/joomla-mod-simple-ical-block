<?php
/**
 * @version $Id: client-timezone-rest.php
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2024 -2025 simpleicalblock, All rights reserved.
 * @license GNU General Public License version 3 or later
 * @author url: https://www.waasdorpsoekhan.nl
 * @author email contact@waasdorpsoekhan.nl
 * @developer A.H.C. Waasdorp
 *
 *
 * simpleicalblock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * 2.4.0 Created as placeholder for Rest (or Ajax) service. Add inline script for restRoot and load view-script via dependency.
 * 2.5.2 rename SimpleicalblockHelper to SimpleicalHelper 
 * 2.5.3 add title collapse toggle attributes to wrapper div 

 */
// no direct access
defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use WaasdorpSoekhan\Module\Simpleicalblock\Site\Helper\SimpleicalHelper;

$wa->addInlineScript(
    '(window.simpleIcalBlock=window.simpleIcalBlock || {}).restRoot = "' . Uri::root() . 'index.php?option=com_ajax&Itemid=' . (($app->getMenu()->getActive()->id) ?? '') . '"',
    ['position' => 'before', 'name' => 'define.restRoot'], [],['simple-ical-block-view.js']
    );

$attributes = SimpleicalHelper::render_attributes( $params->toArray());

echo '<div id="' . $attributes['anchorId']  .'" data-sib-id="' . $attributes['sibid']
. '" data-sib-st="0-start" class="simple_ical_block ' . $attributes['title_collapse_toggle']. '" >';
echo '<p>' . Text::_('MOD_SIMPLEICALBLOCK_PROCESSING') . '</p>';
echo '</div>';

