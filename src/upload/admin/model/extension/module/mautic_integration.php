<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

class ModelExtensionModuleMauticIntegration extends Model {

	public 	$_route 		= 'extension/module/mautic_integration';
	
	public function install() {
		$this->checkAndAddContactIDField();
		return true;
	}
	
	public function uninstall() {
		return true;
	}

	private function addArrayKeyPrefix($prefix, $data) {
		$result = [];
		foreach ($data as $key => $value) {
			$result[$prefix . '.' . $key] = $value;
		}
		return $result;
	}

	public function getMauticContactIdByCustomerId($customer_id) {
		$query = $this->db->query("SELECT mautic_contact_id FROM " . DB_PREFIX . "customer WHERE customer_id = " . $customer_id);
		return $query->row['mautic_contact_id'];
	}

	public function setMauticContactIdToCustomerId($customer_id, $mautic_contact_id) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "customer SET mautic_contact_id = " . (int) $mautic_contact_id . " WHERE customer_id = " . (int) $customer_id);
		return true;
	}

	public function getCustomerIdByEmail($email) {
		$query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		return $query->row['customer_id'];
	}

	private function checkAndAddContactIDField() {
		$columns_query = $this->db->query("SHOW COLUMNS FROM " . DB_PREFIX . "customer");
		$columns = $columns_query->rows;
		
		$exists = false;
		foreach ($columns as $key => $value) {
			if ($value['Field'] === 'mautic_contact_id') {
				$exists = true;
			}
		}

		if (!$exists) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` ADD `mautic_contact_id` int(11) NOT NULL");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer` ADD INDEX `mautic_contact_id` (`mautic_contact_id`)");
		}
	}
	
	public function getApiSettings() {
		return [
			'baseUrl'          		=> $this->config->get('mautic_base_url'),
			'version'          		=> $this->config->get('mautic_auth_version'),
			'clientKey'        		=> $this->config->get('mautic_client_id'),
			'clientSecret'     		=> $this->config->get('mautic_client_secret'),
			'callback'         		=> html_entity_decode($this->url->link($this->_route . '/authMautic', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8'),
			// authorized
			'accessToken'			=> $this->config->get('mautic_access_token'),
			'accessTokenSecret'		=> $this->config->get('mautic_access_token_expires'),
			'accessTokenExpires'	=> $this->config->get('mautic_access_token_type'),
			// [BUG] https://github.com/PipedreamHQ/pipedream/issues/6176
			// solution - increase token lifetime on universe lifetime
			'refresh_token'			=> $this->config->get('mautic_access_refresh_token'),
		];
	}

	public function getCustomerData($customer_id) {
		
		$customer_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = " . (int) $customer_id);
		$customer = $customer_query->row;

		$customer_group_query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "customer_group_description WHERE customer_group_id = " . (int) $customer['customer_group_id'] . " AND language_id = " . (int) $this->config->get('config_language_id') );
		$customer_group = $customer_group_query->row;

		$store_query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "store WHERE store_id = " . $customer['store_id']);
		$store = $store_query->row;

		$language_query = $this->db->query("SELECT `name`, `code` FROM " . DB_PREFIX . "language WHERE language_id = " . (int) $customer['language_id']);
		$language = $language_query->row;

		$address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = " . (int) $customer['address_id']);
		$address = $address_query->row;

		$country_query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "country WHERE country_id = " . (int) $address['country_id']);
		$country = $country_query->row;

		$zone_query = $this->db->query("SELECT `name`, `code` FROM " . DB_PREFIX . "zone WHERE country_id = " . (int) $address['country_id'] . " AND zone_id = " . (int) $address['zone_id']);
		$zone = $zone_query->row;

		return array_merge(
			$this->addArrayKeyPrefix('customer', $customer),
			$this->addArrayKeyPrefix('store', $store),
			$this->addArrayKeyPrefix('language', $language),
			$this->addArrayKeyPrefix('address', $address),
			$this->addArrayKeyPrefix('country', $country),
			$this->addArrayKeyPrefix('zone', $zone),
		);
	}
	
	public function getAllCustomersData($subscribed_only = false) {
		
		$customers_query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer " . (($subscribed_only) ? ' WHERE newsletter = 1' : ""));

		$result = [];
		foreach ($customers_query->rows as $customer) {
			$result[] = $this->getCustomerData($customer['customer_id']);
		}

		return $result;
	}
}