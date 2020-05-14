<?php

/**
 * con4gis - the gis-kit
 *
 * @version   php 7
 * @package   con4gis
 * @author    con4gis contributors (see "authors.txt")
 * @license   GNU/LGPL http://opensource.org/licenses/lgpl-3.0.html
 * @copyright Küstenschmiede GmbH Software & Design 2011 - 2018
 * @link      https://www.kuestenschmiede.de
 */

use Contao\Message;
use Contao\MemberGroupModel;

/**
 * Table tl_c4g_auth_fe_groups
 */
$GLOBALS['TL_DCA']['tl_c4g_auth_fe_groups'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'enableVersioning'            => false,
        'notDeletable'                => true,
        'notCopyable'                 => true,
        'onload_callback'			  => array
        (
            array('tl_c4g_auth_fe_groups', 'loadDataset'),
        ),
        'onsubmit_callback'           => array
        (
            array('tl_c4g_auth_fe_groups', 'saveDataset'),
        ),
    ),
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 2,
            'fields'                  => array('id'),
            'panelLayout'             => 'filter;sort,search,limit',
            'headerFields'            => array('bindDn', 'baseDn', 'password', 'filter'),
        ),
        'label' => array
        (
            'fields'                  => array('bindDn', 'baseDn', 'password', 'filter'),
            'showColumns'             => true,
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset();" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['edit'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg',
            )
        )
    ),

    // Select
    'select' => array
    (
        'buttons_callback' => array()
    ),

    // Edit
    'edit' => array
    (
        'buttons_callback' => array()
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array(''),
        'default'                     => '{ldap}, filter, groups'
    ),

    'subpalettes' => array
    (
        ''                                 => ''
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['id'],
            'sorting'                 => true,
            'search'                  => true,
        ),

        'tstamp' => array(
            'default'                 => 0,
        ),

        'filter' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['filter'],
            'sorting'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'default'                 => '',
            'eval'                    => array('decodeEntities' => true),
        ),

        'groups' => array(

            'label'            => &$GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['groups'],
            'exclude'          => true,
            'filter'           => true,
            'inputType'        => 'checkboxWizard',
            'eval'             => ['maxlength' => 360, 'multiple' => true, 'tl_class' => 'long clr'],
            'options_callback' => array('tl_c4g_auth_fe_groups', 'groupsCallback'),

        ),

    ),
);
class tl_c4g_auth_fe_groups extends \Backend
{
    public function loadDataset(Contao\DataContainer $dc)
    {
        $objConfig = Database::getInstance()->prepare("SELECT id FROM tl_c4g_auth_fe_groups")->execute();

        if (\Input::get('key')) return;

        if(!$objConfig->numRows && !\Input::get('act'))
        {
            $this->redirect($this->addToUrl('act=create'));
        }


        if(!\Input::get('id') && !\Input::get('act'))
        {
            $GLOBALS['TL_DCA']['tl_c4g_settings']['config']['notCreatable'] = true;
            $this->redirect($this->addToUrl('act=edit&id='.$objConfig->id));
        }

        \Contao\Message::addInfo($GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['infotext']);

    }

    public function saveDataset(Contao\DataContainer $dc) {

        $groups = $dc->activeRecord->groups;
        if (substr($groups, 0, 2) == "a:") {
            $groups = unserialize($groups);
        }

        $currentTime = time();

        foreach ($groups as $group) {

            $contaoGroup = MemberGroupModel::findOneByName($group);
            if (!$contaoGroup) {
                $this->Database->prepare("INSERT INTO tl_member_group SET tstamp=?, name=?, con4gisAuthMemberGroup=1")->execute($currentTime, $group);
            }

        }

        $currentGroups = $this->Database->prepare("SELECT name FROM tl_member_group WHERE con4gisAuthMemberGroup=1;")->execute();
        $currentGroups = $currentGroups->fetchAllAssoc();

        foreach ($currentGroups as $currentGroup) {
            if (!in_array($currentGroup['name'], $groups)) {
                $this->Database->prepare("DELETE FROM tl_member_group WHERE name=? AND con4gisAuthMemberGroup=1")->execute($currentGroup);
            }
        }

        echo "test";
    }

    public function groupsCallback(Contao\DataContainer $dc) {

        $authSettings = $this->Database->prepare("SELECT * FROM tl_c4g_auth_settings")->execute()->fetchAllAssoc();
        $authSettings = $authSettings[0];

        $bindDn = $authSettings['bindDn'];
        $baseDn = $authSettings['baseDn'];
        $password = $authSettings['password'];
        $filter = $dc->activeRecord->filter;
        $encryption = $authSettings['encryption'];
        $server = $authSettings['server'];
        $port = $authSettings['port'];
        $groups = [];

        if ($encryption == 'ssl') {
            $adServer = "ldaps://" . $server . ":" . $port;
        } else if ($encryption == 'plain') {
            $adServer = "ldap://" . $server . ":" . $port;
        }

        $ldap = ldap_connect($adServer);

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($ldap, $bindDn, $password);

        if ($bind) {
            if ($filter) {

                //ldapsearch -h 192.168.100.10 -p 389 -x -b "cn=Users,dc=ad,dc=coastforge,dc=de" -D "COASTFORGE\Administrator" -W "(&(objectClass=group))"
                $result = ldap_search($ldap, $baseDn, $filter);
                $ldapGroups = ldap_get_entries($ldap, $result);
                array_shift($ldapGroups);

                foreach ($ldapGroups as $ldapGroup) {

                    $group = strstr($ldapGroup['dn'], ',', true);
                    $group = trim(substr($group, strpos($group, '=') + 1));
                    $groups[$group] = $group;
                }

                return $groups;

            } else {
                $result = ldap_search($ldap, $baseDn);
                $ldapGroups = ldap_get_entries($ldap, $result);
                array_shift($ldapGroups);

                foreach ($ldapGroups as $ldapGroup) {

                    $group = strstr($ldapGroup['dn'], ',', true);
                    $group = trim(substr($group, strpos($group, '=') + 1));
                    $groups[$group] = $group;
                }

                return $groups;
            }
        } else {
            Message::addError($GLOBALS['TL_LANG']['tl_c4g_auth_fe_groups']['bindError']);
        }

    }

}