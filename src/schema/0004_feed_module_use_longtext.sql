ALTER TABLE `feed_data` MODIFY `feed_d_data` LONGTEXT NOT NULL;

--//@UNDO

ALTER TABLE `feed_data` MODIFY `feed_d_data` TEXT NULL;
