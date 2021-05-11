CREATE TABLE `projects` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trello_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `todoist_id` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tasks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `due_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `trello_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trello_status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'incomplete',
  `trello_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `trello_card_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trello_checklist` tinyint(1) NOT NULL DEFAULT 0,
  `trello_checklist_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `todoist_id` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `todoist_status` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT 'incomplete',
  `todoist_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
