<?php
//
// Copyright (c) 2015, Pinterest
// https://github.com/mhwest13/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class MigrateDataStore {

    public static function copyDeployment($deployment, $fromds, $tods)
    {
        // load up the old ds to read the data from
        RevDeploy::init($fromds, true);
        $deployInfo = RevDeploy::getDeploymentInfo($deployment);
        $deployHostSearches = RevDeploy::getDeploymentHostSearches($deployment);
        $deployStaticHosts = RevDeploy::getDeploymentStaticHosts($deployment);

        // load up the new datastore for writing
        RevDeploy::init($tods, true);
        return RevDeploy::createDeployment(
            $deployment, $deployInfo, $deployHostSearches, $deployStaticHosts
        );
    }

    public static function copyDeploymentRevisionMeta($deployment, $fromds, $tods)
    {
        // load up the old ds to read the data from
        RevDeploy::init($fromds, true);
        $revisions = RevDeploy::getDeploymentRevs($deployment);

        // load up the new datastore for writing
        RevDeploy::init($tods, true);
        RevDeploy::setDeploymentAllRevs(
            $deployment, $revisions['prevrev'], $revisions['currrev'], $revisions['nextrev']
        );
    }

    public static function copyDeploymentRevisions($deployment, $fromds, $tods)
    {
        RevDeploy::init($fromds, true);
        $revisions = RevDeploy::getDeploymentAllRevs($deployment);
        $audit_log = RevDeploy::getAuditLog($deployment);
        foreach ($revisions as $revision) {
            self::copyDeploymentRevision($deployment, $revision, $fromds, $tods);
            if ((isset($audit_log[$revision])) && (!empty($audit_log[$revision]))) {
                RevDeploy::init($tods, true);
                RevDeploy::setAuditLog($deployment, $revision, $audit_log[$revision]);
            }
        }
    }

    private static function copyDeploymentRevision($deployment, $revision, $fromds, $tods)
    {
        // load up the old ds to read the data from
        RevDeploy::init($fromds, true);
        $results = array();
        $results['timeperiods'] =
            RevDeploy::getCommonMergedDeploymentTimeperiodswData($deployment, $revision);
        $results['commands'] =
            RevDeploy::getCommonMergedDeploymentCommands($deployment, $revision, false);
        $results['contacttemplates'] =
            RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $revision);
        $results['contactgroups'] =
            RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $revision);
        $results['contacts'] = RevDeploy::getCommonMergedDeploymentContacts($deployment, $revision);
        $results['hosttemplates'] =
            RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $revision);
        $results['hostgroups'] =
            RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $revision);
        $results['servicetemplates'] =
            RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $revision);
        $results['servicegroups'] =
            RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $revision);
        $results['servicedependencies'] =
            RevDeploy::getCommonMergedDeploymentSvcDependencies($deployment, $revision);
        $results['serviceescalations'] =
            RevDeploy::getDeploymentSvcEscalationswInfo($deployment, $revision);
        $results['services'] = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $revision);
        $results['nodetemplates'] =
            RevDeploy::getDeploymentNodeTemplateswInfo($deployment, $revision);
        $results['resourcecfg'] = RevDeploy::getDeploymentResourceCfg($deployment, $revision);
        $results['cgicfg'] = RevDeploy::getDeploymentCgiCfg($deployment, $revision);
        $results['modgearmancfg'] = RevDeploy::getDeploymentModgearmanCfg($deployment, $revision);
        $results['nagioscfg'] = RevDeploy::getDeploymentNagiosCfg($deployment, $revision);
        $results['nrpecmds'] = RevDeploy::getDeploymentNRPECmdswInfo($deployment, $revision);
        $results['nrpecfg'] = RevDeploy::getDeploymentNRPECfg($deployment, $revision);
        $results['nrpeplugins'] = RevDeploy::getDeploymentNRPEPluginswData($deployment, $revision);
        $results['supnrpecfg'] = RevDeploy::getDeploymentSupNRPECfg($deployment, $revision);
        $results['supnrpeplugins'] =
            RevDeploy::getDeploymentSupNRPEPluginswData($deployment, $revision);
        $results['nagiosplugins'] =
            RevDeploy::getDeploymentNagiosPluginswData($deployment, $revision);
        $results['clustercommands'] =
            RevDeploy::getDeploymentClusterCmdswInfo($deployment, $revision);

        // load up the new datastore for writing
        RevDeploy::init($tods, true);
        foreach ($results as $key => $value) {
            if (empty($value)) continue;
            switch ($key) {
                case 'timeperiods':
                    self::copyTimeperiods($deployment, $revision, $results['timeperiods']); break;
                case 'commands':
                    self::copyCommands($deployment, $revision, $results['commands']); break;
                case 'contacttemplates':
                    self::copyContactTemplates(
                        $deployment, $revision, $results['contacttemplates']
                    );
                    break;
                case 'contactgroups':
                    self::copyContactGroups($deployment, $revision, $results['contactgroups']);
                    break;
                case 'contacts':
                    self::copyContacts($deployment, $revision, $results['contacts']); break;
                case 'hosttemplates':
                    self::copyHostTemplates($deployment, $revision, $results['hosttemplates']);
                    break;
                case 'hostgroups':
                    self::copyHostGroups($deployment, $revision, $results['hostgroups']); break;
                case 'servicetemplates':
                    self::copyServiceTemplates(
                        $deployment, $revision, $results['servicetemplates']
                    );
                    break;
                case 'servicegroups':
                    self::copyServiceGroups($deployment, $revision, $results['servicegroups']);
                    break;
                case 'servicedependencies':
                    self::copyServiceDependencies(
                        $deployment, $revision, $results['servicedependencies']
                    );
                    break;
                case 'serviceescalations':
                    self::copyServiceEscalations(
                        $deployment, $revision, $results['serviceescalations']
                    );
                    break;
                case 'services':
                    self::copyServices($deployment, $revision, $results['services']); break;
                case 'nodetemplates':
                    self::copyNodeTemplates($deployment, $revision, $results['nodetemplates']);
                    break;
                case 'resourcecfg':
                    self::copyResourceCfg($deployment, $revision, $results['resourcecfg']); break;
                case 'cgicfg':
                    self::copyCGICfg($deployment, $revision, $results['cgicfg']); break;
                case 'modgearmancfg':
                    self::copyModgearmanCfg($deployment, $revision, $results['modgearmancfg']);
                    break;
                case 'nagioscfg':
                    self::copyNagiosCfg($deployment, $revision, $results['nagioscfg']); break;
                case 'nrpecmds':
                    self::copyNRPECmds($deployment, $revision, $results['nrpecmds']); break;
                case 'nrpecfg':
                    self::copyNRPECfg($deployment, $revision, $results['nrpecfg']); break;
                case 'nrpeplugins':
                    self::copyNRPEPlugins($deployment, $revision, $results['nrpeplugins']); break;
                case 'supnrpecfg':
                    self::copySupNRPECfg($deployment, $revision, $results['supnrpecfg']); break;
                case 'supnrpeplugins':
                    self::copySupNRPEPlugins($deployment, $revision, $results['supnrpeplugins']);
                    break;
                case 'nagiosplugins':
                    self::copyNagiosPlugins($deployment, $revision, $results['nagiosplugins']);
                    break;
                case 'clustercommands':
                    self::copyClusterCommands($deployment, $revision, $results['clustercommands']);
                    break;
                default:
                    break;
            }
        }
    }

    private static function copyTimeperiods($deployment, $revision, array $tpInfo)
	{
        foreach ($tpInfo as $tpName => $tpArray) {
            if ($tpArray['deployment'] != $deployment) continue;
            $tpTimes = $tpArray['times'];
            unset($tpArray['times']);
            unset($tpArray['deployment']);
            RevDeploy::createDeploymentTimeperiod(
                $deployment, $tpName, $tpArray, $tpTimes, $revision
            );
        }
    }

    private static function copyCommands($deployment, $revision, array $cmdInfo)
	{
        foreach ($cmdInfo as $cmd => $cmdArray) {
            if ($cmdArray['deployment'] != $deployment) continue;
            unset($cmdArray['deployment']);
            RevDeploy::createDeploymentCommand($deployment, $cmd, $cmdArray, $revision);
        }
    }

    private static function copyContactTemplates($deployment, $revision, array $ctInfo)
	{
        foreach ($ctInfo as $ct => $ctArray) {
            if ($ctArray['deployment'] != $deployment) continue;
            unset($ctArray['deployment']);
            RevDeploy::createDeploymentContactTemplate($deployment, $ct, $ctArray, $revision);
        }
    }

    private static function copyContactGroups($deployment, $revision, array $cgInfo)
	{
        foreach ($cgInfo as $cg => $cgArray) {
            if ($cgArray['deployment'] != $deployment) continue;
            unset($cgArray['deployment']);
            RevDeploy::createDeploymentContactGroup($deployment, $cg, $cgArray, $revision);
        }
    }

    private static function copyContacts($deployment, $revision, array $cInfo)
	{
        foreach ($cInfo as $contact => $cArray)
	{
            if ($cArray['deployment'] != $deployment) continue;
            unset($cArray['deployment']);
            RevDeploy::createDeploymentContact($deployment, $contact, $cArray, $revision);
        }
    }

    private static function copyHostTemplates($deployment, $revision, array $htInfo)
	{
        foreach ($htInfo as $ht => $htArray) {
            if ($htArray['deployment'] != $deployment) continue;
            unset($htArray['deployment']);
            RevDeploy::createDeploymentHostTemplate($deployment, $ht, $htArray, $revision);
        }
    }

    private static function copyHostGroups($deployment, $revision, array $hgInfo)
	{
        foreach ($hgInfo as $hg => $hgArray) {
            if ($hgArray['deployment'] != $deployment) continue;
            unset($hgArray['deployment']);
            RevDeploy::createDeploymentHostGroup($deployment, $hg, $hgArray, $revision);
        }
    }

    private static function copyServiceTemplates($deployment, $revision, array $stInfo)
	{
        foreach ($stInfo as $st => $stArray) {
            if ($stArray['deployment'] != $deployment) continue;
            unset($stArray['deployment']);
            RevDeploy::createDeploymentSvcTemplate($deployment, $st, $stArray, $revision);
        }
    }

    private static function copyServiceGroups($deployment, $revision, array $sgInfo)
	{
        foreach ($sgInfo as $sg => $sgArray) {
            if ($sgArray['deployment'] != $deployment) continue;
            unset($sgArray['deployment']);
            RevDeploy::createDeploymentSvcGroup($deployment, $sg, $sgArray, $revision);
        }
    }

    private static function copyServiceDependencies($deployment, $revision, array $sdInfo)
	{
        foreach ($sdInfo as $sd => $sdArray) {
            if ($sdArray['deployment'] != $deployment) continue;
            unset($sdArray['deployment']);
            RevDeploy::createDeploymentSvcDependency($deployment, $sd, $sdArray, $revision);
        }
    }

    private static function copyServiceEscalations($deployment, $revision, array $seInfo)
	{
        foreach ($seInfo as $se =>$seArray) {
            if ($seArray['deployment'] != $deployment) continue;
            unset($seArray['deployment']);
            RevDeploy::createDeploymentSvcEscalation($deployment, $se, $seArray, $revision);
        }
    }

    private static function copyServices($deployment, $revision, array $sInfo)
	{
        foreach ($sInfo as $svc => $sArray) {
            if ($sArray['deployment'] != $deployment) continue;
            unset($sArray['deployment']);
            RevDeploy::createDeploymentSvc($deployment, $svc, $sArray, $revision);
        }
    }

    private static function copyNodeTemplates($deployment, $revision, array $ntInfo)
	{
        foreach ($ntInfo as $nt => $ntArray) {
            if ($ntArray['deployment'] != $deployment) continue;
            unset($ntArray['deployment']);
            RevDeploy::createDeploymentNodeTemplate($deployment, $nt, $ntArray, $revision);
        }
    }

    private static function copyClusterCommands($deployment, $revision, array $ccInfo)
	{
        foreach ($ccInfo as $cc => $ccArray) {
            if ($ccArray['deployment'] != $deployment) continue;
            unset($ccArray['deployment']);
            RevDeploy::createDeploymentClusterCmd($deployment, $cc, $ccArray, $revision);
        }
    }

    private static function copyResourceCfg($deployment, $revision, array $resourceInfo)
	{
        RevDeploy::writeDeploymentResourceCfg($deployment, $resourceInfo, $revision);
    }

    private static function copyCGICfg($deployment, $revision, array $cgiInfo)
	{
        RevDeploy::writeDeploymentCgiCfg($deployment, $cgiInfo, $revision);
    }

    private static function copyModgearmanCfg($deployment, $revision, array $mgInfo)
	{
        RevDeploy::writeDeploymentModgearmanCfg($deployment, $mgInfo, $revision);
    }

    private static function copyNagiosCfg($deployment, $revision, array $nInfo)
	{
        RevDeploy::writeDeploymentNagiosCfg($deployment, $nInfo, $revision);
    }

    private static function copyNRPECmds($deployment, $revision, array $nrpeCmds)
	{
        foreach ($nrpeCmds as $nrpeCmd => $nrpeArray) {
            unset($nrpeArray['deployment']);
            RevDeploy::createDeploymentNRPECmd($deployment, $nrpeCmd, $nrpeArray, $revision);
        }
    }

    private static function copyNRPECfg($deployment, $revision, array $nrpeInfo)
	{
        RevDeploy::writeDeploymentNRPECfg($deployment, $nrpeInfo, $revision);
    }

    private static function copyNRPEPlugins($deployment, $revision, array $nrpePlugins)
	{
        foreach ($nrpePlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentNRPEPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

    private static function copySupNRPECfg($deployment, $revision, array $supnrpeInfo)
	{
        RevDeploy::writeDeploymentSupNRPECfg($deployment, $supnrpeInfo, $revision);
    }

    private static function copySupNRPEPlugins($deployment, $revision, $supnrpePlugins)
	{
        foreach ($supnrpePlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentSupNRPEPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

    private static function copyNagiosPlugins($deployment, $revision, array $nagiosPlugins)
	{
        foreach ($nagiosPlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentNagiosPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

}
