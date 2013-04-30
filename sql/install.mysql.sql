DROP TABLE if exists `#__feed2post` ;
DROP TABLE if exists `#__feed2post_queue`;
DROP TABLE if exists `#__feed2post_config`;
CREATE TABLE `#__feed2post` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `published` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `feed_url` text NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `keywords` varchar(255) NOT NULL,
  `checked_out` int(11) NOT NULL,
  `checked_out_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `advert` mediumtext NOT NULL,
  `fulltext` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(100) NOT NULL DEFAULT 'Not set',
  `iframelinks` tinyint(1) NOT NULL DEFAULT '1',
  `negkey` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(40) NOT NULL,
  `cutat` varchar(40) NOT NULL,
  `cutatcharacter` int(11) NOT NULL,
  `minkeylen` int(11) NOT NULL,
  `keycount` int(11) NOT NULL,
  `ignoreitem` int(11) NOT NULL,
  `minimum_count` int(11) NOT NULL,
  `height` varchar(4) NOT NULL,
  `width` varchar(4) NOT NULL,
  `marginheight` tinyint(4) NOT NULL,
  `marginwidth` tinyint(4) NOT NULL,
  `scrolling` varchar(6) NOT NULL,
  `frameborder` tinyint(4) NOT NULL,
  `align` varchar(6) NOT NULL,
  `allowabletags` varchar(40) NOT NULL,
  `iframeclass` varchar(40) NOT NULL,
  `parser` varchar(250) NOT NULL,
  `storage` varchar(250) NOT NULL,
  `storeoptions` mediumtext NOT NULL,
  `parseroptions` varchar(255) NOT NULL,
  `includelink` tinyint(1) NOT NULL DEFAULT '0',
  `replaceimgs` tinyint(1) NOT NULL DEFAULT '0',
  `maxitems` tinyint(4) NOT NULL DEFAULT '0',
  `truncate` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Feeds and data for feed2post';

CREATE TABLE `#__feed2post_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT 'image',
  `done` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Feed2post grabbing queue';

CREATE TABLE `#__feed2post_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '',
  `values` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `onlyone` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Feed2post options';

INSERT INTO `#__feed2post_config` (`id`, `name`, `values`) VALUES
(1, 'basicsettings', '{"getwith":0,"insertadvert":1,"includelink":0,"dupavoid":128,"imagefolder":"media/feed2post/","imageretries":1}'),
(2, 'defaults', '{"validfor":-1,"advert":"","autounpublish":0,"frontpage":0,"fulltext":0,"posterid":63,"origdate":0,"iframelinks":1,"createddate":0,"showintro":1,"origauthor":2,"acgroup":0,"published":1,"sectionid":0,"catid":0,"intrometa":1,"ignoreitem":0,"height":"400","width":"100%","marginheight":0,"marginwidth":0,"scrolling":"auto","frameborder":0,"align":"bottom","allowabletags":"","includelink":1,"replaceimgs":0}');