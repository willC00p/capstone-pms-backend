-- Add foreign key constraints for PMS DB
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `parking_slots`
  ADD CONSTRAINT `parking_slots_layout_id_foreign` FOREIGN KEY (`layout_id`) REFERENCES `parking_layouts` (`id`) ON DELETE CASCADE;

ALTER TABLE `parking_assignments`
  ADD CONSTRAINT `parking_assignments_parking_slot_id_foreign` FOREIGN KEY (`parking_slot_id`) REFERENCES `parking_slots` (`id`) ON DELETE CASCADE;

ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_user_details_id_foreign` FOREIGN KEY (`user_details_id`) REFERENCES `user_details` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vehicles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

SET FOREIGN_KEY_CHECKS=1;
