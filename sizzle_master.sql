-- =========================================================
-- SIZZLE & SHARE — Master Database File
-- Cook, Capture, Challenge · Share Your Culinary Magic!
-- Run this file once to create the full schema + seed data.
-- =========================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ---------------------------------------------------------
-- Create and select database
-- ---------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `sizzle_share`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE `sizzle_share`;

-- ---------------------------------------------------------
-- Drop tables in reverse dependency order (safe re-run)
-- ---------------------------------------------------------
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `challenge_submissions`;
DROP TABLE IF EXISTS `comments`;
DROP TABLE IF EXISTS `likes`;
DROP TABLE IF EXISTS `followers`;
DROP TABLE IF EXISTS `recipe_ingredients`;
DROP TABLE IF EXISTS `user_uploads`;
DROP TABLE IF EXISTS `challenges`;
DROP TABLE IF EXISTS `ingredients`;
DROP TABLE IF EXISTS `recipes`;
DROP TABLE IF EXISTS `user_sessions`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- 1. USERS
-- =========================================================
CREATE TABLE `users` (
    `user_id`                  INT(11)       NOT NULL AUTO_INCREMENT,
    `full_name`                VARCHAR(100)  NOT NULL,
    `username`                 VARCHAR(50)   NOT NULL,
    `email`                    VARCHAR(100)  NOT NULL,
    `password_hash`            VARCHAR(255)  NOT NULL,
    `bio`                      TEXT          DEFAULT NULL,
    `profile_picture`          VARCHAR(255)  DEFAULT NULL
                                             COMMENT 'Filename in uploads/profile-pictures/',
    `total_uploads`            INT(11)       NOT NULL DEFAULT 0
                                             CHECK (`total_uploads` >= 0),
    `total_likes_received`     INT(11)       NOT NULL DEFAULT 0
                                             CHECK (`total_likes_received` >= 0),
    `total_challenges_joined`  INT(11)       NOT NULL DEFAULT 0
                                             CHECK (`total_challenges_joined` >= 0),
    `total_challenges_won`     INT(11)       NOT NULL DEFAULT 0
                                             CHECK (`total_challenges_won` >= 0),
    `role`                     ENUM('user','admin') NOT NULL DEFAULT 'user',
    `is_active`                TINYINT(1)    NOT NULL DEFAULT 1,
    `created_at`               TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`               TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`user_id`),
    UNIQUE KEY `uq_username` (`username`),
    UNIQUE KEY `uq_email`    (`email`),
    KEY `idx_role`            (`role`),
    KEY `idx_is_active`       (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Platform users including admins';

-- =========================================================
-- 2. PASSWORD RESET TOKENS
-- =========================================================
CREATE TABLE `password_resets` (
    `reset_id`    INT(11)      NOT NULL AUTO_INCREMENT,
    `email`       VARCHAR(100) NOT NULL,
    `token`       VARCHAR(255) NOT NULL,
    `expires_at`  DATETIME     NOT NULL,
    `used`        TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`reset_id`),
    UNIQUE KEY `uq_token`  (`token`),
    KEY `idx_email`        (`email`),
    KEY `idx_expires_at`   (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='One-time password reset tokens';

-- =========================================================
-- 3. USER SESSIONS
-- =========================================================
CREATE TABLE `user_sessions` (
    `session_id`     INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`        INT(11)      NOT NULL,
    `session_token`  VARCHAR(255) NOT NULL,
    `ip_address`     VARCHAR(45)  DEFAULT NULL,
    `user_agent`     TEXT         DEFAULT NULL,
    `last_activity`  DATETIME     DEFAULT NULL,
    `is_active`      TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`session_id`),
    UNIQUE KEY `uq_session_token` (`session_token`),
    KEY `idx_user_id`   (`user_id`),
    KEY `idx_is_active` (`is_active`),
    CONSTRAINT `fk_sessions_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Active login sessions per user';

-- =========================================================
-- 4. RECIPES
-- =========================================================
CREATE TABLE `recipes` (
    `recipe_id`    INT(11)                        NOT NULL AUTO_INCREMENT,
    `recipe_name`  VARCHAR(100)                   NOT NULL,
    `description`  TEXT                           DEFAULT NULL,
    `cooking_time` INT(11)                        DEFAULT NULL
                   COMMENT 'Cooking time in minutes'
                   CHECK (`cooking_time` > 0),
    `difficulty`   ENUM('Easy','Medium','Hard')   NOT NULL DEFAULT 'Medium',
    `instructions` TEXT                           DEFAULT NULL,
    `image_path`   VARCHAR(255)                   DEFAULT NULL
                   COMMENT 'Filename in uploads/recipe-images/',
    `total_likes`  INT(11)                        NOT NULL DEFAULT 0
                   CHECK (`total_likes` >= 0),
    `created_at`   TIMESTAMP                      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`recipe_id`),
    KEY `idx_difficulty`  (`difficulty`),
    KEY `idx_total_likes` (`total_likes`),
    FULLTEXT KEY `ft_recipe_search` (`recipe_name`, `description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Curated recipe library';

