<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class LDAPAuth implements Auth
{

    const LDAP_SERVERS = 'host.domain.com host.domain.com';
    const LDAP_PORT = '389';
    const LDAP_BASEDN = 'dc=domain,dc=com';

    public static function getUser()
    {
        if ((isset($_SERVER['PHP_AUTH_USER'])) && (!empty($_SERVER['PHP_AUTH_USER']))) {
            return $_SERVER['PHP_AUTH_USER'];
        }
        return '-';
    }

    public function getTitle()
    {
        return 'LDAP Groups:';
    }

    public function checkAuth($deployment)
    {
        $ldapGroup = RevDeploy::getDeploymentAuthGroup($deployment);
        $supermen = SUPERMEN;
        if ((!isset($_SERVER['PHP_AUTH_USER'])) || (empty($_SERVER['PHP_AUTH_USER']))) {
            return false;
        }
        elseif ((!isset($_SERVER['PHP_AUTH_PW'])) || (empty($_SERVER['PHP_AUTH_PW']))) {
            return false;
        }
        $user = $_SERVER['PHP_AUTH_USER'];
        $pass = $_SERVER['PHP_AUTH_PW'];
        $return = false;
        if (preg_match('/,/', $ldapGroup)) {
            $return = $this->inGroup($user, $pass, $ldapGroup);
            if ($return === true) {
                return true;
            }
            // Check and see if they are a super user
            $groups = preg_split('/\s?,\s?/', $ldapGroup);
            if (!in_array($supermen, $groups)) {
                $return = $this->inGroup($user, $pass, $supermen);
                if ($return === true) {
                    return true;
                }
                return false;
            }
            return false;
        } else {
            $return = $this->inGroup($user, $pass, $ldapGroup);
            if ($return === true) {
                return true;
            }
            // Check and see if they are a super user
            if ($ldapGroup != $supermen) {
                $return = $this->inGroup($user, $pass, $supermen);
                if ($return === true) {
                    return true;
                }
                return false;
            }
            return false;
        }
    }

    /**
     * inGroup
     *
     * @param mixed $user   username used for authentication
     * @param mixed $pass   password used for authentication
     * @param mixed $groups group(s) we are checking to see if the user belongs too
     *
     * @access private
     * @return bool
     */
    private function inGroup($user, $pass, $groups)
    {
        $searchGroups = array();
        if (preg_match('/,/', $groups)) {
            $tmpArray = preg_split('/\s?,\s?/', $groups);
            $searchGroups = $tmpArray;
        } else {
            array_push($searchGroups, $groups);
        }
        $ldap = ldap_connect(self::LDAP_SERVERS);
        $dn = "uid=".$user.",ou=people," . self::LDAP_BASEDN;
        if ($bind = ldap_bind($ldap, $dn, $pass)) {
            foreach ($searchGroups as $group) {
                $filter = "(cn=".$group.")";
                $attrs = array("memberuid");
                $result = ldap_search($ldap, self::LDAP_BASEDN, $filter, $attrs);
                $entries = ldap_get_entries($ldap, $result);
                if ($entries['count'] > 0) {
                    if (in_array($user, $entries[0]['memberuid'])) {
                        ldap_unbind($ldap);
                        return true;
                    }
                }
            }
        }
        ldap_unbind($ldap);
        return false;
    }

}

