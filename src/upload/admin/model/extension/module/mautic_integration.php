<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

class ModelExtensionModuleMauticIntegration extends Model {
	
	public function install() {
		$this->checkAndAddContactIdField();
		return true;
	}
	
	public function uninstall() {
		$this->removeContactIdField();
		return true;
	}
	
	public function checkAndAddContactIdField() {
		$columns_query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "customer");
		$columns = $columns_query->rows;
		
		$exists = false;
		foreach ($columns as $key => $value) {
			if ($value['Field'] === 'mautic_contact_id') {
				$exists = true;
			}
		}

		if (!$exists) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` ADD `mautic_contact_id` int(11) NULL");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` ADD INDEX `mautic_contact_id` (`mautic_contact_id`)");
		}
	}

	public function removeContactIdField() {
		$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer`DROP `mautic_contact_id`");
	}
}