-- =========================================================
-- 5. INGREDIENTS
-- =========================================================
CREATE TABLE `ingredients` (
    `ingredient_id`    INT(11)     NOT NULL AUTO_INCREMENT,
    `ingredient_name`  VARCHAR(50) NOT NULL,
    `image_path`       VARCHAR(255) DEFAULT NULL
                       COMMENT 'Filename in uploads/ingredient-images/',
    `category`         VARCHAR(50)  DEFAULT NULL,
    `created_at`       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ingredient_id`),
    UNIQUE KEY `uq_ingredient_name` (`ingredient_name`),
    KEY `idx_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Master ingredient catalog';

-- =========================================================
-- 6. RECIPE ↔ INGREDIENTS (Junction)
-- =========================================================
CREATE TABLE `recipe_ingredients` (
    `recipe_id`      INT(11)     NOT NULL,
    `ingredient_id`  INT(11)     NOT NULL,
    `quantity`       VARCHAR(50) DEFAULT NULL,
    `unit`           VARCHAR(20) DEFAULT NULL,
    PRIMARY KEY (`recipe_id`, `ingredient_id`),
    KEY `idx_ri_ingredient` (`ingredient_id`),
    CONSTRAINT `fk_ri_recipe`
        FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_ri_ingredient`
        FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`ingredient_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Many-to-many: recipes and their ingredients';

-- =========================================================
-- 7. USER UPLOADS
-- =========================================================
CREATE TABLE `user_uploads` (
    `upload_id`    INT(11)      NOT NULL AUTO_INCREMENT,
    `user_id`      INT(11)      NOT NULL,
    `recipe_id`    INT(11)      DEFAULT NULL
                   COMMENT 'NULL when upload is not linked to a known recipe',
    `dish_name`    VARCHAR(100) NOT NULL,
    `description`  TEXT         DEFAULT NULL,
    `image_path`   VARCHAR(255) DEFAULT NULL
                   COMMENT 'Filename in uploads/',
    `total_likes`  INT(11)      NOT NULL DEFAULT 0
                   CHECK (`total_likes` >= 0),
    `created_at`   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`upload_id`),
    KEY `idx_uu_user_id`    (`user_id`),
    KEY `idx_uu_recipe_id`  (`recipe_id`),
    KEY `idx_uu_created_at` (`created_at`),
    KEY `idx_uu_likes`      (`total_likes`),
    CONSTRAINT `fk_uu_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_uu_recipe`
        FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`recipe_id`)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User-submitted dish photos';

-- =========================================================
-- 8. LIKES
-- =========================================================
CREATE TABLE `likes` (
    `like_id`    INT(11)   NOT NULL AUTO_INCREMENT,
    `user_id`    INT(11)   NOT NULL,
    `upload_id`  INT(11)   NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`like_id`),
    UNIQUE KEY `uq_like` (`user_id`, `upload_id`)
                COMMENT 'One like per user per upload',
    KEY `idx_like_upload` (`upload_id`),
    CONSTRAINT `fk_like_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_like_upload`
        FOREIGN KEY (`upload_id`) REFERENCES `user_uploads` (`upload_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Upload likes; enforces one like per user per post';

-- =========================================================
-- 9. COMMENTS
-- =========================================================
CREATE TABLE `comments` (
    `comment_id`   INT(11)   NOT NULL AUTO_INCREMENT,
    `upload_id`    INT(11)   NOT NULL,
    `user_id`      INT(11)   NOT NULL,
    `comment_text` TEXT      NOT NULL,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`comment_id`),
    KEY `idx_comment_upload`  (`upload_id`),
    KEY `idx_comment_user`    (`user_id`),
    KEY `idx_comment_created` (`created_at`),
    CONSTRAINT `fk_comment_upload`
        FOREIGN KEY (`upload_id`) REFERENCES `user_uploads` (`upload_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_comment_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Comments on user uploads';

-- =========================================================
-- 10. CHALLENGES
-- =========================================================
CREATE TABLE `challenges` (
    `challenge_id`  INT(11)      NOT NULL AUTO_INCREMENT,
    `title`         VARCHAR(100) NOT NULL,
    `description`   TEXT         DEFAULT NULL,
    `start_date`    DATE         DEFAULT NULL,
    `end_date`      DATE         DEFAULT NULL,
    `prize`         VARCHAR(255) DEFAULT NULL,
    `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`challenge_id`),
    KEY `idx_ch_is_active`  (`is_active`),
    KEY `idx_ch_end_date`   (`end_date`),
    CONSTRAINT `chk_challenge_dates`
        CHECK (`end_date` IS NULL OR `start_date` IS NULL OR `end_date` >= `start_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Weekly cooking challenges';

-- =========================================================
-- 11. CHALLENGE SUBMISSIONS
-- =========================================================
CREATE TABLE `challenge_submissions` (
    `submission_id`  INT(11)      NOT NULL AUTO_INCREMENT,
    `challenge_id`   INT(11)      NOT NULL,
    `user_id`        INT(11)      NOT NULL,
    `dish_name`      VARCHAR(100) NOT NULL,
    `description`    TEXT         DEFAULT NULL,
    `image_path`     VARCHAR(255) DEFAULT NULL,
    `votes`          INT(11)      NOT NULL DEFAULT 0
                     CHECK (`votes` >= 0),
    `created_at`     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`submission_id`),
    UNIQUE KEY `uq_challenge_user` (`challenge_id`, `user_id`)
              COMMENT 'One submission per user per challenge',
    KEY `idx_cs_user_id` (`user_id`),
    KEY `idx_cs_votes`   (`votes`),
    CONSTRAINT `fk_cs_challenge`
        FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`challenge_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_cs_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User entries for a cooking challenge';

-- =========================================================
-- 12. NOTIFICATIONS
-- =========================================================
CREATE TABLE `notifications` (
    `notification_id`  INT(11)   NOT NULL AUTO_INCREMENT,
    `user_id`          INT(11)   NOT NULL,
    `message`          TEXT      NOT NULL,
    `type`             ENUM('info','success','warning','error') NOT NULL DEFAULT 'info',
    `is_read`          TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`       TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`notification_id`),
    KEY `idx_notif_user`    (`user_id`),
    KEY `idx_notif_is_read` (`is_read`),
    CONSTRAINT `fk_notif_user`
        FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='In-app notifications per user';

-- =========================================================
-- 13. FOLLOWERS (Social)
-- =========================================================
CREATE TABLE `followers` (
    `follower_id`   INT(11)   NOT NULL
                    COMMENT 'The user who follows',
    `following_id`  INT(11)   NOT NULL
                    COMMENT 'The user being followed',
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`follower_id`, `following_id`),
    KEY `idx_following` (`following_id`),
    CONSTRAINT `chk_no_self_follow`
        CHECK (`follower_id` <> `following_id`),
    CONSTRAINT `fk_follower_user`
        FOREIGN KEY (`follower_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_following_user`
        FOREIGN KEY (`following_id`) REFERENCES `users` (`user_id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='User follow relationships';

-- =========================================================
-- SAMPLE DATA
-- All passwords are "password123"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- =========================================================

-- ---------------------------------------------------------
-- Users
-- ---------------------------------------------------------
INSERT INTO `users`
    (`user_id`, `full_name`, `username`, `email`, `password_hash`, `bio`,
     `total_uploads`, `total_likes_received`, `total_challenges_joined`,
     `total_challenges_won`, `role`, `is_active`)
VALUES
(1,  'Administrator',  'admin',    'admin@sizzle.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Platform administrator',                0,  0,    0, 0, 'admin', 1),
(2,  'Test User',      'testuser', 'test@example.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Food lover and home chef!',             5,  127,  0, 0, 'user',  1),
(3,  'Sarah Johnson',  'sarah',    'sarah@example.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Professional chef and food blogger',   45, 2340, 0, 0, 'user',  1),
(4,  'Ahmed Khan',     'ahmed',    'ahmed@example.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Spice lover and home cook',            38, 1890, 0, 0, 'user',  1),
(5,  'Priya Sharma',   'priya',    'priya@example.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Vegetarian recipe creator',            32, 1560, 0, 0, 'user',  1),
(6,  'Fatima Tahir',   'fatima',   'fatima@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Grill master',                         28,  980, 0, 0, 'user',  1),
(7,  'Amna Luqman',    'amna',     'amna@gmail.com',        '$2y$10$y3KTV9Mfk.HMk/JmLvzyjeVywa66pwwTuYqr3jKwX6N2R.2u.IIpG', 'Lead Developer | UI Specialist',        0,    0, 0, 0, 'admin', 1),
(8,  'Arooj Fatima',   'arooj',    'arooj@gmail.com',       '$2y$10$y3KTV9Mfk.HMk/JmLvzyjeVywa66pwwTuYqr3jKwX6N2R.2u.IIpG', 'Backend Developer | DB Specialist',     0,    0, 0, 0, 'admin', 1),
(9,  'Nayyab',         'nayyab',   'nayyab@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL,                                    0,    0, 0, 0, 'user',  1),
(10, 'Manahil Jamil',  'manahil',  'manahiljamil@gmail.com','$2y$10$KZIbINdkkVJpjKeM5sxwBeuLqB2ADPv5hOuex9kuOFIL6V33pz67O', NULL,                                    4,    0, 0, 0, 'user',  1);

-- ---------------------------------------------------------
-- Recipes
-- ---------------------------------------------------------
INSERT INTO `recipes`
    (`recipe_id`, `recipe_name`, `description`, `cooking_time`, `difficulty`, `instructions`, `image_path`, `total_likes`)
VALUES
(1, 'Spaghetti Carbonara',  'Classic Italian pasta with creamy egg and cheese sauce',    25, 'Medium', '1. Boil pasta in salted water\n2. Fry pancetta until crispy\n3. Whisk eggs and Parmesan\n4. Combine pasta with egg mix off-heat\n5. Add pancetta and serve immediately',              'spaghetti carbonara.jfif', 1200),
(2, 'Margherita Pizza',     'Simple pizza with tomato, mozzarella, and basil',           40, 'Hard',   '1. Make pizza dough and let rise 1 hr\n2. Spread tomato sauce\n3. Add fresh mozzarella slices\n4. Top with fresh basil\n5. Bake at 220°C (fan) for 12-15 min',                       NULL,                       980),
(3, 'Greek Salad',          'Fresh Mediterranean salad with feta and olives',             15, 'Easy',   '1. Chop tomatoes, cucumber and red onion\n2. Add kalamata olives and feta cheese\n3. Drizzle extra virgin olive oil\n4. Season with dried oregano and salt to taste',               NULL,                      2100),
(4, 'Chicken Stir Fry',     'Quick healthy chicken stir fry with colourful vegetables',  20, 'Medium', '1. Slice chicken breast into thin strips\n2. Stir fry in hot wok until golden\n3. Add bell peppers, broccoli and carrots\n4. Deglaze with soy sauce, ginger and garlic\n5. Toss and serve over steamed rice', NULL, 1500),
(5, 'Classic Omelette',     'Fluffy French-style omelette perfect for breakfast',         10, 'Easy',   '1. Beat 3 eggs with salt and pepper\n2. Melt butter in a non-stick pan over medium heat\n3. Pour in eggs; swirl pan gently\n4. Add cheese or vegetables of choice\n5. Fold in half and slide onto plate', 'omelett.jfif', 800),
(6, 'Garlic Butter Rice',   'Simple and aromatic rice side dish',                         20, 'Easy',   '1. Sauté 4 minced garlic cloves in 2 tbsp butter until golden\n2. Add rinsed rice and stir to coat\n3. Pour in chicken or vegetable broth (2:1 ratio)\n4. Cover and simmer 18 min\n5. Fluff with a fork and garnish with parsley', 'garlic-butter-rice.jfif', 650),
(7, 'Chocolate Cake',       'Rich moist chocolate cake with silky chocolate frosting',    60, 'Hard',   '1. Whisk flour, cocoa powder, baking soda and salt\n2. Cream butter and sugar until pale and fluffy\n3. Beat in eggs one at a time\n4. Alternate adding dry mix and buttermilk\n5. Bake at 175°C for 30-35 min; cool before frosting', 'chocolate-cake.jfif', 450),
(8, 'Chicken Curry',        'Aromatic Indian-style chicken curry with basmati',           45, 'Medium', '1. Sauté diced onions, ginger and garlic until caramelised\n2. Add cumin, coriander, garam masala and turmeric\n3. Brown chicken pieces\n4. Add chopped tomatoes and simmer 20 min\n5. Finish with single cream and fresh coriander', 'chicken-stir-fry.jfif', 1100);

-- ---------------------------------------------------------
-- Ingredients
-- ---------------------------------------------------------
INSERT INTO `ingredients` (`ingredient_id`, `ingredient_name`, `category`) VALUES
( 1, 'Tomato',       'Vegetables'),
( 2, 'Onion',        'Vegetables'),
( 3, 'Garlic',       'Herbs & Spices'),
( 4, 'Chicken',      'Meat'),
( 5, 'Eggs',         'Dairy'),
( 6, 'Milk',         'Dairy'),
( 7, 'Flour',        'Grains'),
( 8, 'Rice',         'Grains'),
( 9, 'Pasta',        'Grains'),
(10, 'Cheese',       'Dairy'),
(11, 'Potato',       'Vegetables'),
(12, 'Carrot',       'Vegetables'),
(13, 'Bell Pepper',  'Vegetables'),
(14, 'Olive Oil',    'Oils'),
(15, 'Salt',         'Seasonings'),
(16, 'Black Pepper', 'Seasonings'),
(17, 'Basil',        'Herbs & Spices'),
(18, 'Butter',       'Dairy'),
(19, 'Sugar',        'Sweeteners'),
(20, 'Beef',         'Meat'),
(21, 'Cumin',        'Herbs & Spices'),
(22, 'Coriander',    'Herbs & Spices'),
(23, 'Ginger',       'Herbs & Spices'),
(24, 'Soy Sauce',    'Condiments'),
(25, 'Baking Soda',  'Baking'),
(26, 'Cocoa Powder', 'Baking'),
(27, 'Broccoli',     'Vegetables'),
(28, 'Pancetta',     'Meat'),
(29, 'Mozzarella',   'Dairy'),
(30, 'Heavy Cream',  'Dairy');

-- ---------------------------------------------------------
-- Recipe ↔ Ingredients
-- ---------------------------------------------------------
INSERT INTO `recipe_ingredients` (`recipe_id`, `ingredient_id`, `quantity`, `unit`) VALUES
-- Spaghetti Carbonara (1)
(1,  9,  '200', 'g'),
(1,  5,  '2',   'pcs'),
(1,  10, '50',  'g'),
(1,  28, '100', 'g'),
(1,  3,  '2',   'cloves'),
(1,  2,  '1',   'medium'),
-- Margherita Pizza (2)
(2,  7,  '250', 'g'),
(2,  1,  '2',   'pcs'),
(2,  29, '150', 'g'),
(2,  14, '2',   'tbsp'),
(2,  17, '10',  'leaves'),
-- Greek Salad (3)
(3,  1,  '2',   'pcs'),
(3,  2,  '1',   'medium'),
(3,  10, '100', 'g'),
(3,  14, '3',   'tbsp'),
-- Chicken Stir Fry (4)
(4,  4,  '300', 'g'),
(4,  2,  '1',   'medium'),
(4,  3,  '3',   'cloves'),
(4,  13, '1',   'large'),
(4,  14, '2',   'tbsp'),
(4,  24, '3',   'tbsp'),
(4,  23, '1',   'tsp'),
(4,  27, '150', 'g'),
-- Classic Omelette (5)
(5,  5,  '3',   'pcs'),
(5,  10, '30',  'g'),
(5,  18, '1',   'tbsp'),
-- Garlic Butter Rice (6)
(6,  8,  '200', 'g'),
(6,  3,  '4',   'cloves'),
(6,  18, '2',   'tbsp'),
-- Chocolate Cake (7)
(7,  7,  '200', 'g'),
(7,  26, '50',  'g'),
(7,  19, '150', 'g'),
(7,  18, '100', 'g'),
(7,  5,  '3',   'pcs'),
(7,  6,  '100', 'ml'),
(7,  25, '1',   'tsp'),
-- Chicken Curry (8)
(8,  4,  '400', 'g'),
(8,  1,  '3',   'pcs'),
(8,  2,  '2',   'medium'),
(8,  3,  '4',   'cloves'),
(8,  23, '2',   'cm'),
(8,  21, '1',   'tsp'),
(8,  22, '1',   'tsp'),
(8,  30, '100', 'ml');

-- ---------------------------------------------------------
-- User Uploads
-- ---------------------------------------------------------
INSERT INTO `user_uploads`
    (`upload_id`, `user_id`, `recipe_id`, `dish_name`, `description`, `image_path`, `total_likes`)
VALUES
(1,  3, 1, 'My Perfect Carbonara',        'Creamy and delicious! Used guanciale instead of pancetta',               'spaghetti carbonara.jfif', 234),
(2,  3, 2, 'Homemade Margherita',          'Made the dough from scratch — best pizza I have ever had',               'margherita-pizza.jfif',    189),
(3,  4, 8, 'Mum''s Chicken Curry',         'Family recipe passed down three generations',                            'chicken-stir-fry.jfif',    312),
(4,  5, 3, 'Fresh Greek Salad',            'Used farm-fresh tomatoes — absolutely delicious',                        'greek-salad.jfif',         167),
(5,  4, 4, 'Chicken Stir Fry Deluxe',      'Added cashews for extra crunch!',                                       'chicken-stir-fry.jfif',    145),
(6,  5, 5, 'Sunday Morning Omelette',      'Mushroom, spinach and goat cheese — perfection',                        'omelette.jfif',            98),
(7,  6, 6, 'Garlic Rice My Way',           'Added fresh herbs from the garden',                                     'garlic-butter-rice.jfif',  76),
(8,  2, 7, 'Birthday Chocolate Cake',      'Made for my daughter — she loved it!',                                  'chocolate-cake.jfif',      201),
(9,  3, NULL, 'Lemon Drizzle Cake',        'My own recipe — not in the books yet!',                                  NULL,                       88),
(10, 10, 4, 'Quick Weeknight Stir Fry',   'Ready in 15 minutes — perfect after a long day',                         NULL,                       42);

-- ---------------------------------------------------------
-- Likes
-- ---------------------------------------------------------
INSERT INTO `likes` (`like_id`, `user_id`, `upload_id`) VALUES
( 1, 2,  1), ( 2, 2,  2), ( 3, 3,  3), ( 4, 3,  4),
( 5, 4,  1), ( 6, 4,  5), ( 7, 5,  2), ( 8, 5,  6),
( 9, 6,  1), (10, 6,  8), (11, 7,  3), (12, 7,  9),
(13, 8,  4), (14, 9,  8), (15, 10, 1), (16, 10, 7);

-- ---------------------------------------------------------
-- Challenges
-- ---------------------------------------------------------
INSERT INTO `challenges`
    (`challenge_id`, `title`, `description`, `start_date`, `end_date`, `prize`, `is_active`)
VALUES
(1, 'Pasta Paradise',      'Create the most delicious pasta dish — any style, any shape!',          CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7  DAY), 'Premium Cooking Kit + Featured Chef Badge', 1),
(2, 'Vegan Delight',       'Show us your best plant-based masterpiece. No animal products allowed!', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'Organic Spice Box + Recipe Book',           1),
(3, 'Quick 15-Min Meals',  'Can you make something amazing in just 15 minutes? Prove it!',          CURDATE(), DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'Kitchen Timer Set + Personalised Apron',    1),
(4, 'Baking Masters',      'Sweet or savoury — impress the community with your baking skills.',     CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'Stand Mixer + Baking Accessory Bundle',     1);

-- ---------------------------------------------------------
-- Notifications (sample)
-- ---------------------------------------------------------
INSERT INTO `notifications` (`user_id`, `message`, `type`, `is_read`) VALUES
(2, 'Welcome to Sizzle & Share! Start by uploading your first dish.', 'success', 0),
(3, 'Your upload "My Perfect Carbonara" received 10 new likes!',      'info',    1),
(4, 'Mum''s Chicken Curry is trending in the gallery today!',         'success', 0),
(5, 'A new challenge "Pasta Paradise" just started — enter now!',     'info',    0);

-- ---------------------------------------------------------
-- Followers (sample relationships)
-- ---------------------------------------------------------
INSERT INTO `followers` (`follower_id`, `following_id`) VALUES
(2, 3), (2, 4), (3, 4), (4, 3),
(5, 3), (6, 4), (7, 3), (10, 3);

-- =========================================================
-- COMMIT
-- =========================================================
COMMIT;

-- =========================================================
-- USEFUL QUERIES FOR REFERENCE
-- =========================================================

-- Leaderboard: top chefs by likes received
-- SELECT user_id, full_name, username, total_uploads, total_likes_received
-- FROM users WHERE is_active = 1 AND role = 'user'
-- ORDER BY total_likes_received DESC LIMIT 10;

-- Gallery feed: recent uploads with author info
-- SELECT u.upload_id, u.dish_name, u.description, u.image_path, u.total_likes,
--        u.created_at, us.username, us.full_name
-- FROM user_uploads u
-- JOIN users us ON u.user_id = us.user_id
-- ORDER BY u.created_at DESC LIMIT 20;

-- Active challenges with submission counts
-- SELECT c.challenge_id, c.title, c.end_date, c.prize,
--        COUNT(cs.submission_id) AS total_entries
-- FROM challenges c
-- LEFT JOIN challenge_submissions cs ON cs.challenge_id = c.challenge_id
-- WHERE c.is_active = 1 AND c.end_date >= CURDATE()
-- GROUP BY c.challenge_id ORDER BY c.end_date ASC;

-- Recipe search by ingredient (multi-ingredient intersection)
-- SELECT DISTINCT r.recipe_id, r.recipe_name, r.difficulty, r.cooking_time
-- FROM recipes r
-- JOIN recipe_ingredients ri ON ri.recipe_id = r.recipe_id
-- JOIN ingredients i ON i.ingredient_id = ri.ingredient_id
-- WHERE i.ingredient_name IN ('Chicken', 'Garlic')
-- GROUP BY r.recipe_id
-- HAVING COUNT(DISTINCT i.ingredient_name) = 2;

-- User profile summary
-- SELECT u.user_id, u.full_name, u.username, u.bio,
--        u.total_uploads, u.total_likes_received,
--        u.total_challenges_joined, u.total_challenges_won,
--        (SELECT COUNT(*) FROM followers f WHERE f.following_id = u.user_id) AS followers_count,
--        (SELECT COUNT(*) FROM followers f WHERE f.follower_id  = u.user_id) AS following_count
-- FROM users u WHERE u.user_id = ?;
