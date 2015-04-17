START TRANSACTION;

ALTER TABLE tblUsers ADD COLUMN `homefolder` INTEGER DEFAULT 0;

CREATE TABLE `tblDocumentCheckOuts` (
  `document` int(11) NOT NULL default '0',
  `version` smallint(5) unsigned NOT NULL default '0',
  `userID` int(11) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `filename` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`document`),
  CONSTRAINT `tblDocumentCheckOuts_document` FOREIGN KEY (`document`) REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tblDocumentCheckOuts_user` FOREIGN KEY (`userID`) REFERENCES `tblUsers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

UPDATE tblVersion set major=5, minor=0, subminor=0;

COMMIT;

