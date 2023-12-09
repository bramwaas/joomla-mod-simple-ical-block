<?php
/**
 * @version $Id: default.php
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2022 -2024 simpleicalblock, All rights reserved.
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
 * simpleicalblock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 0.0.0 2022-07-10 first adjustments for J4 convert parameters to array $attributes.
 * 0.0.1 2022-07-25 included display_block function from WP Plugin SimpleicalBlock
 *   replaced $instamce by $attributes, wp_kses ($text, 'post')  by strip_tags  ($text, $allowed_tags)
 *   changed wp_date in date (maybe date_default_timezone_set(<local timezone> is needed but that is already in the code if not we can remove it);
 *   replaced wp get_option('timezone_string') by Factory::getApplication()->get('offset') or (deprecated) Factory::Getconfig()->offset 
 *   replaced wp sanitize_html_class by copy in SimpleicalblockHelper
 *   removed wp esc_attr from sanitizing $e->uid
 *   removed checks isset on attributes because that is already done before.
 *   replaced date( with Date()->format where translation is necessary.
 * 2.0.1 back to static functions getData() and fetch() only instantiate object in fetch when parsing must be done (like it always was in WP)  
 * 2.1.0 add calendar class to list-group-item
 *   add htmlspecialchars() to summary, description and location when not 'allowhtml', replacing similar code from IcsParser
 * 2.1.3 use select 'layout' in stead of 'start with summary' to create more lay-out options.
 * 2.1.4 add closing HTML output after eventlist or when no events are available.    
 */
// no direct access
defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date as Jdate;
use WaasdorpSoekhan\Module\Simpleicalblock\Site\Helper\SimpleicalblockHelper;
use WaasdorpSoekhan\Module\Simpleicalblock\Site\IcsParser;

/*
 * @var array allowed tags for text-output
 */
static $allowed_tags = ['a','abbr', 'acronym', 'address','area','article', 'aside','audio',
 'b','big','blockquote', 'br','button', 'caption','cite','code','col',
 'details', 'div', 'em', 'fieldset', 'figcaption', 'figure', 'footer', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6','hr',
 'i', 'img', 'li', 'label', 'legend', 'ol', 'p','q', 'section', 'small', 'span','strike', 'strong', 'u','ul'] ;
$attributes = SimpleicalblockHelper::render_attributes( $params->toArray());
//$helper = new SimpleicalblockHelper;

echo '<div id="' . $attributes['anchorId']  .'" data-sib-id="' . $attributes['blockid'] . '" ' . '" class="simple_ical_block" >';

