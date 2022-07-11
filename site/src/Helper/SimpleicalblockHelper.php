<?php
/**
 * @version $Id: simpleicalblock.php 
 * @package simpleicalblock
 * @subpackage simpleicalblock Module
 * @copyright Copyright (C) 2022 -2022 A.H.C. Waasdorp, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://www.waasdorpsoekhan.nl
 * @author email contact@waasdorpsoekhan.nl
 * @developer A.H.C. Waasdorp
 *
 *
 * wsacarousel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wsacarousel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with simpleicalblock. If not, see <http://www.gnu.org/licenses/>.
 * 0.0.7
 * 0.2.0 slide delay added.
 */
namespace WaasdorpSoekhan\Module\Simpleicalblock\Site\Helper;
// no direct access
defined('_JEXEC') or die ('Restricted access');


use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Helper for mod_simpleicalblock
 *
 * @since  1.0
 */
class SimpleicalblockHelper
{
    /**
     * Delete existing transient with transientId.
     *
     * @param string  $transientId    Id for the transient
     *
     * @return void 
     */
    static function delete_transient($transientId)
    {
        $transientId = 'SimpleicalBlock'  . $instance['blockid']   ;
        /*
        if ($instance['clear_cache_now']) delete_transient($transientId);
        if(false === ($data = get_transient($transientId))) {
            $data =self::fetch(  $instance,  );
            // do not cache data if fetching failed
            if ($data) {
                set_transient($transientId, $data, $instance['cache_time']*60);
            }
        }
        return $data; */
    }
    /**
     * Retrieves transient data stored with transientId and still valid.
     *
     * @param string  $transientId    Id for the transient ( $transientId = 'SimpleicalBlock'  . $instance['blockid']   ;)
     *
     * @return        $data or false when transient with transientId doesn't exist or is not valid. 
     */
    static function get_transient($transientId)
    {
        if (isset($transientId) && ' ' < $transientId) {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
        ->select($db->quoteName(['a.id', 'a.transient_id', 'a.transient_blob', 'a.transient_expires']))
        ->from($db->quoteName('#__simpleicalblock', 'a'))
        ->where($db->quoteName('a.transient_id') . ' = ' . $transientId);
        $db->setQuery($query);
        try
        {
            return (array) $db->loadObjectList();
        }
        catch (\RuntimeException $e)
        {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
            
            return false;
        }
        } else 
        {
            Factory::getApplication()->enqueueMessage(Text::_('Transientid empty'), 'warning');
            return false;
        }
    }
    /**
     * Creates or updates transient data to store (cache) data that are valid for a period of time.
     *
     * @param string  $transientId    Id for the transient
     *                $transientData  data to store
     *        integer $transientTime  time in seconds that the stored data is valid. 
     *
     * @return boolean true on succes.
     */
    static function set_transient($transientId, $transientData, $transientExpires)
    {
        if ((isset($transientId) && ' ' < $transientId) && isset($transientData)) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
            ->select($db->quoteName(['a.id', 'a.transient_id', 'a.transient_blob', 'a.transient_expires']))
            ->from($db->quoteName('#__simpleicalblock', 'a'))
            ->where($db->quoteName('a.transient_id') . ' = ' . $transientId);
            $db->setQuery($query);
            try
            {
                $query->update($db->quoteName('#__simpleicalblock', 'a'))
                ->set($db->quoteName(['a.transient_blob = ' . $transientData, 'a.transient_expires = ' . $transientExpires]))
                ->where($db->quoteName('a.transient_id') . ' = ' . $transientId);
                $db->setQuery($query);
            }
            catch (\RuntimeException $e)
            {
                $query->insert($db->quoteName('#__simpleicalblock', 'a'), true)
                      ->columns($db->quoteName(['a.transient_id', 'a.transient_blob', 'a.transient_expires']))
                      ->values($transientId, $transientData, $transientExpires);
                $db->setQuery($query);
             }
             try {
                 return $db->execute();
                 
             } catch (\RuntimeException $e) 
             {
                 Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
                 
                 return false;
                 
             }
        } else
        {
            Factory::getApplication()->enqueueMessage(Text::_('Transientid or data is empty'), 'warning');
            return false;
        }
    }
    

}