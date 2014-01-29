<?php
//
// Copyright (c) 2013, Zynga Inc.
// https://github.com/zynga/saigon
// Author: Matt West (https://github.com/mhwest13)
// License: BSD 2-Clause
//

require HTML_HEADER;
?>
<style>
a.menuItem:link,a.menuItem:visited { display:block;width:145px;font-weight:bold;color:#000000;background-color:#66a9bd;text-align:center;padding:4px;text-decoration:none;border-radius:6px;border-width:2px;border-style:solid;border-color:#000000; }
a.menuItem:hover,a.menuItem:active { background-color:#719ba7; }
</style>
<script type="text/javascript">
$(function() {
    $('.parentClass').click(function() {
        $('.parent-desc-' + $(this).attr("id")).slideToggle("fast");
        if ($(this).find("img").attr("src") == "static/imgs/minusSign.gif") {
            $(this).find("img").attr("src", "static/imgs/plusSign.gif");
        } else {
            $(this).find("img").attr("src", "static/imgs/minusSign.gif");
        }
    });
});
</script>
<script type="text/javascript">
$(function() {
    $('.childClass').click(function() {
        $('.child-desc-' + $(this).attr("id")).slideToggle("fast");
        if ($(this).find("img").attr("src") == "static/imgs/minusSign.gif") {
            $(this).find("img").attr("src", "static/imgs/plusSign.gif");
        } else {
            $(this).find("img").attr("src", "static/imgs/minusSign.gif");
        }
    });
});
</script>

<?php
if ($viewData->deploysettings['deploystyle'] != 'nrpe') {
?>
<!-- Nagios Wrapper Div -->
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="NagiosWrapper">
        <img src="static/imgs/plusSign.gif">
        Nagios Configuration
    </div>
    <div class="divHide parent-desc-NagiosWrapper divCacSubResponse">
    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
    <div id="overview" class="divCacSubResponse">
        The following section is for defining Nagis Configuration Information
    </div>
    <div class="divCacGroup"></div>
        <!-- Core Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="CoreSection">
                <img src="static/imgs/plusSign.gif">
                Core Settings
            </div>
            <div class="divHide parent-desc-CoreSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                    The following section is for defining some of the core information for Nagios
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Timeperiod Section -->
                    <div id="Timeperiod">
                        <a class="menuItem" href="action.php?controller=timeperiod&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Timeperiods</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <div id="Commands">
                        <a class="menuItem" href="action.php?controller=command&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Commands</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <!-- Contact Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="ContactSection">
                <img src="static/imgs/plusSign.gif">
                Contact Related Settings
            </div>
            <div class="divHide parent-desc-ContactSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                    The following section will allow you to add, list, modify, and delete specific
                    contacts, contact groups, and contact templates...
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Contact Section -->
                    <div id="Contact">
                        <a class="menuItem" href="action.php?controller=contact&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Contacts</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Contact Group Section -->
                    <div id="ContactGroup">
                        <a class="menuItem" href="action.php?controller=contactgrp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Contact Groups</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Contact Template Section -->
                    <div id="ContactTemplate">
                        <a class="menuItem" href="action.php?controller=contacttemp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Contact Templates</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <!-- Host Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="HostSection">
                <img src="static/imgs/plusSign.gif">
                Host Related Settings
            </div>
            <div class="divHide parent-desc-HostSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                    The following section will allow you to add, list, modify, and delete specific
                    hosts, host groups, and host templates...
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Host Section -->
                    <!-- Hosts will be handled by CMDB right now
                    <div id="Host">
                        <a class="menuItem" href="action.php?controller=host&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Host</a>
                    </div>-->
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Host Group Section -->
                    <div id="HostGroup">
                        <a class="menuItem" href="action.php?controller=hostgrp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Host Groups</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Host Template Section -->
                    <div id="HostTemplate">
                        <a class="menuItem" href="action.php?controller=hosttemp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Host Templates</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <!-- Service Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="ServiceSection">
                <img src="static/imgs/plusSign.gif">
                Service Related Settings
            </div>
            <div class="divHide parent-desc-ServiceSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                The following section will allow you to add, list, modify, and delete
                services, service groups, service templates and service dependencies...
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Service Section -->
                    <div id="Service">
                        <a class="menuItem" href="action.php?controller=svc&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Services</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Service Group Section -->
                    <div id="ServiceGroup">
                        <a class="menuItem" href="action.php?controller=svcgrp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Service Groups</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Service Template Section -->
                    <div id="ServiceTemplate">
                        <a class="menuItem" href="action.php?controller=svctemp&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Service Templates</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Service Dependency Section -->
                    <div id="ServiceDependency">
                        <a class="menuItem" href="action.php?controller=svcdep&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Service Dependencies</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <!-- Service Escalation Section -->
                    <div id="ServiceEscalation">
                        <a class="menuItem" href="action.php?controller=svcesc&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Service Escalations</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <!-- Core Files Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="NagiosConfigsSection">
                <img src="static/imgs/plusSign.gif">
                Core Configuration Files
            </div>
            <div class="divHide parent-desc-NagiosConfigsSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                    The following section will allow you to control settings for the core
                    configuration files. Messing with the settings in this section can have
                    possible negative impacts, so please make sure you know what you are doing.
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Service Section -->
                    <div id="ResourceConfig">
                        <a class="menuItem" href="action.php?controller=resourcecfg&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Resource Config</a>
                    </div>
<?php
if ((isset($viewData->superuser)) && ($viewData->superuser === true)) {
?>
                    <div class="divCacTagGroup"></div>
                    <div id="CGIConfig">
                        <a class="menuItem" href="action.php?controller=cgicfg&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">CGI Config</a>
                    </div>
                    <div class="divCacTagGroup"></div>
                    <div id="NagiosConfig">
                        <a class="menuItem" href="action.php?controller=nagioscfg&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Nagios Config</a>
                    </div>
                    <div class="divCacTagGroup"></div>
                    <div id="ModgearmanConfig">
                        <a class="menuItem" href="action.php?controller=modgearmancfg&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Modgearman Config</a>
                    </div>
<?php
}
?>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <!-- Service Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="parentClass" id="NagiosPluginsSection">
                <img src="static/imgs/plusSign.gif">
                Nagios Cluster Plugins
            </div>
            <div class="divHide parent-desc-NagiosPluginsSection divCacResponse">
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="overview" class="divCacSubResponse">
                The following section will allow you to add, list, modify, and delete
                the custom plugins that are required to be present on a Nagios Cluster.
                </div>
                <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
                <div class="divCacSubResponse">
                    <!-- Service Section -->
                    <div id="Service">
                        <a class="menuItem" href="action.php?controller=nagiosplugin&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify<br />Delete / View</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Nagios Wrapper -->
<div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
<?php
}
if ($viewData->deploysettings['deploystyle'] != 'nagios') {
?>
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="NRPESection">
        <img src="static/imgs/plusSign.gif">
        NRPE Configuration
    </div>
    <div class="divHide parent-desc-NRPESection divCacSubResponse">
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div id="overview" class="divCacSubResponse">
            The following section is for defining NRPE Configuration Information
        </div>
        <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
        <!-- Timeperiod Section -->
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="childClass" id="NRPECommands">
                <img src="static/imgs/plusSign.gif">
                NRPE Commands
            </div>
            <div class="divHide child-desc-NRPECommands divCacResponse">
                <div id="NRPECommands-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpecmd&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify / Delete</a>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="childClass" id="NRPEConfig">
                <img src="static/imgs/plusSign.gif">
                NRPE Config File
            </div>
            <div class="divHide child-desc-NRPEConfig divCacResponse">
                <div id="NRPEConfig-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpecfg&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify / Delete</a>
                </div>
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="NRPEShowConfig" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpecfg&action=show&deployment=<?php echo $viewData->deployment?>" target="output">Show</a>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="childClass" id="NRPEPlugins">
                <img src="static/imgs/plusSign.gif">
                NRPE Plugins
            </div>
            <div class="divHide child-desc-NRPEPlugins divCacResponse">
                <div id="NRPEPlugin-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpeplugin&action=stage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify <br /> Delete / View</a>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="childClass" id="SupNRPEConfig">
                <img src="static/imgs/plusSign.gif">
                Supplemental NRPE Config File
            </div>
            <div class="divHide child-desc-SupNRPEConfig divCacResponse">
                <div id="SupNRPEConfig-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpecfg&action=supstage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify / Delete</a>
                </div>
                <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                <div id="NRPEShowSupConfig-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpecfg&action=supshow&deployment=<?php echo $viewData->deployment?>" target="output">Show</a>
                </div>
            </div>
        </div>
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div class="divCacGroup admin_box admin_box_blue admin_border_black">
            <div class="childClass" id="SupNRPEPlugins">
                <img src="static/imgs/plusSign.gif">
                Supplemental NRPE Plugins
            </div>
            <div class="divHide child-desc-SupNRPEPlugins divCacResponse">
                <div id="SupNRPEPlugin-btn" style="padding:2px 10px;">
                    <a class="menuItem" href="action.php?controller=nrpeplugin&action=sup_stage&deployment=<?php echo $viewData->deployment?>" target="output">Create / Modify <br /> Delete / View</a>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
<?php
}
if ($viewData->deploysettings['deploystyle'] != 'nrpe') {
?>
<!-- Begin Node Templatizer -->
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="NodeTemplatizer">
        <img src="static/imgs/plusSign.gif">
        Matrix Node Mapper
    </div>
    <div class="divhide parent-desc-NodeTemplatizer divCacResponse">
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div id="overview" class="divCacSubResponse">
            The following section is for building a template of information
            which contains, hostgroups, services, and other necessary information
            that we will then apply to nodes in either a dynamic or static method.
        </div>
        <div class="divCacGroup"></div>
        <div class="divCacSubResponse">
            <div id="apply">
                <a class="menuItem" href="action.php?controller=ngnt&action=manage&deployment=<?php echo $viewData->deployment?>" target="output">Manage Templates</a>
            </div>
        </div>
    </div>
</div>
<div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
<!-- End Node Templatizer -->
<?php
}
?>
<!-- Begin Nagios Generated Configs Section -->
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="DeploymentConfigsSection">
        <img src="static/imgs/plusSign.gif">
        Deployment Config Management
    </div>
    <div class="divHide parent-desc-DeploymentConfigsSection divCacResponse">
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div id="overview" class="divCacSubResponse">
            The following section is here so you can test / view your configuration files
            for Nagios
        </div>
        <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
        <div class="divCacSubResponse">
            <div id="ChgDeployment">
                <a class="menuItem" href="action.php?controller=deployment&action=modify_stage&deployment=<?php echo $viewData->deployment?>" target="output">Manage Deployment Info</a>
            </div>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
            <div id="ChgRevs">
                <a class="menuItem" href="action.php?controller=deployment&action=chg_configs&deployment=<?php echo $viewData->deployment?>" target="output">Change Deployed Revision</a>
            </div>
<?php
if ($viewData->deploysettings['deploystyle'] != 'nrpe') {
?>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
            <div id="ShowConfigs">
                <a class="menuItem" href="action.php?controller=deployment&action=show_configs_stage&deployment=<?php echo $viewData->deployment?>" target="output">Show Nagios Configs</a>
            </div>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
            <div id="TestConfigs">
                <a class="menuItem" href="action.php?controller=deployment&action=test_configs_stage&deployment=<?php echo $viewData->deployment?>" target="output">Test Nagios Configs</a>
            </div>
<?php
}
?>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
            <div id="DiffConfigs">
                <a class="menuItem" href="action.php?controller=deployment&action=diff_configs_stage&deployment=<?php echo $viewData->deployment?>" target="output">Diff Config Revisions</a>
            </div>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
            <div class="admin_border_black admin_box divCacGroup">
                <div class="childClass" id="mngRevs">
                    <img src="static/imgs/plusSign.gif">
                    Manage Revisions
                </div>
                <div class="child-desc-mngRevs divHide">
                    <div id="ResetFtrRev">
                        <a class="menuItem" href="action.php?controller=deployment&action=reset_ftr_rev&deployment=<?php echo $viewData->deployment?>" target="output">Reset Future Revision</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <div id="DeleteOldRevs">
                        <a class="menuItem" href="action.php?controller=deployment&action=del_rev_stage&deployment=<?php echo $viewData->deployment?>" target="output">Delete Old Revisions</a>
                    </div>
                    <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
                    <div id="ViewRevLog">
                        <a class="menuItem" href="action.php?controller=deployment&action=view_revlog&deployment=<?php echo $viewData->deployment?>" target="output">View Revisions Log</a>
                    </div>
                </div>
            </div>
            <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        </div>
    </div>  
</div>
<!-- End Nagios Generated Configs Section -->
<?php
if ((isset($viewData->superuser)) && ($viewData->superuser === true)) {
    if ($viewData->deployment != 'common') {
?>  
<!-- Begin Nagios Configurator Configuration Menu -->
<div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="ImportSection">
        <img src="static/imgs/plusSign.gif">
        Import Nagios Related Files
    </div>
    <div class="divHide parent-desc-ImportSection divCacResponse">
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div id="overview" class="divCacSubResponse">
            The following section is for importing pre-existing nagios related files into Saigon
        </div>
        <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
        <div class="divCacSubResponse">
            <div id="importnrpecfg">
                <a class="menuItem" href="action.php?controller=nrpecfg&action=import_stage&deployment=<?php echo $viewData->deployment?>" target="output">Import NRPE Config</a>
            </div>
            <div class="divCacTagGroup"></div>
            <div id="importsupnrpecfg">
                <a class="menuItem" href="action.php?controller=nrpecfg&action=sup_import_stage&deployment=<?php echo $viewData->deployment?>" target="output">Import Supplemental NRPE Config</a>
            </div>
        </div>
    </div>  
</div>
<?php
    }
?>
<!-- Begin Nagios Configurator Configuration Menu -->
<div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
<div class="divCacGroup admin_box admin_box_blue admin_border_black">
    <div class="parentClass" id="SaigonSection">
        <img src="static/imgs/plusSign.gif">
        Saigon Settings
    </div>
    <div class="divHide parent-desc-SaigonSection divCacResponse">
        <div class="divCacTagGroup"><!-- 1 Pixel Spacer --></div>
        <div id="overview" class="divCacSubResponse">
        The following section is for defining some of the core information for 
        the Nagios Configurator, this configuration has no impact directly on Nagios.
        </div>
        <div class="divCacGroup"><!-- 5 Pixel Spacer --></div>
        <div class="divCacSubResponse">
            <div id="Deployments">
                <a class="menuItem" href="action.php?controller=deployment&action=stage" target="output">Deployments</a>
            </div>
        </div>
    </div>  
</div>
<?php
}

require HTML_FOOTER;

