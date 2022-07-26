<?php
/**
 * @version $Id: default.php
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2022 -2022 simpleicalblock, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
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
 */
// no direct access
defined('_JEXEC') or die ('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
// use Joomla\CMS\Date\Date;
use WaasdorpSoekhan\Module\Simpleicalblock\Site\Helper\SimpleicalblockHelper;
use WaasdorpSoekhan\Module\Simpleicalblock\Site\IcsParser;

/*
 * @var array allowed tags for summary
 */
static $allowed_tags = ['a','abbr', 'acronym', 'address','area','article', 'aside','audio',
 'b','big','blockquote', 'br','button', 'caption','cite','code','col',
 'details', 'div', 'em', 'fieldset', 'figcaption', 'figure', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6','hr',
 'i', 'img', 'li', 'label', 'legend', 'ol', 'p','q', 'section', 'small', 'span','strike', 'strong', 'u','ul'] ;
$old_timezone = date_default_timezone_get();
$attributes = SimpleicalblockHelper::render_attributes( $params->toArray());
$transientId = 'SimpleiCalBlock' . $attributes['blockid'];
//$helper = new SimpleicalblockHelper;

if(false === ($data = SimpleicalblockHelper::get_transient($transientId)) OR empty($data)) {
    $data = new \DateTime();
    if ($data) {
        SimpleicalblockHelper::set_transient($transientId, $data , 60 * $attributes['transient_time']);
     }
 }
 
?>

<div id="simpleicalblock<?php echo  $attributes['blockid']; ?>" class="simpleicalblock<?php echo $params->get('moduleclass_sfx') ?> "  tabindex="0">
<!-- <?php  print_r($attributes); ?>  -->
<div><?php print_r( $data); ?></div>

</div>
<?php
/**
  * Front-end display of block or module.
  *
  * @param array $attributes Saved attribute/option values from database.
  * from static function display_block($attributes)
  */
    {
        echo '<h3 class="widget-title block-title">' . $attributes['title'] . '</h3>';
        $startwsum = (isset($attributes['startwsum'])) ? $attributes['startwsum'] : false ;
        $dflg = (isset($attributes['dateformat_lg'])) ? $attributes['dateformat_lg'] : 'l jS \of F' ;
        $dflgend = (isset($attributes['dateformat_lgend'])) ? $attributes['dateformat_lgend'] : '' ;
        $dftsum = (isset($attributes['dateformat_tsum'])) ? $attributes['dateformat_tsum'] : 'G:i ' ;
        $dftsend = (isset($attributes['dateformat_tsend'])) ? $attributes['dateformat_tsend'] : '' ;
        $dftstart = (isset($attributes['dateformat_tstart'])) ? $attributes['dateformat_tstart'] : 'G:i' ;
        $dftend = (isset($attributes['dateformat_tend'])) ? $attributes['dateformat_tend'] : ' - G:i ' ;
        $excerptlength = (isset($attributes['excerptlength'])) ? $attributes['excerptlength'] : '' ;
        $attributes['suffix_lg_class'] = strip_tags($attributes['suffix_lg_class'], $allowed_tags);
        $sflgi = strip_tags($attributes['suffix_lgi_class'], $allowed_tags);
        $sflgia = strip_tags($attributes['suffix_lgia_class'], $allowed_tags);
        if (!in_array($attributes['tag_sum'], self::$allowed_tags_sum)) $attributes['tag_sum'] = 'a';
        $attributes['anchorId'] = sanitize_html_class($attributes['anchorId'], $attributes['blockid']);
        $parser = new IcsParser();
        $data = $parser->getData($attributes);
        if (!empty($data) && is_array($data)) {
            date_default_timezone_set(Factory::getApplication()->get('offset'));
            echo '<ul class="list-group' .  $attributes['suffix_lg_class'] . ' simple-ical-widget">';
            $curdate = '';
            foreach($data as $e) {
                $idlist = explode("@", esc_attr($e->uid) );
                $itemid = $attributes['blockid'] . '_' . $idlist[0]; //TODO find correct block id when duplicate
                $evdate = strip_tags(date( $dflg, $e->start), $allowed_tags);
                if (date('yz', $e->start) != date('yz', $e->end)) {
                    $evdate = str_replace(array("</div><div>", "</h4><h4>", "</h5><h5>", "</h6><h6>" ), '', $evdate . strip_tags(date( $dflgend, $e->end - 1) , $allowed_tags));
                }
                $evdtsum = (($e->startisdate === false) ? strip_tags(date( $dftsum, $e->start) . date( $dftsend, $e->end), $allowed_tags) : '');
                echo '<li class="list-group-item' .  $sflgi . '">';
                if (!$startwsum && $curdate != $evdate ) {
                    $curdate =  $evdate;
                    echo '<span class="ical-date">' . ucfirst($evdate) . '</span>' . (('a' == $attributes['tag_sum'] ) ? '<br>': '');
                }
                echo  '<' . $attributes['tag_sum'] . ' class="ical_summary' .  $sflgia . (('a' == $attributes['tag_sum'] ) ? '" data-toggle="collapse" data-bs-toggle="collapse" href="#'.
                $itemid . '" aria-expanded="false" aria-controls="'.
                $itemid . '">' : '">') ;
                if (!$startwsum)	{
                    echo $evdtsum;
                }
                if(!empty($e->summary)) {
                    echo str_replace("\n", '<br>', strip_tags($e->summary,$allowed_tags));
                }
                echo	'</' . $attributes['tag_sum'] . '>' ;
                if ($startwsum ) {
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
                    echo   $e->description ,(strrpos($e->description, '<br>') == (strlen($e->description) - 4)) ? '' : '<br>';
                }
                if ($e->startisdate === false && date('yz', $e->start) === date('yz', $e->end))	{
                    echo '<span class="time">', strip_tags(date( $dftstart, $e->start ), $allowed_tags),
                    '</span><span class="time">', strip_tags(date( $dftend, $e->end ), $allowed_tags), '</span> ' ;
                } else {
                    echo '';
                }
                if(!empty($e->location)) {
                    echo  '<span class="location">', str_replace("\n", '<br>', strip_tags($e->location,$allowed_tags)) , '</span>';
                }
                
                
                echo '</div></li>';
            }
            echo '</ul>';
            date_default_timezone_set($old_timezone);
        }
        
        echo '<br class="clear" />';
    }
    

