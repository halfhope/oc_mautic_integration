<?php

namespace Mautic;

final class MauticOcIntegration {

	private $db;
	private $config;
	private $url;
	private $session;

	private $_extension_route = 'extension/module/mautic_integration';

	public function __construct($registry) {
		$this->db = $registry->get('db');
		$this->config = $registry->get('config');
		$this->url = $registry->get('url');
		$this->session = $registry->get('session');
	}

	private static function addArrayKeyPrefix($prefix, $data) {
		$result = [];
		foreach ($data as $key => $value) {
			$result[$prefix . '.' . $key] = $value;
		}
		return $result;
	}

	public static function mergeFieldDataMapping($map, $data, $invert = false) {
		$result = [];
		foreach ($map as $field_data) {
			$m_field = $field_data['m'];
			$o_field = $field_data['o'];

			if ($invert) {
				if (isset($data[$m_field])) {
					$result[$o_field] = $data[$m_field];
				}
			} else {
				if (isset($data[$o_field])) {
					$result[$m_field] = $data[$o_field];
				}
			}
		}
		return $result;
	}

	public function getApiSettings() {
		return [
			'baseUrl'          		=> $this->config->get('mautic_base_url'),
			'version'          		=> $this->config->get('mautic_auth_version'),
			'clientKey'        		=> $this->config->get('mautic_client_id'),
			'clientSecret'     		=> $this->config->get('mautic_client_secret'),
			'callback'         		=> html_entity_decode($this->url->link($this->_extension_route . '/authMautic', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8'),
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

		if (isset($address['country_id'])) {	
			$country_query = $this->db->query("SELECT `name` FROM " . DB_PREFIX . "country WHERE country_id = " . (int) $address['country_id']);
			$country = $country_query->row;
		} else {
			$country = [];
		}

		if (isset($address['country_id']) && isset($address['zone_id'])) {
			$zone_query = $this->db->query("SELECT `name`, `code` FROM " . DB_PREFIX . "zone WHERE country_id = " . (int) $address['country_id'] . " AND zone_id = " . (int) $address['zone_id']);
			$zone = $zone_query->row;
		} else {
			$zone = [];
		}

		return array_merge(
			$this->addArrayKeyPrefix('customer', $customer),
			$this->addArrayKeyPrefix('store', $store),
			$this->addArrayKeyPrefix('language', $language),
			$this->addArrayKeyPrefix('address', $address),
			$this->addArrayKeyPrefix('country', $country),
			$this->addArrayKeyPrefix('zone', $zone),
		);
	}
	
	public function getCustomersIds($subscribers_only = false, $synced = false) {
		$customers_query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE email <> '' " . (($subscribers_only) ? "AND newsletter = 1 " : "") . "AND mautic_contact_id IS " . (($synced) ? "NOT NULL " : "NULL") . " ORDER BY customer_id");
		return $customers_query->rows;
	}

	public function getCustomersCount($subscribers_only = false, $synced = false) {
		$customers_query = $this->db->query("SELECT count(customer_id) as total FROM " . DB_PREFIX . "customer WHERE email <> '' " . (($subscribers_only) ? "AND newsletter = 1 " : "") . "AND mautic_contact_id IS " . (($synced) ? "NOT NULL " : "NULL") . " ORDER BY customer_id");
		return $customers_query->row['total'];
	}

	public function updateCustomerData($customer_id, $data) {

		$fields_sql = [];
		foreach ($data as $field => $value) {
			$fields_sql[] = $field . " = '" . $value . "'";
		}

		$this->db->query("UPDATE " . DB_PREFIX . "customer SET " . implode(',', $fields_sql) . " WHERE customer_id = " . (int) $customer_id);
		return true;
	}
	
	public function updateCustomerAddressData($customer_id, $data) {
		$address_query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "customer	WHERE customer_id = " . (int) $customer_id);
		$address_id = $address_query->row;

		if ($address_id) {

			$fields_sql = [];
			foreach ($data as $field => $value) {
				$fields_sql[] = $field . " = '" . $value . "'";
			}
			
			$this->db->query("UPDATE " . DB_PREFIX . "address SET " . implode(',', $fields_sql) . " WHERE address_id  = '" . (int) $address_id . "' AND customer_id = '" . (int) $customer_id . "'");
		}
		return true;
	}

	public function getMauticContactIdByCustomerId($customer_id) {
		$query = $this->db->query("SELECT mautic_contact_id FROM " . DB_PREFIX . "customer WHERE customer_id = " . $customer_id);
		return $query->row['mautic_contact_id'];
	}

	public function getCustomerIdByEmail($email) {
		$query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");
		if (isset($query->row['customer_id'])) {
			return $query->row['customer_id'];
		} else {
			return false;
		}
	}

	public function setMauticContactIdToCustomerId($customer_id, $mautic_contact_id) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "customer SET mautic_contact_id = " . (int) $mautic_contact_id . " WHERE customer_id = " . (int) $customer_id);
		return true;
	}

	public function clearMauticContactIdToCustomerId($customer_id) {
		$query = $this->db->query("UPDATE " . DB_PREFIX . "customer SET mautic_contact_id = NULL WHERE customer_id = " . (int) $customer_id);
		return true;
	}

}
