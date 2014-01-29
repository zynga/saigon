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
                default:
                    break;
            }
        }
    }

    private static function copyTimeperiods($deployment, $revision, array $tpInfo) {
        if (self::$init === false) self::init();
        foreach ($tpInfo as $tpName => $tpArray) {
            if (($tpArray['deployment'] == self::$m_commonrepo) && ($tpArray['deployment'] == 'common')) continue;
            if ($tpArray['deployment'] == self::$m_commonrepo) continue;
            $tpTimes = $tpArray['times'];
            unset($tpArray['times']);
            unset($tpArray['deployment']);
            RevDeploy::createDeploymentTimeperiod($deployment, $tpName, $tpArray, $tpTimes, $revision);
        }
    }

    private static function copyCommands($deployment, $revision, array $cmdInfo) {
        if (self::$init === false) self::init();
        foreach ($cmdInfo as $cmd => $cmdArray) {
            if (($cmdArray['deployment'] == self::$m_commonrepo) && ($cmdArray['deployment'] == 'common')) continue;
            if ($cmdArray['deployment'] == self::$m_commonrepo) continue;
            unset($cmdArray['deployment']);
            RevDeploy::createDeploymentCommand($deployment, $cmd, $cmdArray, $revision);
        }
    }

    private static function copyContactTemplates($deployment, $revision, array $ctInfo) {
        if (self::$init === false) self::init();
        foreach ($ctInfo as $ct => $ctArray) {
            if (($ctArray['deployment'] == self::$m_commonrepo) && ($ctArray['deployment'] == 'common')) continue;
            if ($ctArray['deployment'] == self::$m_commonrepo) continue;
            unset($ctArray['deployment']);
            foreach ($ctArray as $key => $value) {
                if (is_array($ctArray[$key])) {
                    $ctArray[$key] = implode(',', $ctArray[$key]);
                }
            }
            RevDeploy::createDeploymentContactTemplate($deployment, $ct, $ctArray, $revision);
        }
    }

    private static function copyContactGroups($deployment, $revision, array $cgInfo) {
        if (self::$init === false) self::init();
        foreach ($cgInfo as $cg => $cgArray) {
            if (($cgArray['deployment'] == self::$m_commonrepo) && ($cgArray['deployment'] == 'common')) continue;
            if ($cgArray['deployment'] == self::$m_commonrepo) continue;
            foreach ($cgArray as $key => $value) {
                if (is_array($cgArray[$key])) {
                    $cgArray[$key] = implode(',', $cgArray[$key]);
                }
            }
            unset($cgArray['deployment']);
            RevDeploy::createDeploymentContactGroup($deployment, $cg, $cgArray, $revision);
        }
    }

    private static function copyContacts($deployment, $revision, array $cInfo) {
        if (self::$init === false) self::init();
        foreach ($cInfo as $contact => $cArray) {
            if (($cArray['deployment'] == self::$m_commonrepo) && ($cArray['deployment'] == 'common')) continue;
            if ($cArray['deployment'] == self::$m_commonrepo) continue;
            foreach ($cArray as $key => $value) {
                if (is_array($cArray[$key])) {
                    $cArray[$key] = implode(',', $cArray[$key]);
                }
            }
            RevDeploy::createDeploymentContact($deployment, $contact, $cArray, $revision);
        }
    }

    private static function copyHostTemplates($deployment, $revision, array $htInfo) {
        if (self::$init === false) self::init();
        foreach ($htInfo as $ht => $htArray) {
            if (($htArray['deployment'] == self::$m_commonrepo) && ($htArray['deployment'] == 'common')) continue;
            if ($htArray['deployment'] == self::$m_commonrepo) continue;
            unset($htArray['deployment']);
            foreach ($htArray as $key => $value) {
                if (is_array($htArray[$key])) {
                    $htArray[$key] = implode(',', $htArray[$key]);
                }
            }
            RevDeploy::createDeploymentHostTemplate($deployment, $ht, $htArray, $revision);
        }
    }

    private static function copyHostGroups($deployment, $revision, array $hgInfo) {
        if (self::$init === false) self::init();
        foreach ($hgInfo as $hg => $hgArray) {
            if (($hgArray['deployment'] == self::$m_commonrepo) && ($hgArray['deployment'] == 'common')) continue;
            if ($hgArray['deployment'] == self::$m_commonrepo) continue;
            unset($hgArray['deployment']);
            RevDeploy::createDeploymentHostGroup($deployment, $hg, $hgArray, $revision);
        }
    }

    private static function copyServiceTemplates($deployment, $revision, array $stInfo) {
        if (self::$init === false) self::init();
        foreach ($stInfo as $st => $stArray) {
            if (($stArray['deployment'] == self::$m_commonrepo) && ($stArray['deployment'] == 'common')) continue;
            if ($stArray['deployment'] == self::$m_commonrepo) continue;
            unset($stArray['deployment']);
            foreach ($stArray as $key => $value) {
                if (is_array($stArray[$key])) {
                    $stArray[$key] = implode(',', $stArray[$key]);
                }
            }
            RevDeploy::createDeploymentSvcTemplate($deployment, $st, $stArray, $revision);
        }
    }

    private static function copyServiceGroups($deployment, $revision, array $sgInfo) {
        if (self::$init === false) self::init();
        foreach ($sgInfo as $sg => $sgArray) {
            if (($sgArray['deployment'] == self::$m_commonrepo) && ($sgArray['deployment'] == 'common')) continue;
            if ($sgArray['deployment'] == self::$m_commonrepo) continue;
            unset($sgArray['deployment']);
            foreach ($sgArray as $key => $value) {
                if (is_array($sgArray[$key])) {
                    $sgArray[$key] = implode(',', $sgArray[$key]);
                }
            }
            RevDeploy::createDeploymentSvcGroup($deployment, $sg, $sgArray, $revision);
        }
    }

    private static function copyServiceDependencies($deployment, $revision, array $sdInfo) {
        if (self::$init === false) self::init();
        foreach ($sdInfo as $sd => $sdArray) {
            if (($sdArray['deployment'] == self::$m_commonrepo) && ($sdArray['deployment'] == 'common')) continue;
            if ($sdArray['deployment'] == self::$m_commonrepo) continue;
            unset($sdArray['deployment']);
            foreach ($sdArray as $key => $value) {
                if (is_array($sdArray[$key])) {
                    $sdArray[$key] = implode(',', $sdArray[$key]);
                }
            }
            RevDeploy::createDeploymentSvcDependency($deployment, $sd, $sdArray, $revision);
        }
    }

    private static function copyServiceEscalations($deployment, $revision, array $seInfo) {
        if (self::$init === false) self::init();
        foreach ($seInfo as $se =>$seArray) {
            if (($seArray['deployment'] == self::$m_commonrepo) && ($seArray['deployment'] == 'common')) continue;
            if ($seArray['deployment'] == self::$m_commonrepo) continue;
            unset($seArray['deployment']);
            foreach ($seArray as $key => $value) {
                if (is_array($seArray[$key])) {
                    $seArray[$key] = implode(',', $seArray[$key]);
                }
            }
            RevDeploy::createDeploymentSvcEscalation($deployment, $se, $seArray, $revision);
        }
    }

    private static function copyServices($deployment, $revision, array $sInfo) {
        if (self::$init === false) self::init();
        foreach ($sInfo as $svc => $sArray) {
            if (($sArray['deployment'] == self::$m_commonrepo) && ($sArray['deployment'] == 'common')) continue;
            if ($sArray['deployment'] == self::$m_commonrepo) continue;
            unset($sArray['deployment']);
            foreach ($sArray as $key => $value) {
                if (is_array($sArray[$key])) {
                    $sArray[$key] = implode(',', $sArray[$key]);
                }
            }
            RevDeploy::createDeploymentSvc($deployment, $svc, $sArray, $revision);
        }
    }

    private static function copyNodeTemplates($deployment, $revision, array $ntInfo) {
        if (self::$init === false) self::init();
        foreach ($ntInfo as $nt => $ntArray) {
            if ((isset($ntArray['services'])) && (!empty($ntArray['services']))) {
                $ntArray['services'] = implode(',', $ntArray['services']);
            }
            if ((isset($ntArray['nservices'])) && (!empty($ntArray['nservices']))) {
                $ntArray['nservices'] = implode(',', $ntArray['nservices']);
            }
            RevDeploy::createDeploymentNodeTemplate($deployment, $nt, $ntArray, $revision);
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
            RevDeploy::createDeploymentSupNRPEPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

    private static function copyNagiosPlugins($deployment, $revision, array $nagiosPlugins) {
        if (self::$init === false) self::init();
        foreach ($nagiosPlugins as $plugin => $pArray) {
            RevDeploy::createDeploymentNagiosPlugin($deployment, $plugin, $pArray, $revision);
        }
    }

}
