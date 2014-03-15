SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `glpi_plugin_rackview_racks`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `glpi_plugin_rackview_racks` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT 'Rack-ID' ,
  `name` VARCHAR(200) NULL COMMENT 'Name (Short description) of the rack' ,
  `description` TEXT NULL COMMENT '(Long) description of the rack, notes, etc.' ,
  `locations_id` INT NULL COMMENT 'Binding to a GLPI-location' ,
  `size` INT NULL COMMENT 'Size in Rack-Units\n' ,
  `entities_id` INT NULL COMMENT 'Link to glpi entities' ,
  `notepad` TEXT NULL COMMENT 'For making the notes-tab available' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Definition of a rack';


-- -----------------------------------------------------
-- Table `glpi_plugin_rackview_mount`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `glpi_plugin_rackview_mount` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'Mount-ID' ,
  `object_type` VARCHAR(200) NULL COMMENT 'Type of object, that is mounted (Computer, Network equipment,...)' ,
  `object_id` INT(11) NULL COMMENT 'ID of the mounted object' ,
  `rack_id` INT(11) NULL COMMENT 'Binding to the rack' ,
  `startu` INT NULL COMMENT 'Starting Unit (lower unit)' ,
  `horizontal` INT NULL COMMENT 'Horizontal placement (for placing towers vertically in a rack)\n\n0 - full\n1 - left\n2 - center\n3 - right' ,
  `depth` INT NULL COMMENT 'Depth allocation (how much space is used)\n\n0 - full\n1 - front\n2 - back\n\nThere can be two objects placed on the same Rack unit, one in the front and one in the back.\n\nTypically used for short switches or power panels' ,
  `description` TEXT NULL COMMENT 'Description of this mount (special notes,...)' ,
  `mount_size` INT NULL COMMENT 'Size (in RackUnits) for this specific mount\n\nEnables users to use a default rack size and a mount specific size. This is useful for generic objects like cover plates that have different sizes in different mounts.' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'Definition of a rack mounted object (computer, network equip' /* comment truncated */;


-- -----------------------------------------------------
-- Table `glpi_plugin_rackview_object`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `glpi_plugin_rackview_object` (
  `object_type` VARCHAR(200) NOT NULL COMMENT 'Type of object' ,
  `object_id` INT(11) NOT NULL COMMENT 'ID of object' ,
  `size` INT NULL COMMENT 'Size of Object in Rack Units' ,
  PRIMARY KEY (`object_type`, `object_id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Placeholder table for view `glpi_plugin_rackview_objectmounts`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `glpi_plugin_rackview_objectmounts` (`id` INT, `type` INT, `mounts` INT);

-- -----------------------------------------------------
-- View `glpi_plugin_rackview_objectmounts`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `glpi_plugin_rackview_objectmounts`;
CREATE  OR REPLACE VIEW `glpi_plugin_rackview_objectmounts` AS
SELECT 
    mount.object_id as id,
    mount.object_type as type,
    GROUP_CONCAT(rack.name) as mounts
FROM glpi_plugin_rackview_racks rack
JOIN glpi_plugin_rackview_mount mount
ON (rack.id = mount.rack_id)
GROUP BY 
    mount.object_id,
    mount.object_type;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
