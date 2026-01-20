-- Adminer 4.8.1 MySQL 8.0.16 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

-- Drop WooCommerce tables, as Plugin activation will create these.
DROP TABLE IF EXISTS `wp_wc_admin_note_actions`;
DROP TABLE IF EXISTS `wp_wc_admin_notes`;
DROP TABLE IF EXISTS `wp_wc_category_lookup`;
DROP TABLE IF EXISTS `wp_wc_customer_lookup`;
DROP TABLE IF EXISTS `wp_wc_download_log`;
DROP TABLE IF EXISTS `wp_wc_order_coupon_lookup`;
DROP TABLE IF EXISTS `wp_wc_order_product_lookup`;
DROP TABLE IF EXISTS `wp_wc_order_stats`;
DROP TABLE IF EXISTS `wp_wc_order_tax_lookup`;
DROP TABLE IF EXISTS `wp_wc_product_attributes_lookup`;
DROP TABLE IF EXISTS `wp_wc_product_meta_lookup`;
DROP TABLE IF EXISTS `wp_wc_rate_limits`;
DROP TABLE IF EXISTS `wp_wc_reserved_stock`;
DROP TABLE IF EXISTS `wp_wc_tax_rate_classes`;
DROP TABLE IF EXISTS `wp_wc_webhooks`;
DROP TABLE IF EXISTS `wp_woocommerce_api_keys`;
DROP TABLE IF EXISTS `wp_woocommerce_attribute_taxonomies`;
DROP TABLE IF EXISTS `wp_woocommerce_downloadable_product_permissions`;
DROP TABLE IF EXISTS `wp_woocommerce_log`;
DROP TABLE IF EXISTS `wp_woocommerce_order_itemmeta`;
DROP TABLE IF EXISTS `wp_woocommerce_order_items`;
DROP TABLE IF EXISTS `wp_woocommerce_payment_tokenmeta`;
DROP TABLE IF EXISTS `wp_woocommerce_payment_tokens`;
DROP TABLE IF EXISTS `wp_woocommerce_sessions`;
DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_locations`;
DROP TABLE IF EXISTS `wp_woocommerce_shipping_zone_methods`;
DROP TABLE IF EXISTS `wp_woocommerce_shipping_zones`;
DROP TABLE IF EXISTS `wp_woocommerce_tax_rate_locations`;
DROP TABLE IF EXISTS `wp_woocommerce_tax_rates`;

DROP TABLE IF EXISTS `wp_commentmeta`;
CREATE TABLE `wp_commentmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `comment_id` (`comment_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_comments`;
CREATE TABLE `wp_comments` (
  `comment_ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_post_ID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `comment_author` tinytext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_author_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_url` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_author_IP` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_content` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `comment_karma` int(11) NOT NULL DEFAULT '0',
  `comment_approved` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '1',
  `comment_agent` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'comment',
  `comment_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_ID`),
  KEY `comment_post_ID` (`comment_post_ID`),
  KEY `comment_approved_date_gmt` (`comment_approved`,`comment_date_gmt`),
  KEY `comment_date_gmt` (`comment_date_gmt`),
  KEY `comment_parent` (`comment_parent`),
  KEY `comment_author_email` (`comment_author_email`(10))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_comments` (`comment_ID`, `comment_post_ID`, `comment_author`, `comment_author_email`, `comment_author_url`, `comment_author_IP`, `comment_date`, `comment_date_gmt`, `comment_content`, `comment_karma`, `comment_approved`, `comment_agent`, `comment_type`, `comment_parent`, `user_id`) VALUES
(1, 1,  'A WordPress Commenter',  'wapuu@wordpress.example',  'https://wordpress.org/', '', '2023-07-03 13:38:12',  '2023-07-03 13:38:12',  'Hi, this is a comment.\nTo get started with moderating, editing, and deleting comments, please visit the Comments screen in the dashboard.\nCommenter avatars come from <a href=\"https://en.gravatar.com/\">Gravatar</a>.', 0,  '1',  '', 'comment',  0,  0);

DROP TABLE IF EXISTS `wp_links`;
CREATE TABLE `wp_links` (
  `link_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_name` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_image` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_target` varchar(25) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_description` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_visible` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Y',
  `link_owner` bigint(20) unsigned NOT NULL DEFAULT '1',
  `link_rating` int(11) NOT NULL DEFAULT '0',
  `link_updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `link_rel` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `link_notes` mediumtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `link_rss` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`link_id`),
  KEY `link_visible` (`link_visible`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `wp_mailpoet_forms`;
CREATE TABLE `wp_mailpoet_forms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(90) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'enabled',
  `body` longtext COLLATE utf8mb4_unicode_520_ci,
  `settings` longtext COLLATE utf8mb4_unicode_520_ci,
  `styles` longtext COLLATE utf8mb4_unicode_520_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `wp_options`;
CREATE TABLE `wp_options` (
  `option_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_options` (`option_id`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'siteurl',  'http://kit.local', 'on'),
(2, 'home', 'http://kit.local', 'on'),
(3, 'blogname', 'kit', 'on'),
(4, 'blogdescription',  'Just another WordPress site',  'on'),
(5, 'users_can_register', '0',  'on'),
(6, 'admin_email',  'dev-email@flywheel.local', 'on'),
(7, 'start_of_week',  '1',  'on'),
(8, 'use_balanceTags',  '0',  'on'),
(9, 'use_smilies',  '1',  'on'),
(10,  'require_name_email', '1',  'on'),
(11,  'comments_notify',  '1',  'on'),
(12,  'posts_per_rss',  '10', 'on'),
(13,  'rss_use_excerpt',  '0',  'on'),
(14,  'mailserver_url', 'mail.example.com', 'on'),
(15,  'mailserver_login', 'login@example.com',  'on'),
(16,  'mailserver_pass',  'password', 'on'),
(17,  'mailserver_port',  '110',  'on'),
(18,  'default_category', '1',  'on'),
(19,  'default_comment_status', 'open', 'on'),
(20,  'default_ping_status',  'open', 'on'),
(21,  'default_pingback_flag',  '1',  'on'),
(22,  'posts_per_page', '10', 'on'),
(23,  'date_format',  'F j, Y', 'on'),
(24,  'time_format',  'g:i a',  'on'),
(25,  'links_updated_date_format',  'F j, Y g:i a', 'on'),
(26,  'comment_moderation', '0',  'on'),
(27,  'moderation_notify',  '1',  'on'),
(28,  'permalink_structure',  '/%postname%/', 'on'),
(29,  'rewrite_rules',  'a:93:{s:11:\"^wp-json/?$\";s:22:\"index.php?rest_route=/\";s:14:\"^wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:21:\"^index.php/wp-json/?$\";s:22:\"index.php?rest_route=/\";s:24:\"^index.php/wp-json/(.*)?\";s:33:\"index.php?rest_route=/$matches[1]\";s:17:\"^wp-sitemap\\.xml$\";s:23:\"index.php?sitemap=index\";s:17:\"^wp-sitemap\\.xsl$\";s:36:\"index.php?sitemap-stylesheet=sitemap\";s:23:\"^wp-sitemap-index\\.xsl$\";s:34:\"index.php?sitemap-stylesheet=index\";s:48:\"^wp-sitemap-([a-z]+?)-([a-z\\d_-]+?)-(\\d+?)\\.xml$\";s:75:\"index.php?sitemap=$matches[1]&sitemap-subtype=$matches[2]&paged=$matches[3]\";s:34:\"^wp-sitemap-([a-z]+?)-(\\d+?)\\.xml$\";s:47:\"index.php?sitemap=$matches[1]&paged=$matches[2]\";s:47:\"category/(.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:42:\"category/(.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:52:\"index.php?category_name=$matches[1]&feed=$matches[2]\";s:23:\"category/(.+?)/embed/?$\";s:46:\"index.php?category_name=$matches[1]&embed=true\";s:35:\"category/(.+?)/page/?([0-9]{1,})/?$\";s:53:\"index.php?category_name=$matches[1]&paged=$matches[2]\";s:17:\"category/(.+?)/?$\";s:35:\"index.php?category_name=$matches[1]\";s:44:\"tag/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:39:\"tag/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?tag=$matches[1]&feed=$matches[2]\";s:20:\"tag/([^/]+)/embed/?$\";s:36:\"index.php?tag=$matches[1]&embed=true\";s:32:\"tag/([^/]+)/page/?([0-9]{1,})/?$\";s:43:\"index.php?tag=$matches[1]&paged=$matches[2]\";s:14:\"tag/([^/]+)/?$\";s:25:\"index.php?tag=$matches[1]\";s:45:\"type/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:40:\"type/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?post_format=$matches[1]&feed=$matches[2]\";s:21:\"type/([^/]+)/embed/?$\";s:44:\"index.php?post_format=$matches[1]&embed=true\";s:33:\"type/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?post_format=$matches[1]&paged=$matches[2]\";s:15:\"type/([^/]+)/?$\";s:33:\"index.php?post_format=$matches[1]\";s:12:\"robots\\.txt$\";s:18:\"index.php?robots=1\";s:13:\"favicon\\.ico$\";s:19:\"index.php?favicon=1\";s:48:\".*wp-(atom|rdf|rss|rss2|feed|commentsrss2)\\.php$\";s:18:\"index.php?feed=old\";s:20:\".*wp-app\\.php(/.*)?$\";s:19:\"index.php?error=403\";s:18:\".*wp-register.php$\";s:23:\"index.php?register=true\";s:32:\"feed/(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:27:\"(feed|rdf|rss|rss2|atom)/?$\";s:27:\"index.php?&feed=$matches[1]\";s:8:\"embed/?$\";s:21:\"index.php?&embed=true\";s:20:\"page/?([0-9]{1,})/?$\";s:28:\"index.php?&paged=$matches[1]\";s:41:\"comments/feed/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:36:\"comments/(feed|rdf|rss|rss2|atom)/?$\";s:42:\"index.php?&feed=$matches[1]&withcomments=1\";s:17:\"comments/embed/?$\";s:21:\"index.php?&embed=true\";s:44:\"search/(.+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:39:\"search/(.+)/(feed|rdf|rss|rss2|atom)/?$\";s:40:\"index.php?s=$matches[1]&feed=$matches[2]\";s:20:\"search/(.+)/embed/?$\";s:34:\"index.php?s=$matches[1]&embed=true\";s:32:\"search/(.+)/page/?([0-9]{1,})/?$\";s:41:\"index.php?s=$matches[1]&paged=$matches[2]\";s:14:\"search/(.+)/?$\";s:23:\"index.php?s=$matches[1]\";s:47:\"author/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:42:\"author/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:50:\"index.php?author_name=$matches[1]&feed=$matches[2]\";s:23:\"author/([^/]+)/embed/?$\";s:44:\"index.php?author_name=$matches[1]&embed=true\";s:35:\"author/([^/]+)/page/?([0-9]{1,})/?$\";s:51:\"index.php?author_name=$matches[1]&paged=$matches[2]\";s:17:\"author/([^/]+)/?$\";s:33:\"index.php?author_name=$matches[1]\";s:69:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:64:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:80:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&feed=$matches[4]\";s:45:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/embed/?$\";s:74:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&embed=true\";s:57:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:81:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&paged=$matches[4]\";s:39:\"([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/?$\";s:63:\"index.php?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]\";s:56:\"([0-9]{4})/([0-9]{1,2})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:51:\"([0-9]{4})/([0-9]{1,2})/(feed|rdf|rss|rss2|atom)/?$\";s:64:\"index.php?year=$matches[1]&monthnum=$matches[2]&feed=$matches[3]\";s:32:\"([0-9]{4})/([0-9]{1,2})/embed/?$\";s:58:\"index.php?year=$matches[1]&monthnum=$matches[2]&embed=true\";s:44:\"([0-9]{4})/([0-9]{1,2})/page/?([0-9]{1,})/?$\";s:65:\"index.php?year=$matches[1]&monthnum=$matches[2]&paged=$matches[3]\";s:26:\"([0-9]{4})/([0-9]{1,2})/?$\";s:47:\"index.php?year=$matches[1]&monthnum=$matches[2]\";s:43:\"([0-9]{4})/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:38:\"([0-9]{4})/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?year=$matches[1]&feed=$matches[2]\";s:19:\"([0-9]{4})/embed/?$\";s:37:\"index.php?year=$matches[1]&embed=true\";s:31:\"([0-9]{4})/page/?([0-9]{1,})/?$\";s:44:\"index.php?year=$matches[1]&paged=$matches[2]\";s:13:\"([0-9]{4})/?$\";s:26:\"index.php?year=$matches[1]\";s:27:\".?.+?/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\".?.+?/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\".?.+?/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\".?.+?/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\".?.+?/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"(.?.+?)/embed/?$\";s:41:\"index.php?pagename=$matches[1]&embed=true\";s:20:\"(.?.+?)/trackback/?$\";s:35:\"index.php?pagename=$matches[1]&tb=1\";s:40:\"(.?.+?)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:35:\"(.?.+?)/(feed|rdf|rss|rss2|atom)/?$\";s:47:\"index.php?pagename=$matches[1]&feed=$matches[2]\";s:28:\"(.?.+?)/page/?([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&paged=$matches[2]\";s:35:\"(.?.+?)/comment-page-([0-9]{1,})/?$\";s:48:\"index.php?pagename=$matches[1]&cpage=$matches[2]\";s:24:\"(.?.+?)(?:/([0-9]+))?/?$\";s:47:\"index.php?pagename=$matches[1]&page=$matches[2]\";s:27:\"[^/]+/attachment/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:37:\"[^/]+/attachment/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:57:\"[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:52:\"[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:33:\"[^/]+/attachment/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";s:16:\"([^/]+)/embed/?$\";s:37:\"index.php?name=$matches[1]&embed=true\";s:20:\"([^/]+)/trackback/?$\";s:31:\"index.php?name=$matches[1]&tb=1\";s:40:\"([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:35:\"([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:43:\"index.php?name=$matches[1]&feed=$matches[2]\";s:28:\"([^/]+)/page/?([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&paged=$matches[2]\";s:35:\"([^/]+)/comment-page-([0-9]{1,})/?$\";s:44:\"index.php?name=$matches[1]&cpage=$matches[2]\";s:24:\"([^/]+)(?:/([0-9]+))?/?$\";s:43:\"index.php?name=$matches[1]&page=$matches[2]\";s:16:\"[^/]+/([^/]+)/?$\";s:32:\"index.php?attachment=$matches[1]\";s:26:\"[^/]+/([^/]+)/trackback/?$\";s:37:\"index.php?attachment=$matches[1]&tb=1\";s:46:\"[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$\";s:49:\"index.php?attachment=$matches[1]&feed=$matches[2]\";s:41:\"[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$\";s:50:\"index.php?attachment=$matches[1]&cpage=$matches[2]\";s:22:\"[^/]+/([^/]+)/embed/?$\";s:43:\"index.php?attachment=$matches[1]&embed=true\";}',  'on'),
(30,  'hack_file',  '0',  'on'),
(31,  'blog_charset', 'UTF-8',  'on'),
(32,  'moderation_keys',  '', 'off'),
(33,  'active_plugins', 'a:0:{}', 'on'),
(34,  'category_base',  '', 'on'),
(35,  'ping_sites', 'http://rpc.pingomatic.com/', 'on'),
(36,  'comment_max_links',  '2',  'on'),
(37,  'gmt_offset', '0',  'on'),
(38,  'default_email_category', '1',  'on'),
(39,  'recently_edited',  '', 'off'),
(40,  'template', 'twentytwentythree',  'on'),
(41,  'stylesheet', 'twentytwentythree',  'on'),
(42,  'comment_registration', '0',  'on'),
(43,  'html_type',  'text/html',  'on'),
(44,  'use_trackback',  '0',  'on'),
(45,  'default_role', 'subscriber', 'on'),
(46,  'db_version', '60717',  'on'),
(47,  'uploads_use_yearmonth_folders',  '1',  'on'),
(48,  'upload_path',  '', 'on'),
(49,  'blog_public',  '1',  'on'),
(50,  'default_link_category',  '2',  'on'),
(51,  'show_on_front',  'posts',  'on'),
(52,  'tag_base', '', 'on'),
(53,  'show_avatars', '1',  'on'),
(54,  'avatar_rating',  'G',  'on'),
(55,  'upload_url_path',  '', 'on'),
(56,  'thumbnail_size_w', '150',  'on'),
(57,  'thumbnail_size_h', '150',  'on'),
(58,  'thumbnail_crop', '1',  'on'),
(59,  'medium_size_w',  '300',  'on'),
(60,  'medium_size_h',  '300',  'on'),
(61,  'avatar_default', 'mystery',  'on'),
(62,  'large_size_w', '1024', 'on'),
(63,  'large_size_h', '1024', 'on'),
(64,  'image_default_link_type',  'none', 'on'),
(65,  'image_default_size', '', 'on'),
(66,  'image_default_align',  '', 'on'),
(67,  'close_comments_for_old_posts', '0',  'on'),
(68,  'close_comments_days_old',  '14', 'on'),
(69,  'thread_comments',  '1',  'on'),
(70,  'thread_comments_depth',  '5',  'on'),
(71,  'page_comments',  '0',  'on'),
(72,  'comments_per_page',  '50', 'on'),
(73,  'default_comments_page',  'newest', 'on'),
(74,  'comment_order',  'asc',  'on'),
(75,  'sticky_posts', 'a:0:{}', 'on'),
(76,  'widget_categories',  'a:0:{}', 'on'),
(77,  'widget_text',  'a:0:{}', 'on'),
(78,  'widget_rss', 'a:0:{}', 'on'),
(79,  'uninstall_plugins',  'a:0:{}', 'off'),
(80,  'timezone_string',  '', 'on'),
(81,  'page_for_posts', '0',  'on'),
(82,  'page_on_front',  '0',  'on'),
(83,  'default_post_format',  '0',  'on'),
(84,  'link_manager_enabled', '0',  'on'),
(85,  'finished_splitting_shared_terms',  '1',  'on'),
(86,  'site_icon',  '0',  'on'),
(87,  'medium_large_size_w',  '768',  'on'),
(88,  'medium_large_size_h',  '0',  'on'),
(89,  'wp_page_for_privacy_policy', '3',  'on'),
(90,  'show_comments_cookies_opt_in', '1',  'on'),
(91,  'admin_email_lifespan', '1805512805', 'on'),
(92,  'disallowed_keys',  '', 'off'),
(93,  'comment_previously_approved',  '1',  'on'),
(94,  'auto_plugin_theme_update_emails',  'a:0:{}', 'off'),
(95,  'auto_update_core_dev', 'enabled',  'on'),
(96,  'auto_update_core_minor', 'enabled',  'on'),
(97,  'auto_update_core_major', 'enabled',  'on'),
(98,  'wp_force_deactivated_plugins', 'a:0:{}', 'on'),
(99,  'initial_db_version', '60717',  'on'),
(100, 'wp_user_roles',  'a:5:{s:13:\"administrator\";a:2:{s:4:\"name\";s:13:\"Administrator\";s:12:\"capabilities\";a:61:{s:13:\"switch_themes\";b:1;s:11:\"edit_themes\";b:1;s:16:\"activate_plugins\";b:1;s:12:\"edit_plugins\";b:1;s:10:\"edit_users\";b:1;s:10:\"edit_files\";b:1;s:14:\"manage_options\";b:1;s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:6:\"import\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:8:\"level_10\";b:1;s:7:\"level_9\";b:1;s:7:\"level_8\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;s:12:\"delete_users\";b:1;s:12:\"create_users\";b:1;s:17:\"unfiltered_upload\";b:1;s:14:\"edit_dashboard\";b:1;s:14:\"update_plugins\";b:1;s:14:\"delete_plugins\";b:1;s:15:\"install_plugins\";b:1;s:13:\"update_themes\";b:1;s:14:\"install_themes\";b:1;s:11:\"update_core\";b:1;s:10:\"list_users\";b:1;s:12:\"remove_users\";b:1;s:13:\"promote_users\";b:1;s:18:\"edit_theme_options\";b:1;s:13:\"delete_themes\";b:1;s:6:\"export\";b:1;}}s:6:\"editor\";a:2:{s:4:\"name\";s:6:\"Editor\";s:12:\"capabilities\";a:34:{s:17:\"moderate_comments\";b:1;s:17:\"manage_categories\";b:1;s:12:\"manage_links\";b:1;s:12:\"upload_files\";b:1;s:15:\"unfiltered_html\";b:1;s:10:\"edit_posts\";b:1;s:17:\"edit_others_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:10:\"edit_pages\";b:1;s:4:\"read\";b:1;s:7:\"level_7\";b:1;s:7:\"level_6\";b:1;s:7:\"level_5\";b:1;s:7:\"level_4\";b:1;s:7:\"level_3\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:17:\"edit_others_pages\";b:1;s:20:\"edit_published_pages\";b:1;s:13:\"publish_pages\";b:1;s:12:\"delete_pages\";b:1;s:19:\"delete_others_pages\";b:1;s:22:\"delete_published_pages\";b:1;s:12:\"delete_posts\";b:1;s:19:\"delete_others_posts\";b:1;s:22:\"delete_published_posts\";b:1;s:20:\"delete_private_posts\";b:1;s:18:\"edit_private_posts\";b:1;s:18:\"read_private_posts\";b:1;s:20:\"delete_private_pages\";b:1;s:18:\"edit_private_pages\";b:1;s:18:\"read_private_pages\";b:1;}}s:6:\"author\";a:2:{s:4:\"name\";s:6:\"Author\";s:12:\"capabilities\";a:10:{s:12:\"upload_files\";b:1;s:10:\"edit_posts\";b:1;s:20:\"edit_published_posts\";b:1;s:13:\"publish_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_2\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;s:22:\"delete_published_posts\";b:1;}}s:11:\"contributor\";a:2:{s:4:\"name\";s:11:\"Contributor\";s:12:\"capabilities\";a:5:{s:10:\"edit_posts\";b:1;s:4:\"read\";b:1;s:7:\"level_1\";b:1;s:7:\"level_0\";b:1;s:12:\"delete_posts\";b:1;}}s:10:\"subscriber\";a:2:{s:4:\"name\";s:10:\"Subscriber\";s:12:\"capabilities\";a:2:{s:4:\"read\";b:1;s:7:\"level_0\";b:1;}}}',  'on'),
(101, 'fresh_site', '1',  'on'),
(102, 'user_count', '1',  'off'),
(103, 'widget_block', 'a:6:{i:2;a:1:{s:7:\"content\";s:19:\"<!-- wp:search /-->\";}i:3;a:1:{s:7:\"content\";s:154:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Posts</h2><!-- /wp:heading --><!-- wp:latest-posts /--></div><!-- /wp:group -->\";}i:4;a:1:{s:7:\"content\";s:227:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Recent Comments</h2><!-- /wp:heading --><!-- wp:latest-comments {\"displayAvatar\":false,\"displayDate\":false,\"displayExcerpt\":false} /--></div><!-- /wp:group -->\";}i:5;a:1:{s:7:\"content\";s:146:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Archives</h2><!-- /wp:heading --><!-- wp:archives /--></div><!-- /wp:group -->\";}i:6;a:1:{s:7:\"content\";s:150:\"<!-- wp:group --><div class=\"wp-block-group\"><!-- wp:heading --><h2>Categories</h2><!-- /wp:heading --><!-- wp:categories /--></div><!-- /wp:group -->\";}s:12:\"_multiwidget\";i:1;}', 'on'),
(104, 'sidebars_widgets', 'a:4:{s:19:\"wp_inactive_widgets\";a:0:{}s:9:\"sidebar-1\";a:3:{i:0;s:7:\"block-2\";i:1;s:7:\"block-3\";i:2;s:7:\"block-4\";}s:9:\"sidebar-2\";a:2:{i:0;s:7:\"block-5\";i:1;s:7:\"block-6\";}s:13:\"array_version\";i:3;}', 'on'),
(105, 'cron', 'a:9:{i:1696862175;a:1:{s:28:\"wp_update_comment_type_batch\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:2:{s:8:\"schedule\";b:0;s:4:\"args\";a:0:{}}}}i:1696865698;a:1:{s:34:\"wp_privacy_delete_old_export_files\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"hourly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:3600;}}}i:1696905298;a:3:{s:16:\"wp_version_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:17:\"wp_update_plugins\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}s:16:\"wp_update_themes\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1696905315;a:1:{s:21:\"wp_update_user_counts\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:10:\"twicedaily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:43200;}}}i:1696948498;a:2:{s:30:\"wp_site_health_scheduled_check\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}s:32:\"recovery_mode_clean_expired_keys\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1696948515;a:2:{s:19:\"wp_scheduled_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}s:25:\"delete_expired_transients\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1696948516;a:1:{s:30:\"wp_scheduled_auto_draft_delete\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:5:\"daily\";s:4:\"args\";a:0:{}s:8:\"interval\";i:86400;}}}i:1697466926;a:1:{s:30:\"wp_delete_temp_updater_backups\";a:1:{s:32:\"40cd750bba9870f18aada2478b24840a\";a:3:{s:8:\"schedule\";s:6:\"weekly\";s:4:\"args\";a:0:{}s:8:\"interval\";i:604800;}}}s:7:\"version\";i:2;}',  'on'),
(106, 'widget_pages', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(107, 'widget_calendar',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(108, 'widget_archives',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(109, 'widget_media_audio', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(110, 'widget_media_image', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(111, 'widget_media_gallery', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(112, 'widget_media_video', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(113, 'widget_meta',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(114, 'widget_search',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(115, 'widget_recent-posts',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(116, 'widget_recent-comments', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(117, 'widget_tag_cloud', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(118, 'widget_nav_menu',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(119, 'widget_custom_html', 'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(120, 'nonce_key',  ':@mS9DsY,vFI=iAKiwy;g$hm^l d4nE(_%Bd+>v2K`d;Km2Me;mO9[f_-Q Zh=[Q', 'off'),
(121, 'nonce_salt', '/?#2ig*c_e_}-[mel-$?%U;;@}oC9TF%|l /Df%)V@kV~|$Lo[bKcL,W{y-]4%S>', 'off'),
(122, 'recovery_keys',  'a:0:{}', 'on'),
(123, 'theme_mods_twentytwentythree', 'a:1:{s:18:\"custom_css_post_id\";i:-1;}',  'on'),
(124, 'can_compress_scripts', '1',  'on'),
(125, 'WishListMemberOptions_Migrated', '1',  'on'),
(126, 'widget_wishlistwidget',  'a:1:{s:12:\"_multiwidget\";i:1;}', 'on'),
(127, 'WishListMemberOptions_MigrateLevelData', '1',  'on'),
(128, 'WishListMemberOptions_MigrateContentLevelData',  '1',  'on'),
(129, 'et_support_center_setup_done',  'processed',  'on'),
(130, 'et_support_center_installed',  'true',  'on'),
(131, 'woocommerce_version',  '9.7.1',  'on');

DROP TABLE IF EXISTS `wp_postmeta`;
CREATE TABLE `wp_postmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `post_id` (`post_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `wp_posts`;
CREATE TABLE `wp_posts` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_author` bigint(20) unsigned NOT NULL DEFAULT '0',
  `post_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_date_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_title` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_excerpt` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'publish',
  `comment_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `ping_status` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'open',
  `post_password` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `post_name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `to_ping` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `pinged` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_modified_gmt` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `post_content_filtered` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `post_parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `guid` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `menu_order` int(11) NOT NULL DEFAULT '0',
  `post_type` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'post',
  `post_mime_type` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `comment_count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `post_name` (`post_name`(191)),
  KEY `type_status_date` (`post_type`,`post_status`,`post_date`,`ID`),
  KEY `post_parent` (`post_parent`),
  KEY `post_author` (`post_author`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

DROP TABLE IF EXISTS `wp_term_relationships`;
CREATE TABLE `wp_term_relationships` (
  `object_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_taxonomy_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `term_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`,`term_taxonomy_id`),
  KEY `term_taxonomy_id` (`term_taxonomy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_relationships` (`object_id`, `term_taxonomy_id`, `term_order`) VALUES
(1, 1,  0);

DROP TABLE IF EXISTS `wp_term_taxonomy`;
CREATE TABLE `wp_term_taxonomy` (
  `term_taxonomy_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `taxonomy` varchar(32) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `description` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `parent` bigint(20) unsigned NOT NULL DEFAULT '0',
  `count` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_taxonomy_id`),
  UNIQUE KEY `term_id_taxonomy` (`term_id`,`taxonomy`),
  KEY `taxonomy` (`taxonomy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_term_taxonomy` (`term_taxonomy_id`, `term_id`, `taxonomy`, `description`, `parent`, `count`) VALUES
(1, 1,  'category', '', 0,  1);

DROP TABLE IF EXISTS `wp_termmeta`;
CREATE TABLE `wp_termmeta` (
  `meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `term_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`meta_id`),
  KEY `term_id` (`term_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_terms`;
CREATE TABLE `wp_terms` (
  `term_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `slug` varchar(200) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `term_group` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`term_id`),
  KEY `slug` (`slug`(191)),
  KEY `name` (`name`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_terms` (`term_id`, `name`, `slug`, `term_group`) VALUES
(1, 'Uncategorized',  'uncategorized',  0);

DROP TABLE IF EXISTS `wp_usermeta`;
CREATE TABLE `wp_usermeta` (
  `umeta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_key` varchar(255) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `meta_value` longtext COLLATE utf8mb4_unicode_520_ci,
  PRIMARY KEY (`umeta_id`),
  KEY `user_id` (`user_id`),
  KEY `meta_key` (`meta_key`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_usermeta` (`umeta_id`, `user_id`, `meta_key`, `meta_value`) VALUES
(1, 1,  'nickname', 'admin'),
(2, 1,  'first_name', ''),
(3, 1,  'last_name',  ''),
(4, 1,  'description',  ''),
(5, 1,  'rich_editing', 'true'),
(6, 1,  'syntax_highlighting',  'true'),
(7, 1,  'comment_shortcuts',  'false'),
(8, 1,  'admin_color',  'fresh'),
(9, 1,  'use_ssl',  '0'),
(10,  1,  'show_admin_bar_front', 'true'),
(11,  1,  'locale', ''),
(12,  1,  'wp_capabilities',  'a:1:{s:13:\"administrator\";b:1;}'),
(13,  1,  'wp_user_level',  '10'),
(14,  1,  'dismissed_wp_pointers',  ''),
(15,  1,  'show_welcome_panel', '1'),
(16,  1,  'session_tokens', 'a:1:{s:64:\"d1edb8c7d17dc41fa6de9833631a6381dca0306f20dfd4b64947e6b8818dd16e\";a:4:{s:10:\"expiration\";i:1676810217;s:2:\"ip\";s:9:\"127.0.0.1\";s:2:\"ua\";s:117:\"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36\";s:5:\"login\";i:1676637417;}}'),
(17,  1,  'wp_user-settings', 'unfold=1&ampampmfold=o&ampampeditor=html&ampamplibraryContent=browse&ampampsiteorigin_panels_setting_tab=widgets&amplibraryContent=browse&ampeditor=tinymce&libraryContent=browse&editor=tinymce&siteorigin_panels_setting_tab=welcome'),
(18,  1,  'wp_user-settings-time',  '1676637417'),
(19,  1,  'wp_dashboard_quick_press_last_post_id',  '1'),
(20,  1,  'edit_page_per_page',  '100'),
(21,  1,  'edit_post_per_page',  '100'),
(22,  1,  'wp_persisted_preferences', 'a:4:{s:4:"core";a:1:{s:26:"isComplementaryAreaVisible";b:1;}s:14:"core/edit-post";a:1:{s:12:"welcomeGuide";b:0;}s:9:"_modified";s:24:"2024-07-18T02:45:41.491Z";s:17:"core/edit-widgets";a:2:{s:26:"isComplementaryAreaVisible";b:1;s:12:"welcomeGuide";b:0;}}');

DROP TABLE IF EXISTS `wp_users`;
CREATE TABLE `wp_users` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(60) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_pass` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_nicename` varchar(50) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_url` varchar(100) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_registered` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `user_activation_key` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `user_status` int(11) NOT NULL DEFAULT '0',
  `display_name` varchar(250) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`ID`),
  KEY `user_login_key` (`user_login`),
  KEY `user_nicename` (`user_nicename`),
  KEY `user_email` (`user_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_users` (`ID`, `user_login`, `user_pass`, `user_nicename`, `user_email`, `user_url`, `user_registered`, `user_activation_key`, `user_status`, `display_name`) VALUES
(1, 'admin',  '$P$BPKHO1xSCwu6j57sJB/p7JndeBdRVd.', 'admin',  'dev-email@flywheel.local', 'http://kit.local',  '2023-07-03 13:38:12',  '', 0,  'admin');

DROP TABLE IF EXISTS `wp_wlcc_contentarchiver`;
CREATE TABLE `wp_wlcc_contentarchiver` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `mlevel` varchar(15) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `exp_date` datetime DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlcc_contentmanager_move`;
CREATE TABLE `wp_wlcc_contentmanager_move` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `action` varchar(15) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `categories` text COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `due_date` datetime NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlcc_contentmanager_repost`;
CREATE TABLE `wp_wlcc_contentmanager_repost` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `due_date` datetime NOT NULL,
  `rep_num` int(11) DEFAULT NULL,
  `rep_by` varchar(10) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `rep_end` int(11) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlcc_contentmanager_set`;
CREATE TABLE `wp_wlcc_contentmanager_set` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `due_date` datetime NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlcc_contentsched`;
CREATE TABLE `wp_wlcc_contentsched` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) NOT NULL,
  `mlevel` varchar(15) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `num_days` int(11) NOT NULL,
  `hide_days` int(11) NOT NULL DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_api_queue`;
CREATE TABLE `wp_wlm_api_queue` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `notes` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `tries` int(11) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_contentlevel_options`;
CREATE TABLE `wp_wlm_contentlevel_options` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `contentlevel_id` bigint(20) NOT NULL,
  `option_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `contentlevel_id` (`contentlevel_id`,`option_name`),
  KEY `autoload` (`autoload`),
  KEY `contentlevel_id2` (`contentlevel_id`),
  KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_contentlevels`;
CREATE TABLE `wp_wlm_contentlevels` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `content_id` bigint(20) NOT NULL,
  `level_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `type` varchar(21) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `content_id` (`content_id`,`level_id`,`type`),
  KEY `content_id2` (`content_id`),
  KEY `level_id` (`level_id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_wlm_contentlevels` (`ID`, `content_id`, `level_id`, `type`) VALUES
(1, 1,  'Protection', '~CATEGORY');

DROP TABLE IF EXISTS `wp_wlm_email_queue`;
CREATE TABLE `wp_wlm_email_queue` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `broadcastid` int(9) NOT NULL,
  `userid` bigint(20) NOT NULL,
  `failed` int(1) NOT NULL DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`),
  KEY `broadcastid` (`broadcastid`),
  CONSTRAINT `wp_wlm_email_queue_ibfk_1` FOREIGN KEY (`broadcastid`) REFERENCES `wp_wlm_emailbroadcast` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_emailbroadcast`;
CREATE TABLE `wp_wlm_emailbroadcast` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `from_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `from_email` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `subject` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `text_body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `footer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `send_to` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `mlevel` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `sent_as` varchar(5) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `status` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'Queueing',
  `otheroptions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci,
  `total_queued` int(11) DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_level_options`;
CREATE TABLE `wp_wlm_level_options` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `level_id` bigint(20) NOT NULL,
  `option_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_logs`;
CREATE TABLE `wp_wlm_logs` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `log_group` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `log_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `date_added` datetime NOT NULL,
  `log_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `user_id__log_group__log_key__date_added` (`user_id`,`log_group`,`log_key`,`date_added`),
  KEY `user_id` (`user_id`),
  KEY `user_id__log_group` (`user_id`,`log_group`),
  KEY `user_id__log_group__log_key` (`user_id`,`log_group`,`log_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_options`;
CREATE TABLE `wp_wlm_options` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

INSERT INTO `wp_wlm_options` (`ID`, `option_name`, `option_value`, `autoload`) VALUES
(1, 'FormVersion',  'themestyled',  'yes'),
(2, 'paypalec_spb', 'a:1:{s:6:\"enable\";i:1;}',  'yes'),
(3, 'ActiveShoppingCarts',  'a:1:{i:0;s:35:\"integration.shoppingcart.stripe.php\";}',  'yes'),
(4, 'CurrentVersion', '3.22.5', 'yes'),
(5, 'dashboard_checklist_archived_closed',  'closed', 'yes'),
(6, 'onetime_login_link_label', 'Send me a One-Time Login Link',  'yes'),
(7, 'expiring_notification_admin',  '0',  'yes'),
(8, 'expiring_notification',  '1',  'yes'),
(9, 'requireemailconfirmation_notification',  '1',  'yes'),
(10,  'require_admin_approval_free_notification_admin', '1',  'yes'),
(11,  'require_admin_approval_free_notification_user1', '1',  'yes'),
(12,  'require_admin_approval_free_notification_user2', '1',  'yes'),
(13,  'require_admin_approval_paid_notification_admin', '1',  'yes'),
(14,  'require_admin_approval_paid_notification_user1', '1',  'yes'),
(15,  'require_admin_approval_paid_notification_user2', '1',  'yes'),
(16,  'notify_admin_of_newuser',  '1',  'yes'),
(17,  'newuser_notification_user',  '1',  'yes'),
(18,  'incomplete_notification',  '1',  'yes'),
(19,  'cancel_notification',  '0',  'yes'),
(20,  'uncancel_notification',  '0',  'yes'),
(21,  'recaptcha_public_key', '', 'yes'),
(22,  'recaptcha_private_key',  '', 'yes'),
(23,  'menu_on_top',  '1',  'yes'),
(24,  'auto_insert_more_at',  '50', 'yes'),
(25,  'login_limit',  '7',  'yes'),
(26,  'login_limit_error',  '<b>Error:</b> You have reached your daily login limit.', 'yes'),
(27,  'min_passlength', '8',  'yes'),
(28,  'privacy_require_tos_on_registration',  '0',  'yes'),
(29,  'privacy_require_tos_checkbox_text',  'By checking this box you confirm that you have read and agree to the Terms of Service.', 'yes'),
(30,  'privacy_require_tos_error_message',  'In order to register for this site you must agree to the Terms of Service by checking the box next to the Terms of Service agreement.',  'yes'),
(31,  'privacy_enable_consent_to_market', '0',  'yes'),
(32,  'privacy_consent_to_market_text', 'By checking this box you agree to receive additional information regarding our products/services, events, news and offers.', 'yes'),
(33,  'privacy_consent_affects_emailbroadcast', '1',  'yes'),
(34,  'privacy_consent_affects_autoresponder',  '1',  'yes'),
(35,  'privacy_display_tos_on_footer',  '0',  'yes'),
(36,  'privacy_display_pp_on_footer', '0',  'yes'),
(37,  'privacy_email_template_request_subject', 'Confirm your request to [request]',  'yes'),
(38,  'privacy_email_template_request', '<p>Hi [firstname]</p><p>A request has been made to perform the following action on your account at [sitename] ([siteurl])</p><p>[request]</p><p>To confirm this, please click on the following link:<br>[confirm_url]</p><p>You can safely ignore and delete this email if you do not want to take this action.</p><p>This email has been sent to [email]</p><p>Thank you.</p>', 'yes'),
(39,  'privacy_email_template_download_subject',  'Personal Data Export', 'yes'),
(40,  'privacy_email_template_download',  '<p>Hi [firstname]</p><p>Your request for an export of personal data has been completed. You may download your personal data by clicking on the link below. For privacy and security, we will automatically delete the file on [expiration], so please download it before then.</p><p>[link]</p><p>This email has been sent to [email].</p><p>Thank you.</p>',  'yes'),
(41,  'privacy_email_template_delete_subject',  'Erasure Request Fulfilled',  'yes'),
(42,  'privacy_email_template_delete',  '<p>Hi [firstname]</p><p>Your request to erase your personal data on [sitename] has been completed.</p><p>If you have any follow-up questions or concerns, please contact the site administrator at [siteurl]</p><p>Thank you.</p>',  'yes'),
(43,  'member_unsub_notification',  '1',  'yes'),
(44,  'member_unsub_notification_subject',  '[sitename] - Unsubscribed From Email Broadcast', 'yes'),
(45,  'member_unsub_notification_body', '<p>You have been unsubscribed from the Email Broadcasts.</p><p>You may use the link below if you would like to subscribe again.</p><p>[resubscribeurl]</p>', 'yes'),
(46,  'show_wp_admin_bar',  '1',  'yes'),
(47,  'rss_hide_protected', '1',  'yes'),
(48,  'wpm_levels', 'a:1:{i:1676637461;a:150:{s:4:\"name\";s:6:\"Bronze\";s:2:\"id\";i:1676637461;s:10:\"addToLevel\";N;s:16:\"afterregredirect\";N;s:13:\"allcategories\";N;s:11:\"allcomments\";N;s:8:\"allpages\";N;s:8:\"allposts\";N;s:8:\"calendar\";s:4:\"Days\";s:19:\"disableexistinglink\";i:0;s:20:\"disableprefilledinfo\";N;s:6:\"expire\";i:7;s:13:\"inheritparent\";N;s:6:\"isfree\";N;s:10:\"levelOrder\";i:1676637461;s:13:\"loginredirect\";N;s:21:\"registrationdatereset\";N;s:27:\"registrationdateresetactive\";N;s:15:\"removeFromLevel\";N;s:20:\"requireadminapproval\";N;s:33:\"requireadminapproval_integrations\";N;s:14:\"requirecaptcha\";N;s:24:\"requireemailconfirmation\";N;s:4:\"role\";s:10:\"subscriber\";s:9:\"salespage\";N;s:22:\"uncancelonregistration\";N;s:3:\"url\";s:10:\"EFMXdewx28\";s:14:\"allow_free_reg\";i:1;s:22:\"enable_custom_reg_form\";N;s:16:\"enable_salespage\";N;s:10:\"enable_tos\";N;s:11:\"expire_date\";N;s:13:\"expire_option\";N;s:3:\"tos\";s:46:\"I agree to the following terms and conditions.\";s:5:\"count\";N;s:12:\"upgradeAfter\";N;s:18:\"upgradeAfterPeriod\";N;s:13:\"upgradeMethod\";N;s:13:\"upgradeOnDate\";N;s:15:\"upgradeSchedule\";N;s:9:\"upgradeTo\";N;s:20:\"enable_header_footer\";N;s:14:\"regform_before\";N;s:13:\"regform_after\";N;s:15:\"custom_reg_form\";N;s:24:\"custom_afterreg_redirect\";N;s:22:\"afterreg_redirect_type\";s:7:\"message\";s:16:\"afterreg_message\";s:246:\"<p>Hey [wlm_firstname]!</p><p>Thanks for joining our [wlm_memberlevel] membership!</p><p>Below, you\'ll find links to important areas of the site.</p><p>Please feel free to explore the site and definitely let us know if you have any questions.</p>\";s:13:\"afterreg_page\";N;s:12:\"afterreg_url\";N;s:21:\"custom_login_redirect\";N;s:19:\"login_redirect_type\";s:7:\"message\";s:13:\"login_message\";s:248:\"<p>Hey [wlm_firstname]!</p><p>Welcome back! Below, you\'ll find links to all the important areas of your membership site. Please feel free peruse the site and definitely let us know if you have any questions.</p><p>[Site admin, place links here]</p>\";s:10:\"login_page\";N;s:9:\"login_url\";N;s:22:\"custom_logout_redirect\";N;s:20:\"logout_redirect_type\";s:7:\"message\";s:14:\"logout_message\";s:57:\"<p>You have been successfully logged out of the site.</p>\";s:11:\"logout_page\";N;s:10:\"logout_url\";N;s:25:\"autocreate_account_enable\";i:0;s:27:\"autocreate_account_username\";s:7:\"{email}\";s:31:\"autocreate_account_enable_delay\";i:0;s:24:\"autocreate_account_delay\";i:15;s:29:\"autocreate_account_delay_type\";i:1;s:27:\"autoadd_other_registrations\";i:0;s:38:\"require_email_confirmation_sender_name\";s:5:\"admin\";s:39:\"require_email_confirmation_sender_email\";s:24:\"dev-email@flywheel.local\";s:34:\"require_email_confirmation_subject\";s:32:\"Please confirm your registration\";s:34:\"require_email_confirmation_message\";s:606:\"<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be confirmed before it is active.</p><p>Confirm by visiting the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>** These login details will only give you proper access after the registration has been confirmed.</p><p>Thank You.</p>\";s:35:\"require_email_confirmation_reminder\";s:1:\"1\";s:32:\"require_email_confirmation_start\";s:1:\"1\";s:37:\"require_email_confirmation_start_type\";s:0:\"\";s:37:\"require_email_confirmation_send_every\";s:2:\"24\";s:34:\"require_email_confirmation_howmany\";s:1:\"3\";s:47:\"require_email_confirmation_reminder_sender_name\";s:5:\"admin\";s:48:\"require_email_confirmation_reminder_sender_email\";s:24:\"dev-email@flywheel.local\";s:43:\"require_email_confirmation_reminder_subject\";s:43:\"Reminder - Please confirm your registration\";s:43:\"require_email_confirmation_reminder_message\";s:319:\"<p>Hi [firstname]</p><p>This is a reminder that your registration for [memberlevel] requires confirmation before it is active.</p><p>You can confirm by using the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed, you can login using the following link.</p><p>Login URL: [loginurl]</p><p>Thank You.</p>\";s:15:\"email_confirmed\";i:0;s:27:\"email_confirmed_sender_name\";s:5:\"admin\";s:28:\"email_confirmed_sender_email\";s:24:\"dev-email@flywheel.local\";s:23:\"email_confirmed_subject\";s:22:\"Registration confirmed\";s:23:\"email_confirmed_message\";s:94:\"<p>Hi [firstname]</p><p>Your registration for [memberlevel] is confirmed.</p><p>Thank You.</p>\";s:46:\"require_admin_approval_free_notification_admin\";s:1:\"1\";s:41:\"require_admin_approval_free_admin_subject\";s:30:\"A New Member Requires Approval\";s:41:\"require_admin_approval_free_admin_message\";s:221:\"<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>\";s:46:\"require_admin_approval_free_notification_user1\";s:1:\"1\";s:45:\"require_admin_approval_free_user1_sender_name\";s:5:\"admin\";s:46:\"require_admin_approval_free_user1_sender_email\";s:24:\"dev-email@flywheel.local\";s:41:\"require_admin_approval_free_user1_subject\";s:36:\"Registration requires admin approval\";s:41:\"require_admin_approval_free_user1_message\";s:576:\"<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>\";s:46:\"require_admin_approval_free_notification_user2\";s:1:\"1\";s:45:\"require_admin_approval_free_user2_sender_name\";s:5:\"admin\";s:46:\"require_admin_approval_free_user2_sender_email\";s:24:\"dev-email@flywheel.local\";s:41:\"require_admin_approval_free_user2_subject\";s:27:\"Registration admin approval\";s:41:\"require_admin_approval_free_user2_message\";s:173:\"<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>\";s:46:\"require_admin_approval_paid_notification_admin\";s:1:\"1\";s:41:\"require_admin_approval_paid_admin_subject\";s:30:\"A New Member Requires Approval\";s:41:\"require_admin_approval_paid_admin_message\";s:221:\"<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>\";s:46:\"require_admin_approval_paid_notification_user1\";s:1:\"1\";s:45:\"require_admin_approval_paid_user1_sender_name\";s:5:\"admin\";s:46:\"require_admin_approval_paid_user1_sender_email\";s:24:\"dev-email@flywheel.local\";s:41:\"require_admin_approval_paid_user1_subject\";s:36:\"Registration requires admin approval\";s:41:\"require_admin_approval_paid_user1_message\";s:576:\"<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>\";s:46:\"require_admin_approval_paid_notification_user2\";s:1:\"1\";s:45:\"require_admin_approval_paid_user2_sender_name\";s:5:\"admin\";s:46:\"require_admin_approval_paid_user2_sender_email\";s:24:\"dev-email@flywheel.local\";s:41:\"require_admin_approval_paid_user2_subject\";s:27:\"Registration admin approval\";s:41:\"require_admin_approval_paid_user2_message\";s:173:\"<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>\";s:23:\"incomplete_notification\";s:1:\"1\";s:16:\"incomplete_start\";s:1:\"1\";s:21:\"incomplete_start_type\";N;s:21:\"incomplete_send_every\";s:2:\"24\";s:18:\"incomplete_howmany\";s:1:\"3\";s:22:\"incomplete_sender_name\";s:5:\"admin\";s:23:\"incomplete_sender_email\";s:24:\"dev-email@flywheel.local\";s:18:\"incomplete_subject\";s:33:\"Please Complete Your Registration\";s:18:\"incomplete_message\";s:156:\"<p>Hi,</p><p>Thank you for registering for [memberlevel]</p><p>Complete your registration by visiting the link below:</p><p>[incregurl]</p><p>Thank you.</p>\";s:26:\"newuser_notification_admin\";s:1:\"1\";s:23:\"newuser_admin_recipient\";s:24:\"dev-email@flywheel.local\";s:21:\"newuser_admin_subject\";s:27:\"A New Member has Registered\";s:21:\"newuser_admin_message\";s:208:\"<p>A new member has registered with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]<br>Membership Level: [memberlevel]<br>Username: [username]</p><p>Thank you.</p>\";s:25:\"newuser_notification_user\";s:1:\"1\";s:24:\"newuser_user_sender_name\";s:5:\"admin\";s:25:\"newuser_user_sender_email\";s:24:\"dev-email@flywheel.local\";s:20:\"newuser_user_subject\";s:30:\"Congrats - You are registered!\";s:20:\"newuser_user_message\";s:344:\"<p>[firstname],</p><p>You have successfully registered as a [memberlevel] member.</p><p>Please keep this information safe as it contains your username and password.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>You are invited to login and check things out.</p><p>We hope to see you inside.</p>\";s:27:\"expiring_notification_admin\";s:1:\"0\";s:19:\"expiring_admin_send\";s:1:\"3\";s:22:\"expiring_admin_subject\";s:41:\"[memberlevel]: Upcoming Member Expiration\";s:22:\"expiring_admin_message\";s:248:\"<p>There is an upcoming member expiration with the following information:</p><p>Membership Level: [memberlevel]<br>Expiration: [expirydate]</p><p>Username: [username]<br>Name: [firstname] [lastname]<br>Email: [email]</p><p>Login URL: [loginurl]</p>\";s:26:\"expiring_notification_user\";s:1:\"1\";s:18:\"expiring_user_send\";s:1:\"3\";s:25:\"expiring_user_sender_name\";s:5:\"admin\";s:26:\"expiring_user_sender_email\";s:24:\"dev-email@flywheel.local\";s:21:\"expiring_user_subject\";s:41:\"Expiring Membership Subscription Reminder\";s:21:\"expiring_user_message\";s:128:\"<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] is about to expire on [expirydate].</p><p>Thank you.</p>\";s:19:\"cancel_notification\";s:1:\"0\";s:18:\"cancel_sender_name\";s:5:\"admin\";s:19:\"cancel_sender_email\";s:24:\"dev-email@flywheel.local\";s:14:\"cancel_subject\";s:23:\"[memberlevel] Cancelled\";s:14:\"cancel_message\";s:112:\"<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been cancelled.</p><p>Thank you.</p>\";s:21:\"uncancel_notification\";s:1:\"0\";s:20:\"uncancel_sender_name\";s:5:\"admin\";s:21:\"uncancel_sender_email\";s:24:\"dev-email@flywheel.local\";s:16:\"uncancel_subject\";s:25:\"[memberlevel] Uncancelled\";s:16:\"uncancel_message\";s:114:\"<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been uncancelled.</p><p>Thank you.</p>\";s:8:\"noexpire\";i:1;s:2:\"ID\";i:1676637461;}}',  'yes'),
(49,  'pending_period', '', 'yes'),
(50,  'rss_secret_key', '11bf1f8baaf20d0081500e1267d9bf65', 'yes'),
(51,  'disable_rss_enclosures', '1',  'yes'),
(52,  'auto_login_after_confirm', '1',  'yes'),
(53,  'reg_cookie_timeout', '600',  'yes'),
(54,  'payperpost_ismember',  '0',  'yes'),
(55,  'protect_after_more', '0',  'yes'),
(56,  'auto_insert_more', '0',  'yes'),
(57,  'private_tag_protect_msg',  '<i>[Content protected for [level] members only]</i>',  'yes'),
(58,  'reverse_private_tag_protect_msg',  '<i>[Content not available for [level] members ]</i>',  'yes'),
(59,  'members_can_update_info',  '1',  'yes'),
(60,  'unsub_notification', '1',  'yes'),
(61,  'html_tags_support',  '0',  'yes'),
(62,  'incomplete_notification_first',  '1',  'yes'),
(63,  'incomplete_notification_add',  '3',  'yes'),
(64,  'incomplete_notification_add_every',  '24', 'yes'),
(65,  'expiring_notification_days', '3',  'yes'),
(66,  'show_linkback',  '0',  'yes'),
(67,  'unsubscribe_expired_members',  '0',  'yes'),
(68,  'dont_send_reminder_email_when_unsubscribed', '0',  'yes'),
(69,  'redirect_existing_member', '0',  'yes'),
(70,  'prevent_ppp_deletion', '1',  'yes'),
(71,  'password_hinting', '0',  'yes'),
(72,  'enable_short_registration_links',  '0',  'yes'),
(73,  'enable_login_redirect_override', '1',  'yes'),
(74,  'enable_logout_redirect_override',  '1',  'yes'),
(75,  'login_limit_notify', '1',  'yes'),
(76,  'enable_retrieve_password_override',  '0',  'yes'),
(77,  'strongpassword', '0',  'yes'),
(78,  'disable_legacy_reg_shortcodes',  '0',  'yes'),
(79,  'disable_legacy_private_tags',  '0',  'yes'),
(80,  'email_per_hour', '100',  'yes'),
(81,  'email_per_minute', '30', 'yes'),
(82,  'WLM_ContentDrip_Option', '', 'yes'),
(83,  'file_protection_ignore', 'jpg, jpeg, png, gif, bmp, css, js',  'yes'),
(84,  'mask_passwords_in_emails', '1',  'yes'),
(85,  'email_conf_send_after',  '1',  'yes'),
(86,  'email_conf_how_many',  '3',  'yes'),
(87,  'email_conf_send_every',  '24', 'yes'),
(88,  'register_email_subject', 'Congrats - You are registered!', 'yes'),
(89,  'register_email_body',  '<p>[firstname],</p><p>You have successfully registered as a [memberlevel] member.</p><p>Please keep this information safe as it contains your username and password.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>You are invited to login and check things out.</p><p>We hope to see you inside.</p>', 'yes'),
(90,  'lostinfo_email_subject', 'Your membership password reset request', 'yes'),
(91,  'lostinfo_email_message', '<p>Dear [firstname],</p><p>Our records show that you recently asked to reset the password for your account.</p><p>Your current information is:<br>Username: [username]<br>Membership: [memberlevel]</p><p>As a security measure all passwords are encrypted in our database and cannot be retrieved. However, you can easily reset it.</p><p>To reset your password visit the following URL, otherwise just ignore this email and your membership info will remain the same.</p><p>[reseturl]</p><p>Thanks again!</p>',  'yes'),
(92,  'confirm_email_subject',  'Please confirm your registration', 'yes'),
(93,  'confirm_email_message',  '<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be confirmed before it is active.</p><p>Confirm by visiting the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>** These login details will only give you proper access after the registration has been confirmed.</p><p>Thank You.</p>', 'yes'),
(94,  'email_confirmation_reminder_subject',  'Reminder - Please confirm your registration',  'yes'),
(95,  'email_confirmation_reminder_message',  '<p>Hi [firstname]</p><p>This is a reminder that your registration for [memberlevel] requires confirmation before it is active.</p><p>You can confirm by using the link below:</p><p>[confirmurl]</p><p>Once your account is confirmed, you can login using the following link.</p><p>Login URL: [loginurl]</p><p>Thank You.</p>',  'yes'),
(96,  'email_confirmed_subject',  'Registration confirmed', 'yes'),
(97,  'email_confirmed_message',  '<p>Hi [firstname]</p><p>Your registration for [memberlevel] is confirmed.</p><p>Thank You.</p>', 'yes'),
(98,  'requireadminapproval_email_subject', 'Registration requires admin approval', 'yes'),
(99,  'requireadminapproval_email_message', '<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>', 'yes'),
(100, 'registrationadminapproval_email_subject',  'Registration admin approval',  'yes'),
(101, 'registrationadminapproval_email_message',  '<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>',  'yes'),
(102, 'requireadminapproval_admin_subject', 'A New Member Requires Approval', 'yes'),
(103, 'requireadminapproval_admin_message', '<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>',  'yes'),
(104, 'requireadminapproval_email_paid_subject',  'Registration requires admin approval', 'yes'),
(105, 'requireadminapproval_email_paid_message',  '<p>Hi [firstname]</p><p>Thank You for registering for [memberlevel]</p><p>Your registration must be approved first by the admin before your status can be active.</p><p>Once your account is approved you will be able to login with the following details.</p><p>Your Membership Info:<br>U: [username]<br>P: [password]</p><p>Login URL: [loginurl]</p><p>Please keep this information safe, it is the only email that will include your username and password.</p><p>These login details will only give you proper access when the admin has approved your registration.</p><p>Thank You.</p>', 'yes'),
(106, 'registrationadminapproval_email_paid_subject', 'Registration admin approval',  'yes'),
(107, 'registrationadminapproval_email_paid_message', '<p>Hi [firstname]</p><p>Your registration is now approved by the admin.</p><p>Please use the login details were sent in your initial registration email.</p><p>Thank You.</p>',  'yes'),
(108, 'requireadminapproval_admin_paid_subject',  'A New Member Requires Approval', 'yes'),
(109, 'requireadminapproval_admin_paid_message',  '<p>Approval is required for a new member with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]</p><p>Username: [username]<br>Membership Level: [memberlevel]</p><p>Thank you.</p>',  'yes'),
(110, 'newmembernotice_email_subject',  'A New Member has Registered',  'yes'),
(111, 'newmembernotice_email_message',  '<p>A new member has registered with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]<br>Membership Level: [memberlevel]<br>Username: [username]</p><p>Thank you.</p>', 'yes'),
(112, 'unsubscribe_notice_email_subject', 'Member has Unsubscribed',  'yes'),
(113, 'unsubscribe_notice_email_message', '<p>A member has unsubscribed with the following info:</p><p>First Name: [firstname]<br>Last Name: [lastname]<br>Email: [email]<br>Username: [username]</p><p>Thank you.</p>',  'yes'),
(114, 'incnotification_email_subject',  'Please Complete Your Registration',  'yes'),
(115, 'incnotification_email_message',  '<p>Hi,</p><p>Thank you for registering for [memberlevel]</p><p>Complete your registration by visiting the link below:</p><p>[incregurl]</p><p>Thank you.</p>', 'yes'),
(116, 'expiringnotification_email_subject', 'Expiring Membership Subscription Reminder',  'yes'),
(117, 'expiringnotification_email_message', '<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] is about to expire on [expirydate].</p><p>Thank you.</p>', 'yes'),
(118, 'expiring_admin_subject', '[memberlevel]: Upcoming Member Expiration',  'yes'),
(119, 'expiring_admin_message', '<p>There is an upcoming member expiration with the following information:</p><p>Membership Level: [memberlevel]<br>Expiration: [expirydate]</p><p>Username: [username]<br>Name: [firstname] [lastname]<br>Email: [email]</p><p>Login URL: [loginurl]</p>', 'yes'),
(120, 'cancel_email_subject', '[memberlevel] Cancelled',  'yes'),
(121, 'cancel_email_message', '<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been cancelled.</p><p>Thank you.</p>', 'yes'),
(122, 'uncancel_email_subject', '[memberlevel] Uncancelled',  'yes'),
(123, 'uncancel_email_message', '<p>Hi [firstname],</p><p>Your Membership Subscription for [memberlevel] has been uncancelled.</p><p>Thank you.</p>', 'yes'),
(124, 'onetime_login_link_email_subject', 'Your One-Time Login Link', 'yes'),
(125, 'onetime_login_link_email_message', '<p>Hi [firstname],</p><p>Click the one-time login link below in order to login.</p><p>This link can only be used once.</p><p>[one_time_login_link redirect=\"\"]</p><p>Thank you.</p>',  'yes'),
(126, 'password_hint_email_subject',  'Your Password Hint', 'yes'),
(127, 'password_hint_email_message',  '<p>Hi [firstname] [lastname],</p><p>Your Password Hint is:</p><p>[passwordhint]</p><p>Click the link below to login<br>[loginurl]</p><p>Thank you.</p>', 'yes'),
(128, 'reg_instructions_new', '<p>To complete your registration, please select one of the two options:</p>\n<ol>\n<li>Existing members, please <a href=\"[existinglink]\">click here</a>.</li>\n<li>New members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>\n</ol>',  'yes'),
(129, 'reg_instructions_new_noexisting',  '<p>Please fill in the form below to complete your <b>[level]</b> registration.</p>', 'yes'),
(130, 'reg_instructions_existing',  '<p>To complete your registration, please select one of the two options:</p>\n<ol>\n<li>New members, please <a href=\"[newlink]\">click here</a>.</li>\n<li>Existing members, please fill in the form below to complete<br />your <b>[level]</b> application.</li>\n</ol>', 'yes'),
(131, 'sidebar_widget_css', '/* The Main Widget Enclosure */\n.WishListMember_Widget{ }', 'yes'),
(132, 'login_mergecode_css',  '/* The Main Login Merge Code Enclosure */\n.WishListMember_LoginMergeCode{ }', 'yes'),
(133, 'reg_form_css', '/* CSS Code for the Registration Form */\n\n/* The Main Registration Form Table */\n.wpm_registration{\n clear:both;\n padding:0;\n  margin:10px 0;\n}\n.wpm_registration td{\n  text-align:left;\n}\n/*CSS for Existing Members Login Table*/\n.wpm_existing{\n clear:both;\n padding:0;\n  margin:10px 0;\n}\n/* CSS for Registration Error Messages */\np.wpm_err{\n  color:#f00;\n font-weight:bold;\n}\n\n/* CSS for custom message sent to registration url */\np.wlm_reg_msg_external {\n border: 2px dotted #aaaaaa;\n padding: 10px;\n  background: #fff;\n color: #000;\n}\n\n/* CSS Code for the Registration Instructions Box */\n\n/* The Main Instructions Box */\ndiv#wlmreginstructions{\n background:#ffffdd;\n border:1px solid #ff0000;\n padding:0 1em 1em 1em;\n  margin:0 auto 1em auto;\n font-size:1em;\n  width:450px;\n  color:#333333;\n}\n\n/* Links displayed in the Instructions Box */\n#wlmreginstructions a{\n  color:#0000ff;\n  text-decoration:underline;\n}\n\n/* Numbered Bullets in the Instructions Box */\n#wlmreginstructions ol{\n  margin:0 0 0 1em;\n padding:0 0 0 1em;\n  list-style:decimal;\n background:none;\n}\n\n/* Each Bullet Entry */\n#wlmreginstructions li{\n margin:0;\n padding:0;\n  background:none;\n}', 'yes'),
(134, 'closed_comments_msg',  'You are not allowed to view comments on this post.', 'yes'),
(135, 'active_email_integrations',  'a:0:{}', 'yes'),
(136, 'active_other_integrations',  'a:1:{i:0;s:9:\"gutenberg\";}', 'yes'),
(137, 'login_styling_custom_template',  'template-09',  'yes'),
(138, 'WLMAPIKey',  'mDBSHMqVv3MCO0ZIEJgsFK0VRhmrHRwFfQtPafn9C7WnH4ZuYE', 'yes'),
(139, 'email_sender_name',  'admin',  'yes'),
(140, 'email_sender_address', 'dev-email@flywheel.local', 'yes'),
(141, 'newmembernotice_email_recipient',  'dev-email@flywheel.local', 'yes'),
(142, 'cydecthankyou',  '', 'yes'),
(143, 'cydecsecret',  '', 'yes'),
(144, 'cydec_migrated', '1',  'yes'),
(145, 'file_protection_migrated', '2',  'yes'),
(146, 'parentFolder', '', 'yes'),
(147, 'WishListMemberOptions_MigrateFolderProtectionData',  '1',  'yes'),
(148, 'folder_protection_migrated', '1',  'yes'),
(149, 'payperpost', 'a:10:{s:24:\"custom_afterreg_redirect\";N;s:22:\"afterreg_redirect_type\";s:7:\"message\";s:16:\"afterreg_message\";s:104:\"<p>Hey [wlm_firstname],</p><p>You now have access to [wlm_payperpost].</p><p>Thanks for registering.</p>\";s:13:\"afterreg_page\";N;s:12:\"afterreg_url\";N;s:21:\"custom_login_redirect\";N;s:19:\"login_redirect_type\";s:7:\"message\";s:13:\"login_message\";s:248:\"<p>Hey [wlm_firstname]!</p><p>Welcome back! Below, you\'ll find links to all the important areas of your membership site. Please feel free peruse the site and definitely let us know if you have any questions.</p><p>[Site admin, place links here]</p>\";s:10:\"login_page\";N;s:9:\"login_url\";N;}', 'yes'),
(150, 'LicenseKey', '', 'yes'),
(151, 'LicenseLastCheck', '1676637468', 'yes'),
(152, 'LicenseStatus',  '1',  'yes'),
(153, 'FixedUserAddress', '1',  'yes'),
(154, 'magic_page', '2',  'yes'),
(155, 'import_member_queue_count',  '0',  'yes'),
(156, 'expnotification_last_sent',  '1676637451', 'yes'),
(157, 'wizard/welcome', '2023-02-17 12:37:35',  'yes'),
(158, 'wizard/membership-levels', '2023-02-17 12:37:41',  'yes'),
(159, 'wizard/integrations',  '2023-02-17 12:37:43',  'yes'),
(160, 'login_styling_enable_custom_template', '1',  'yes'),
(163, 'after_login_type', 'internal', 'yes'),
(164, 'after_login_internal', '5',  'yes'),
(165, 'wizard/membership-pages/dashboard/configure',  '5',  'yes'),
(166, 'after_registration_type',  'internal', 'yes'),
(167, 'after_registration_internal',  '6',  'yes'),
(168, 'wizard/membership-pages/onboarding/configure', '6',  'yes'),
(169, 'wizard/membership-pages',  '2023-02-17 12:37:46',  'yes'),
(170, 'wizard_ran', '1',  'yes'),
(171, 'checklist/done/create-membership-level', '1',  'yes'),
(172, 'checklist/done/customize-member-free-registration',  '0',  'yes'),
(173, 'checklist/done/customize-member-paid-registration',  '0',  'yes'),
(174, 'checklist/done/customize-styled-member-login-page',  '0',  'yes'),
(175, 'checklist/done/customize-member-welcome-page', '0',  'yes'),
(176, 'checklist/done/customize-member-dashboard-page', '0',  'yes'),
(177, 'checklist/done/create-membership-content', '1',  'yes'),
(178, 'checklist/video/shown',  '2023-02-17 12:37:47',  'yes');

DROP TABLE IF EXISTS `wp_wlm_presto_player_visits`;
CREATE TABLE `wp_wlm_presto_player_visits` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `visit_time` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `video_id` bigint(20) NOT NULL,
  `percent` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_user_options`;
CREATE TABLE `wp_wlm_user_options` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `option_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `user_id` (`user_id`,`option_name`),
  KEY `autoload` (`autoload`),
  KEY `user_id2` (`user_id`),
  KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_userlevel_options`;
CREATE TABLE `wp_wlm_userlevel_options` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `userlevel_id` bigint(20) NOT NULL,
  `option_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `option_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `userlevel_id` (`userlevel_id`,`option_name`),
  KEY `autoload` (`autoload`),
  KEY `userlevel_id2` (`userlevel_id`),
  KEY `option_name` (`option_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


DROP TABLE IF EXISTS `wp_wlm_userlevels`;
CREATE TABLE `wp_wlm_userlevels` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL,
  `level_id` bigint(20) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `user_id` (`user_id`,`level_id`),
  KEY `user_id2` (`user_id`),
  KEY `level_id` (`level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;


-- 2023-02-17 12:37:57