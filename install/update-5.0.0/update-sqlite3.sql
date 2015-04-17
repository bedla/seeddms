BEGIN;

ALTER TABLE tblUsers ADD COLUMN `homefolder` INTEGER DEFAULT 0;

CREATE TABLE `tblDocumentCheckOuts` (
  `document` INTEGER REFERENCES `tblDocuments` (`id`) ON DELETE CASCADE,
  `userID` INTEGER NOT NULL default '0' REFERENCES `tblUsers` (`id`)
  `version` INTEGER unsigned NOT NULL default '0',
  `date` TEXT NOT NULL default '0000-00-00 00:00:00',
  `filename` varchar(255) NOT NULL default '',
  UNIQUE (`document`)
) ;

UPDATE tblVersion set major=5, minor=0, subminor=0;

COMMIT;


