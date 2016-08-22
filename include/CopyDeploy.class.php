<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class CopyDeploy {

    protected static $init = false;
    protected static $m_commonrepo;

    public static function init() {
        if (self::$init === false) {
            RevDeploy::init();
            self::$init = true;
            return;
        }
        return;
    }

    public static function resetDeploymentRevision($deployment, $currrev, $torev) {
        if (self::$init === false) self::init();
        RevDeploy::deleteDeploymentRev($deployment, $torev);
        self::copyDeploymentRevision($deployment, $currrev, $torev);
    }

    public static function copyDeploymentRevision($deployment, $fromrev, $torev) {
        if (self::$init === false) self::init();
        self::$m_commonrepo = RevDeploy::getDeploymentCommonRepo($deployment);
        $results = array();
        $results['timeperiods'] = RevDeploy::getCommonMergedDeploymentTimeperiodswData($deployment, $fromrev);
        $results['commands'] = RevDeploy::getCommonMergedDeploymentCommands($deployment, $fromrev, false);
        $results['contacttemplates'] = RevDeploy::getCommonMergedDeploymentContactTemplates($deployment, $fromrev);
        $results['contactgroups'] = RevDeploy::getCommonMergedDeploymentContactGroups($deployment, $fromrev);
        $results['contacts'] = RevDeploy::getCommonMergedDeploymentContacts($deployment, $fromrev);
        $results['hosttemplates'] = RevDeploy::getCommonMergedDeploymentHostTemplates($deployment, $fromrev);
        $results['hostgroups'] = RevDeploy::getCommonMergedDeploymentHostGroups($deployment, $fromrev);
        $results['servicetemplates'] = RevDeploy::getCommonMergedDeploymentSvcTemplates($deployment, $fromrev);
        $results['servicegroups'] = RevDeploy::getCommonMergedDeploymentSvcGroups($deployment, $fromrev);
        $results['servicedependencies'] = RevDeploy::getCommonMergedDeploymentSvcDependencies($deployment, $fromrev);
        $results['serviceescalations'] = RevDeploy::getDeploymentSvcEscalationswInfo($deployment, $fromrev);
        $results['services'] = RevDeploy::getCommonMergedDeploymentSvcs($deployment, $fromrev);
        $results['nodetemplates'] = RevDeploy::getDeploymentNodeTemplateswInfo($deployment, $fromrev);
        $results['resourcecfg'] = RevDeploy::getDeploymentResourceCfg($deployment, $fromrev);
        $results['cgicfg'] = RevDeploy::getDeploymentCgiCfg($deployment, $fromrev);
        $results['modgearmancfg'] = RevDeploy::getDeploymentModgearmanCfg($deployment, $fromrev);
        $results['nagioscfg'] = RevDeploy::getDeploymentNagiosCfg($deployment, $fromrev);
        $results['nrpecmds'] = RevDeploy::getDeploymentNRPECmdswInfo($deployment, $fromrev);
        $results['nrpecfg'] = RevDeploy::getDeploymentNRPECfg($deployment, $fromrev);
        $results['nrpeplugins'] = RevDeploy::getDeploymentNRPEPluginswData($deployment, $fromrev);
        $results['supnrpecfg'] = RevDeploy::getDeploymentSupNRPECfg($deployment, $fromrev);
        $results['supnrpeplugins'] = RevDeploy::getDeploymentSupNRPEPluginswData($deployment, $fromrev);
        $results['nagiosplugins'] = RevDeploy::getDeploymentNagiosPluginswData($deployment, $fromrev);
        $results['clustercommands'] = RevDeploy::getDeploymentClusterCmdswInfo($deployment, $fromrev);
        foreach ($results as $key => $value) {
            if (empty($value)) continue;
            switch ($key) {
                case 'timeperiods':
                    self::copyTimeperiods($deployment, $torev, $results['timeperiods']); break;
                case 'commands':
                    self::copyCommands($deployment, $torev, $results['commands']); break;
                case 'contacttemplates':
                    self::copyContactTemplates($deployment, $torev, $results['contacttemplates']); break;
                case 'contactgroups':
                    self::copyContactGroups($deployment, $torev, $results['contactgroups']); break;
                case 'contacts':
                    self::copyContacts($deployment, $torev, $results['contacts']); break;
                case 'hosttemplates':
                    self::copyHostTemplates($deployment, $torev, $results['hosttemplates']); break;
                case 'hostgroups':
                    self::copyHostGroups($deployment, $torev, $results['hostgroups']); break;
                case 'servicetemplates':
                    self::copyServiceTemplates($deployment, $torev, $results['servicetemplates']); break;
                case 'servicegroups':
                    self::copyServiceGroups($deployment, $torev, $results['servicegroups']); break;
                case 'servicedependencies':
                    self::copyServiceDependencies($deployment, $torev, $results['servicedependencies']); break;
                case 'serviceescalations':
                    self::copyServiceEscalations($deployment, $torev, $results['serviceescalations']); break;
                case 'services':
                    self::copyServices($deployment, $torev, $results['services']); break;
                case 'nodetemplates':
                    self::copyNodeTemplates($deployment, $torev, $results['nodetemplates']); break;
                case 'resourcecfg':
                    self::copyResourceCfg($deployment, $torev, $results['resourcecfg']); break;
                case 'cgicfg':
                    self::copyCGICfg($deployment, $torev, $results['cgicfg']); break;
                case 'modgearmancfg':
                    self::copyModgearmanCfg($deployment, $torev, $results['modgearmancfg']); break;
                case 'nagioscfg':
                    self::copyNagiosCfg($deployment, $torev, $results['nagioscfg']); break;
                case 'nrpecmds':
                    self::copyNRPECmds($deployment, $torev, $results['nrpecmds']); break;
                case 'nrpecfg':
                    self::copyNRPECfg($deployment, $torev, $results['nrpecfg']); break;
                case 'nrpeplugins':
                    self::copyNRPEPlugins($deployment, $torev, $results['nrpeplugins']); break;
                case 'supnrpecfg':
                    self::copySupNRPECfg($deployment, $torev, $results['supnrpecfg']); break;
                case 'supnrpeplugins':
                    self::copySupNRPEPlugins($deployment, $torev, $results['supnrpeplugins']); break;
                case 'nagiosplugins':
                    self::copyNagiosPlugins($deployment, $torev, $results['nagiosplugins']); break;
                case 'clustercommands':
                    self::copyClusterCommands($deployment, $torev, $results['clustercommands']); break;
                default:
                    break;
            }
        }
    }

    private static function copyTimeperiods($deployment, $revision, array $tpInfo) {
        if (self::$init === false) self::init();
        foreach ($tpInfo as $tpName => $tpArray) {
            if ($tpArray['deployment'] != $deployment) continue;
            $tpTimes = $tpArray['times'];
            unset($tpArray['times']);
            unset($tpArray['deployment']);
            RevDeploy::createDeploymentTimeperiod($deployment, $tpName, $tpArray, $tpTimes, $revision);
        }
    }

    private static function copyCommands($deployment, $revision, array $cmdInfo) {
        if (self::$init === false) self::init();
        foreach ($cmdInfo as $cmd => $cmdArray) {
            if ($cmdArray['deployment'] != $deployment) continue;
            unset($cmdArray['deployment']);
            RevDeploy::createDeploymentCommand($deployment, $cmd, $cmdArray, $revision);
        }
    }

    private static function copyContactTemplates($deployment, $revision, array $ctInfo) {
        if (self::$init === false) self::init();
        foreach ($ctInfo as $ct => $ctArray) {
            if ($ctArray['deployment'] != $deployment) continue;
            unset($ctArray['deployment']);
            RevDeploy::createDeploymentContactTemplate($deployment, $ct, $ctArray, $revision);
        }
    }

    private static function copyContactGroups($deployment, $revision, array $cgInfo) {
        if (self::$init === false) self::init();
        foreach ($cgInfo as $cg => $cgArray) {
            if ($cgArray['deployment'] != $deployment) continue;
            unset($cgArray['deployment']);
            RevDeploy::createDeploymentContactGroup($deployment, $cg, $cgArray, $revision);
        }
    }

    private static function copyContacts($deployment, $revision, array $cInfo) {
        if (self::$init === false) self::init();
        foreach ($cInfo as $contact => $cArray) {
            if ($cArray['deployment'] != $deployment) continue;
            unset($cArray['deployment']);
            RevDeploy::createDeploymentContact($deployment, $contact, $cArray, $revision);
        }
    }

    private static function copyHostTemplates($deployment, $revision, array $htInfo) {
        if (self::$init === false) self::init();
        foreach ($htInfo as $ht => $htArray) {
            if ($htArray['deployment'] != $deployment) continue;
            unset($htArray['deployment']);
            RevDeploy::createDeploymentHostTemplate($deployment, $ht, $htArray, $revision);
        }
    }

    private static function copyHostGroups($deployment, $revision, array $hgInfo) {
        if (self::$init === false) self::init();
        foreach ($hgInfo as $hg => $hgArray) {
            if ($hgArray['deployment'] != $deployment) continue;
            unset($hgArray['deployment']);
            RevDeploy::createDeploymentHostGroup($deployment, $hg, $hgArray, $revision);
        }
    }

    private static function copyServiceTemplates($deployment, $revision, array $stInfo) {
        if (self::$init === false) self::init();
        foreach ($stInfo as $st => $stArray) {
            if ($stArray['deployment'] != $deployment) continue;
            unset($stArray['deployment']);
            RevDeploy::createDeploymentSvcTemplate($deployment, $st, $stArray, $revision);
        }
    }

    private static function copyServiceGroups($deployment, $revision, array $sgInfo) {
        if (self::$init === false) self::init();
        foreach ($sgInfo as $sg => $sgArray) {
            if ($sgArray['deployment'] != $deployment) continue;
            unset($sgArray['deployment']);
            RevDeploy::createDeploymentSvcGroup($deployment, $sg, $sgArray, $revision);
        }
    }

    private static function copyServiceDependencies($deployment, $revision, array $sdInfo) {
        if (self::$init === false) self::init();
        foreach ($sdInfo as $sd => $sdArray) {
            if ($sdArray['deployment'] != $deployment) continue;
            unset($sdArray['deployment']);
            RevDeploy::createDeploymentSvcDependency($deployment, $sd, $sdArray, $revision);
        }
    }

    private static function copyServiceEscalations($deployment, $revision, array $seInfo) {
        if (self::$init === false) self::init();
        foreach ($seInfo as $se =>$seArray) {
            if ($seArray['deployment'] != $deployment) continue;
            unset($seArray['deployment']);
            RevDeploy::createDeploymentSvcEscalation($deployment, $se, $seArray, $revision);
        }
    }

    private static function copyServices($deployment, $revision, array $sInfo) {
        if (self::$init === false) self::init();
        foreach ($sInfo as $svc => $sArray) {
            if ($sArray['deployment'] != $deployment) continue;
            unset($sArray['deployment']);
            RevDeploy::createDeploymentSvc($deployment, $svc, $sArray, $revision);
        }
    }

    private static function copyNodeTemplates($deployment, $revision, array $ntInfo) {
        if (self::$init === false) self::init();
        foreach ($ntInfo as $nt => $ntArray) {
            if ($ntArray['deployment'] != $deployment) continue;
            unset($ntArray['deployment']);
            RevDeploy::createDeploymentNodeTemplate($deployment, $nt, $ntArray, $revision);
        }
    }

    private static function copyClusterCommands($deployment, $revision, array $ccInfo) {
        if (self::$init === false) self::init();
        foreach ($ccInfo as $cc => $ccArray) {
            if ($ccArray['deployment'] != $deployment) continue;
            unset($ccArray['deployment']);
            RevDeploy::createDeploymentClusterCmd($deployment, $cc, $ccArray, $revision);
        }
    }

    private static function copyResourceCfg($deployment, $revision, array $resourceInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentResourceCfg($deployment, $resourceInfo, $revision);
    }

    private static function copyCGICfg($deployment, $revision, array $cgiInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentCgiCfg($deployment, $cgiInfo, $revision);
    }

    private static function copyModgearmanCfg($deployment, $revision, array $mgInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentModgearmanCfg($deployment, $mgInfo, $revision);
    }

    private static function copyNagiosCfg($deployment, $revision, array $nInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentNagiosCfg($deployment, $nInfo, $revision);
    }

    private static function copyNRPECmds($deployment, $revision, array $nrpeCmds) {
        if (self::$init === false) self::init();
        foreach ($nrpeCmds as $nrpeCmd => $nrpeArray) {
            unset($nrpeArray['deployment']);
            RevDeploy::createDeploymentNRPECmd($deployment, $nrpeCmd, $nrpeArray, $revision);
        }
    }

    private static function copyNRPECfg($deployment, $revision, array $nrpeInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentNRPECfg($deployment, $nrpeInfo, $revision);
    }

    private static function copyNRPEPlugins($deployment, $revision, array $nrpePlugins) {
        if (self::$init === false) self::init();
        foreach ($nrpePlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentNRPEPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

    private static function copySupNRPECfg($deployment, $revision, array $supnrpeInfo) {
        if (self::$init === false) self::init();
        RevDeploy::writeDeploymentSupNRPECfg($deployment, $supnrpeInfo, $revision);
    }

    private static function copySupNRPEPlugins($deployment, $revision, $supnrpePlugins) {
        if (self::$init === false) self::init();
        foreach ($supnrpePlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentSupNRPEPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

    private static function copyNagiosPlugins($deployment, $revision, array $nagiosPlugins) {
        if (self::$init === false) self::init();
        foreach ($nagiosPlugins as $plugin => $pArray) {
            if ($pArray['deployment'] != $deployment) continue;
            unset($pArray['deployment']);
            RevDeploy::createDeploymentNagiosPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

}
