<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

class NagLogger
{

    private $log;
    private static $_config = array(
        'appenders' => array(
            'default' => array(
                'class' => 'LoggerAppenderFile',
                'layout' => array(
                    'class' => 'LoggerLayoutPattern',
                    'params' => array(
                        'conversionPattern' => '%d{[Y/m/d H:i:s.u]} [%t] %c - %m%n'
                    ),
                ),
                'params' => array(
                    'file' => AUDIT_LOG,
                ),
            ),
        ),
        'renderers' => array(
            array('renderedClass' => 'DeploymentData', 
                'renderingClass' => 'DeploymentDataRenderer'),
            array('renderedClass' => 'CgiConfigData', 
                'renderingClass' => 'CgiConfigDataRenderer'),
            array('renderedClass' => 'CommandData',
                'renderingClass' => 'CommandDataRenderer'),
            array('renderedClass' => 'ContactData',
                'renderingClass' => 'ContactDataRenderer'),
            array('renderedClass' => 'ContactGroupData',
                'renderingClass' => 'ContactGroupDataRenderer'),
            array('renderedClass' => 'ContactTemplateData',
                'renderingClass' => 'ContactTemplateDataRenderer'),
            array('renderedClass' => 'HostGroupData',
                'renderingClass' => 'HostGroupDataRenderer'),
            array('renderedClass' => 'HostTemplateData',
                'renderingClass' => 'HostTemplateDataRenderer'),
            array('renderedClass' => 'ModgearmanConfigData',
                'renderingClass' => 'ModgearmanConfigDataRenderer'),
            array('renderedClass' => 'NagiosConfigData',
                'renderingClass' => 'NagiosConfigDataRenderer'),
            array('renderedClass' => 'NagiosPluginData',
                'renderingClass' => 'NagiosPluginDataRenderer'),
            array('renderedClass' => 'NodeTemplateData',
                'renderingClass' => 'NodeTemplateDataRenderer'),
            array('renderedClass' => 'NRPECfgData',
                'renderingClass' => 'NRPECfgDataRenderer'),
            array('renderedClass' => 'NRPECmdData',
                'renderingClass' => 'NRPECmdDataRenderer'),
            array('renderedClass' => 'NRPEPluginData',
                'renderingClass' => 'NRPEPluginDataRenderer'),
            array('renderedClass' => 'SupNRPEPluginData',
                'renderingClass' => 'SupNRPEPluginDataRenderer'),
            array('renderedClass' => 'ResourceConfigData',
                'renderingClass' => 'ResourceConfigDataRenderer'),
            array('renderedClass' => 'ServiceData',
                'renderingClass' => 'ServiceDataRenderer'),
            array('renderedClass' => 'ServiceDependencyData',
                'renderingClass' => 'ServiceDependencyDataRenderer'),
            array('renderedClass' => 'ServiceGroupData',
                'renderingClass' => 'ServiceGroupDataRenderer'),
            array('renderedClass' => 'ServiceTemplateData',
                'renderingClass' => 'ServiceTemplateDataRenderer'),
            array('renderedClass' => 'ServiceEscalationData',
                'renderingClass' => 'ServiceEscalationDataRenderer'),
            array('renderedClass' => 'SupNRPECfgData',
                'renderingClass' => 'SupNRPECfgDataRenderer'),
            array('renderedClass' => 'TimeperiodData',
                'renderingClass' => 'TimeperiodDataRenderer'),
        ),
        'rootLogger' => array(
            'appenders' => array('default'),
        ),
    );

    /**
     * __construct - object initializer 
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        Logger::configure(self::$_config);
        $this->log = Logger::getLogger('saigon');
    }

    /**
     * addToLog - write data to log file, if a renderedClass is detected,
     *  the renderingClass output style will be used.
     * 
     * @param mixed $object data to be rendered
     *
     * @access public
     * @return void
     */
    public function addToLog($object)
    {
        $this->log->info($object);
    }

}

