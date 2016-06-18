<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagDiff
{

    private static $_results = array();
    private static $_output;

    /**
     * getOutput - get error from diff
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getOutput()
    {
        return self::$_output;
    }

    /**
     * getResults - get results of running diff
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getResults()
    {
        return self::$_results;
    }

    /**
     * diff - diff the two revisions we specify
     * 
     * @param mixed $from from revision
     * @param mixed $to   to revision
     *
     * @static
     * @access public
     * @return void
     */
    public static function diff($from, $to)
    {
        $results = array();
        $options = array( 'ignoreWhitespace' => true );
        foreach ($from as $key => $value) {
            $a = explode("\n", $value);
            $b = explode("\n", $to[$key]);
            $results[$key] = new Diff($a, $b, $options);
        }
        return $results;
    }

    /**
     * buildDiffRevisions - build the revisions of the deployment and store them for diffing
     * 
     * @param mixed $deployment    deployment we are building revisions for
     * @param mixed $fromrev       from revision we are building
     * @param mixed $torev         to revision we are building
     * @param mixed $shardposition shard position we may be using
     *
     * @static
     * @access public
     * @return void
     */
    public static function buildDiffRevisions($deployment, $fromrev, $torev, $shardposition)
    {
        self::$_results = array();
        self::$_output = "";
        NagCreate::resetLocalCache();
        /* Get Current Nagios Configs */
        NagCreate::buildDeployment($deployment, $fromrev, true, true, $shardposition);
        $fromconfs = NagCreate::returnDeploymentConfigs($deployment);
        $fromconfs['nrpe.cfg'] = self::_getNRPECfg($deployment, $fromrev);
        $fromconfs['supplemental-nrpe.cfg'] = self::_getSupNRPECfg($deployment, $fromrev);
        /* Get Future Revision Configs */
        NagCreate::buildDeployment($deployment, $torev, true, true, $shardposition);
        $toconfs = NagCreate::returnDeploymentConfigs($deployment);
        $toconfs['nrpe.cfg'] = self::_getNRPECfg($deployment, $torev);
        $toconfs['supplemental-nrpe.cfg'] = self::_getSupNRPECfg($deployment, $torev);
        RevDeploy::deleteConsumerDeploymentLock($deployment, false, 'diff');
        /* Get Plugin Information */
        $fnagplugins = RevDeploy::getDeploymentNagiosPlugins($deployment, $fromrev);
        $tnagplugins = RevDeploy::getDeploymentNagiosPlugins($deployment, $torev);
        $nagplugins = array_merge($fnagplugins, $tnagplugins);
        $fromnagiosplugins = self::_getPlugins('nagios', $deployment, $fromrev, $nagplugins);
        $tonagiosplugins = self::_getPlugins('nagios', $deployment, $torev, $nagplugins);
        $fplugins = RevDeploy::getDeploymentNRPEPlugins($deployment, $fromrev);
        $tplugins = RevDeploy::getDeploymentNRPEPlugins($deployment, $torev);
        $plugins = array_merge($fplugins, $tplugins);
        $fromnrpecoreplugins = self::_getPlugins('nrpe-core', $deployment, $fromrev, $plugins);
        $tonrpecoreplugins = self::_getPlugins('nrpe-core', $deployment, $torev, $plugins);
        $fsplugins = RevDeploy::getDeploymentSupNRPEPlugins($deployment, $fromrev);
        $tsplugins = RevDeploy::getDeploymentSupNRPEPlugins($deployment, $torev);
        $supplugins = array_merge($fsplugins, $tsplugins);
        $fromsupnrpeplugins = self::_getPlugins('nrpe-sup', $deployment, $fromrev, $supplugins);
        $tosupnrpeplugins = self::_getPlugins('nrpe-sup', $deployment, $torev, $supplugins);
        /* Ok lets diff the results and send it out */
        $results = array();
        $results['nagiosconfs']['from'] = $fromconfs;
        $results['nagiosconfs']['to'] = $toconfs;
        $results['plugins']['nagios']['from'] = $fromnagiosplugins;
        $results['plugins']['nagios']['to'] = $tonagiosplugins;
        $results['plugins']['nrpe']['core']['from'] = $fromnrpecoreplugins;
        $results['plugins']['nrpe']['core']['to'] = $tonrpecoreplugins;
        $results['plugins']['nrpe']['sup']['from'] = $fromsupnrpeplugins;
        $results['plugins']['nrpe']['sup']['to'] = $tosupnrpeplugins;
        self::$_results = $results;
        unset($fromconfs, $toconfs, $results);
        unset($fnagplugins, $tnagplugins, $nagplugins, $fromnagiosplugins, $tonagiosplugins);
        unset($fplugins, $tplugins, $plugins, $fromnrpecoreplugins, $tonrpecoreplugins);
        unset($fsplugins, $tsplugins, $supplugins, $fromsupnrpeplugins, $tosupnrpeplugins);
        return true;
    }

    /**
     * _getNRPECfg - get core nrpe config file 
     * 
     * @param mixed $deployment deployment we are referencing
     * @param mixed $revision   revision we are referencing
     *
     * @static
     * @access private
     * @return void
     */
    private static function _getNRPECfg($deployment, $revision)
    {
        $results = "";
        if (RevDeploy::existsDeploymentNRPECfg($deployment, $revision) !== false) {
            $nrpecfgInfo = RevDeploy::getDeploymentNRPECfg($deployment, $revision);
            $results = NRPECreate::buildNRPEFile($deployment, $revision, $nrpecfgInfo);
            return $results;
        }
        return $results;
    }

    /**
     * _getSupNRPECfg - get supplemental nrpe config file 
     * 
     * @param mixed $deployment 
     * @param mixed $revision 
     *
     * @static
     * @access private
     * @return void
     */
    private static function _getSupNRPECfg($deployment, $revision)
    {
        $results = "";
        if (RevDeploy::existsDeploymentSupNRPECfg($deployment, $revision) !== false) {
            $supnrpecfgInfo = RevDeploy::getDeploymentSupNRPECfg($deployment, $revision);
            $results = NRPECreate::buildSupNRPEFile($deployment, $revision, $supnrpecfgInfo);
            return $results;
        }
        return $results;
    }

    /**
     * _getPlugins - get plugins for nagios / nrpe and return their contents 
     * 
     * @param mixed $mode       mode driving where the plugins are fetched from
     * @param mixed $deployment deployment we are referencing
     * @param mixed $revision   revision we are referencing
     * @param mixed $plugins    plugins we are referencing
     *
     * @static
     * @access private
     * @return void
     */
    private static function _getPlugins($mode, $deployment, $revision, $plugins)
    {
        $results = array();
        foreach ($plugins as $plugin) {
            if ($mode == 'nrpe-core') {
                $tmpInfo = RevDeploy::getDeploymentNRPEPluginFileContents($deployment, $plugin, $revision);
            } elseif ($mode == 'nrpe-sup') {
                $tmpInfo = RevDeploy::getDeploymentSupNRPEPluginFileContents($deployment, $plugin, $revision);
            } elseif ($mode == 'nagios') {
                $tmpInfo = RevDeploy::getDeploymentNagiosPluginFileContents($deployment, $plugin, $revision);
            }
            if ($tmpInfo !== false) {
                $results[$plugin] = base64_decode($tmpInfo);
            } else {
                $results[$plugin] = "";
            }
        }
        return $results;
    }

}