/**
  * Front-end display of block or module.
  *
  * @param array $attributes Saved attribute/option values from database.
  * from static function display_block($attributes)
  */
    {
        $old_timezone = date_default_timezone_get();
        $tzid_ui = Factory::getApplication()->get('offset');
        $tz_ui = new \DateTimeZone($tzid_ui);
        $layout = (isset($attributes['sib_layout'])) ? intval($attributes['sib_layout']) : 3;
        $dflg = $attributes['dateformat_lg'];
        $dflgend =$attributes['dateformat_lgend'];
        $dftsum =$attributes['dateformat_tsum'];
        $dftsend = $attributes['dateformat_tsend'];
        $dftstart = $attributes['dateformat_tstart'];
        $dftend = $attributes['dateformat_tend'];
        $excerptlength = $attributes['excerptlength'];
        $sflgi = $attributes['suffix_lgi_class'];
        $sflgia = $attributes['suffix_lgia_class'];
        $data = IcsParser::getData($attributes);
        if (!empty($data) && is_array($data)) {
            date_default_timezone_set($tzid_ui);
            echo '<ul class="list-group' .  $attributes['suffix_lg_class'] . ' simple-ical-widget">';
            $curdate = '';
            foreach($data as $e) {
                $idlist = explode("@", $e->uid );
                $itemid = 'b' . $attributes['blockid'] . '_' . $idlist[0]; 
                $e_dtstart = new Jdate ($e->start);
                $e_dtstart->setTimezone($tz_ui);
                $e_dtend = new Jdate ($e->end);
                $e_dtend->setTimezone($tz_ui);
                $e_dtend_1 = new Jdate ($e->end -1);
                $e_dtend_1->setTimezone($tz_ui);
                $cal_class = ((!empty($e->cal_class)) ? ' ' . SimpleicalblockHelper::sanitize_html_class($e->cal_class): '');
                $evdate = strip_tags($e_dtstart->format($dflg, true, true) , $allowed_tags);
                if ( !$attributes['allowhtml']) {
                    if (!empty($e->summary)) $e->summary = htmlspecialchars($e->summary);
                    if (!empty($e->description)) $e->description = htmlspecialchars($e->description);
                    if (!empty($e->location)) $e->location = htmlspecialchars($e->location);
                }
                if (date('yz', $e->start) != date('yz', $e->end)) {
                    $evdate = str_replace(array("</div><div>", "</h4><h4>", "</h5><h5>", "</h6><h6>" ), '', $evdate . strip_tags( $e_dtend_1->format($dflgend, true, true) , $allowed_tags));
                }
                $evdtsum = (($e->startisdate === false) ? strip_tags($e_dtstart->format($dftsum, true, true) . $e_dtend->format($dftsend, true, true), $allowed_tags) : '');
                if ($layout < 2 && $curdate != $evdate) {
                    if  ($curdate != '') { echo '</ul></li>';}
                    echo '<li class="list-group-item' .  $sflgi . ' head">' .
                        '<span class="ical-date">' . ucfirst($evdate) . '</span><ul class="list-group' .  $attributes['suffix_lg_class'] . '">';
                }
                echo '<li class="list-group-item' .  $sflgi . $cal_class . '">';
                if ($layout == 3 && $curdate != $evdate) {
                    echo '<span class="ical-date">' . ucfirst($evdate) . '</span>' . (('a' == $attributes['tag_sum'] ) ? '<br>': '');
                }
                echo  '<' . $attributes['tag_sum'] . ' class="ical_summary' .  $sflgia . (('a' == $attributes['tag_sum'] ) ? '" data-toggle="collapse" data-bs-toggle="collapse" href="#'.
                $itemid . '" aria-expanded="false" aria-controls="'.
                $itemid . '">' : '">') ;
                if ($layout != 2)	{
                    echo $evdtsum;
                }
                if(!empty($e->summary)) {
                    echo str_replace("\n", '<br>', strip_tags($e->summary,$allowed_tags));
                }
                echo	'</' . $attributes['tag_sum'] . '>' ;
                if ($layout == 2)	{
                    echo '<span>', $evdate, $evdtsum, '</span>';
                }
                echo '<div class="ical_details' .  $sflgia . (('a' == $attributes['tag_sum'] ) ? ' collapse' : '') . '" id="',  $itemid, '">';
                if(!empty($e->description) && trim($e->description) > '' && $excerptlength !== 0) {
                    if ($excerptlength !== '' && strlen($e->description) > $excerptlength) {$e->description = substr($e->description, 0, $excerptlength + 1);
                    if (rtrim($e->description) !== $e->description) {$e->description = substr($e->description, 0, $excerptlength);}
                    else {if (strrpos($e->description, ' ', max(0,$excerptlength - 10))!== false OR strrpos($e->description, "\n", max(0,$excerptlength - 10))!== false )
                    {$e->description = substr($e->description, 0, max(strrpos($e->description, "\n", max(0,$excerptlength - 10)),strrpos($e->description, ' ', max(0,$excerptlength - 10))));
                    } else
                    {$e->description = substr($e->description, 0, $excerptlength);}
                    }
                    }
                    $e->description = str_replace("\n", '<br>', strip_tags($e->description,$allowed_tags) );
                    echo   $e->description ,(strrpos($e->description, '<br>') === (strlen($e->description) - 4)) ? '' : '<br>';
                }
                if ($e->startisdate === false && date('yz', $e->start) === date('yz', $e->end))	{
                    echo '<span class="time">', strip_tags($e_dtstart->format($dftstart, true, true), $allowed_tags),
                    '</span><span class="time">', strip_tags($e_dtend->format($dftend, true, true) , $allowed_tags), '</span> ' ;
                } else {
                    echo '';
                }
                if(!empty($e->location)) {
                    echo  '<span class="location">', str_replace("\n", '<br>', strip_tags($e->location,$allowed_tags)) , '</span>';
                }
                echo '</div></li>';
                $curdate =  $evdate;
            }
            if ($layout < 2 ) {
                echo '</ul></li>';
            }
            echo '</ul>';
            date_default_timezone_set($old_timezone);
            echo strip_tags($attributes['after_events'],$allowed_tags);
        }
        else {
            echo strip_tags($attributes['no_events'],$allowed_tags);
            
        }
        echo '<br class="clear" />';
    }
echo '</div>';

