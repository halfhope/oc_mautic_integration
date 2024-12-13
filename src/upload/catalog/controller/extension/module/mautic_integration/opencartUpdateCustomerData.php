<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;
use \Mautic\MauticOcIntegration;

class ControllerExtensionModuleMauticIntegrationOpencartUpdateCustomerData extends Controller {
	private $_extension_route = 'extension/module/mautic_integration';

	private $ml; // Mautic Log instance
	private $mi; // Mautic Integration library instance 
	
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->config('mautic');

		$this->ml = new Log($this->config->get('mautic_log_filename'));
		$this->mi = new MauticOcIntegration($registry);
	}

	public function index($response) {
		$this->load->language($this->_extension_route);

		if (empty($response)) {
			return;
		}
		
		$contacts = $response['mautic.lead_post_save_update'];
		
		foreach ($contacts as $contact_data) {
			$contact = $contact_data['contact'];
			$mautic_contact_id = $contact['id'];
			
			$contact_data = [];
			foreach ($contact['fields'] as $field_group_key => $field_group_fields) {
				foreach ($field_group_fields as $field) {
					$contact_data[$field['alias']] = $field['value'];
				}
			}
			
			$email = $contact_data['email'];
			$customer_id = $this->mi->getCustomerIdByEmail($email);

			$this->ml->write(sprintf($this->language->get('text_webhook_update_customer'), $mautic_contact_id, $customer_id));

			$mautic_fields_map = $this->config->get('mautic_fields_map');
			$customer = $this->mi->mergeFieldDataMapping($mautic_fields_map, $contact_data, true);
	
			$field_data = [];
			foreach ($customer as $key => $value) {
				$exp = explode('.', $key);
				$field_data[$exp[0]][$exp[1]] = $value;
			}
			
			if (isset($field_data['customer'])) {
				$this->mi->updateCustomerData($customer_id, $field_data['customer']);
			}

			if (isset($field_data['address'])) {
				$this->mi->updateCustomerAddressData($customer_id, $field_data['address']);
			}
			
		}
	}
}
