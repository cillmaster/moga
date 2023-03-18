CREATE TABLE IF NOT EXISTS `pages_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `locale` varchar(5) NOT NULL,
  `published` tinyint(1) NOT NULL,
  `caption` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `text` longtext NOT NULL,
  `meta_keywords` varchar(255) NOT NULL,
  `meta_description` varchar(255) NOT NULL,
  `theme` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `pages_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(100) NOT NULL,
  `preg` varchar(100) NOT NULL,
  `title` varchar(100) NOT NULL,
  `css_class` varchar(50) NOT NULL,
  `parent_id` int(11) NOT NULL,
  `sort` int(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;