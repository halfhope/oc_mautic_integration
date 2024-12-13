<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;
use \Mautic\MauticOcIntegration;

class ControllerExtensionModuleMauticIntegrationMauticEditContact extends Controller {
	private $_extension_route = 'extension/module/mautic_integration';

	private $ml; // Mautic Log instance
	private $mi; // Mautic Integration library instance 
	
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->config('mautic');

		$this->ml = new Log($this->config->get('mautic_log_filename'));
		$this->mi = new MauticOcIntegration($registry);
	}

	public function index($args) {
		$this->load->language($this->_extension_route);

		list($auth, $data) = $args;
		if (empty($auth) || empty($data)) {
			return;
		}
		
		$customer_id = $data['customer.customer_id'];
		$this->ml->write(sprintf($this->language->get('text_event_edit_contact'), $customer_id));

		$apiUrl = $this->config->get('mautic_base_url') . '/api/';
		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);

		$mautic_fields_map = $this->config->get('mautic_fields_map');
		$contact_data = $this->mi->mergeFieldDataMapping($mautic_fields_map, $data);

		$createIfNotFound = true;
		$mautic_contact_id = $data['customer.mautic_contact_id'];
		$response = $contactApi->edit($mautic_contact_id, $contact_data, $createIfNotFound);
		
		if (isset($response['contact'])) {
			$mautic_contact_id = $response['contact']['id'];
			$this->mi->setMauticContactIdToCustomerId($customer_id, $mautic_contact_id);

			$this->ml->write(sprintf($this->language->get('text_event_edited_contact'), $mautic_contact_id));
		}

		if (isset($response['errors'])) {
			foreach ($response['errors'] as $key => $error) {
				$this->ml->write($this->language->get('error_common')  . str_replace(["\n","\r\n"], '', $error['message']));
			}
		}
	}
}
