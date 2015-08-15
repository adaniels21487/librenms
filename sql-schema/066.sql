DROP TABLE IF EXISTS `component`;
CREATE TABLE `component` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each component, unique index', `device_id` int(11) unsigned NOT NULL COMMENT 'device_id from the devices table', `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'name from the component_type table', `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Display label for the component', `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'The status of the component, retreived from the device', `disabled` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Should this component be polled', `ignore` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Should this component be alerted on', PRIMARY KEY (`id`), KEY `device` (`device_id`), KEY `type` (`type`) ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='components attached to a device.';
DROP TABLE IF EXISTS `component_prefs`;
CREATE TABLE `component_prefs` ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID for each entry', `component` int(11) unsigned NOT NULL COMMENT 'id from the component table', `attribute` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Attribute for the Component', `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Value for the Component', PRIMARY KEY (`id`), KEY `component` (`component`), CONSTRAINT `component_prefs_ibfk_1` FOREIGN KEY (`component`) REFERENCES `component` (`id`) ON DELETE CASCADE ON UPDATE CASCADE ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='AV Pairs for each component';
INSERT INTO `alert_rules` (`device_id`,`rule`,`severity`,`extra`,`disabled`,`name`) VALUES ('-1','%macros.component_alarm = \"1\"','critical','{\"mute\":false,\"count\":\"-1\",\"delay\":\"300\"}',0,'Component Alarm');
INSERT INTO `config` (`config_name`,`config_value`,`config_default`,`config_descr`,`config_group`,`config_group_order`,`config_sub_group`,`config_sub_group_order`,`config_hidden`,`config_disabled`) VALUES ('alert.macros.rule.component','(%component.disabled = 0 && %component.ignore = 0)','(%component.disabled = 0 && %component.ignore = 0)','Component that isn\'t disabled or ignored','alerting',0,'macros',0,'1','0'),('alert.macros.rule.component_normal','(%component.status = 1 && %macros.component)','(%component.status = 1 && %macros.component)','Component that is in a normal state','alerting',0,'macros',0,'1','0'),('alert.macros.rule.component_alarm','(%component.status = 0 && %macros.component)','(%component.status = 0 && %macros.component)','Component that alarming','alerting',0,'macros',0,'1','0');
