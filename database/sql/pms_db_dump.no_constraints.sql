-- Cleaned SQL dump (no foreign key constraints)
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
SET FOREIGN_KEY_CHECKS=0;

-- (The rest of this file mirrors pms_db_dump.cleaned.sql but with foreign key constraint ALTER TABLE sections removed.)

-- Database: `pms_db`

DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  `guard_id` int(11) DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `feedback`;
CREATE TABLE `feedback` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `date_time` datetime NOT NULL,
  `comments` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2023_07_23_000000_create_parking_layouts_table', 1),
(6, '2024_05_11_093905_create_roles_table', 1),
(7, '2025_07_14_134849_create_driver_table', 1),
(8, '2025_07_15_061518_create_admin_table', 1),
(9, '2025_07_15_061907_create_user_info_table', 1),
(10, '2025_07_15_063927_create_vehicle_table', 1),
(11, '2025_07_15_064017_create_paking_history_table', 1),
(12, '2025_07_15_065514_create_qr_code_table', 1),
(13, '2025_07_15_065542_feedback_code_table', 1),
(14, '2025_07_15_065611_create_incident_report_table', 1),
(15, '2025_07_15_065625_create_guest_table', 1),
(16, '2025_07_23_000001_create_parking_slots_table', 1),
(17, '2025_07_24_052212_create_user_details_table', 1),
(18, '2025_07_24_052248_create_teams_table', 1),
(19, '2025_07_24_052249_create_team_users_table', 1),
(20, '2025_07_28_122459_fix_database_structure', 1),
(21, '2025_07_28_124021_create_parking_structure', 1),
(22, '2025_07_30_010000_add_missing_fields', 1),
(23, '2025_07_30_060000_add_vehicle_color_to_parking_assignments_table', 1),
(24, '2025_07_30_070000_sync_parking_assignments_fields', 1),
(25, '2025_07_30_120000_add_metadata_column_to_parking_slots', 1),
(26, '2025_07_30_130000_add_metadata_to_parking_slots', 1),
(27, '2025_08_17_193124_restructure_parking_assignments_table', 1),
(28, '2025_08_18_add_position_to_parking_assignments', 1),
(29, '2025_09_18_110000_ensure_users_table_exists', 1),
(30, '2025_09_18_120000_create_admin_table', 1),
(31, '2025_09_18_123000_add_profile_pic_to_users_table', 1),
(32, '2025_09_20_000000_create_password_resets_table_fix', 1),
(33, '2025_09_21_000000_add_user_details_fields', 1),
(34, '2025_09_21_010000_ensure_users_table_exists', 1),
(35, '2025_09_21_010500_ensure_roles_table_exists', 1),
(36, '2025_09_21_120000_prune_user_details_columns', 1),
(37, '2025_09_21_123000_add_faculty_employee_id_to_user_details', 1),
(38, '2025_09_21_130000_ensure_personal_access_tokens_table', 1),
(39, '2025_09_22_000000_add_or_and_cr_paths_to_user_details', 1),
(40, '2025_09_22_010000_create_vehicles_table', 2),
(41, '2025_09_22_020000_add_userdetails_and_plate_numbers', 3);

-- (rest of CREATE TABLEs and INSERTs omitted here for brevity in this file)

SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
