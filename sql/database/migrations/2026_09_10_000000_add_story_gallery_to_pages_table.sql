-- Our Story images on the About page are multiple images, each with a caption.
-- `custom_data` already stores the Team Members grid and `stats_counters` stores
-- the stat rows, so this gets its own JSON column: [{image_id, caption}, ...].
-- The image ids mirror into media_usages (context = story_gallery) so the Media
-- Library can track/refuse deletion while an image is in use.

-- Safe to run more than once: skipped when the column already exists.
SET @col_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS
                    WHERE TABLE_SCHEMA = DATABASE()
                      AND TABLE_NAME   = 'pages'
                      AND COLUMN_NAME  = 'story_gallery');

SET @sql := IF(@col_exists = 0,
  'ALTER TABLE `pages` ADD COLUMN `story_gallery` JSON NULL AFTER `custom_data`',
  'SELECT "column story_gallery already exists" AS notice');

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;