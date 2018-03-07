INSERT IGNORE INTO `#__rsform_config` (`SettingName`, `SettingValue`) VALUES('postcodeapi.code', '');

CREATE TABLE IF NOT EXISTS `#__rsform_postcodeapi` (
  `form_id` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`form_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;