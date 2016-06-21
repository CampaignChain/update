# deployment-update
Update script

If you already have a CampaignChain installation, please run first this SQL query:

```sql


SET NAMES utf8;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `migration_versions`;
CREATE TABLE `migration_versions` (
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `migration_versions` (`version`) VALUES
('20160621000000'),
('20160621000001'),
('20160621000002'),
('20160621000003'),
('20160621000004'),
('20160621000005'),
('20160621000006'),
('20160621000007'),
('20160621000008'),
('20160621000009'),
('20160621000010'),
('20160621000011'),
('20160621000012'),
('20160621000013'),
('20160621000014'),
('20160621000015'),
('20160621000016'),
('20160621000017'),
('20160621000018'),
('20160621000019'),
('20160621000020'),
('20160621000021'),
('20160621000022');

```