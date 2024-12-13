<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;
use \Mautic\MauticOcIntegration;

class ControllerExtensionModuleMauticIntegrationMauticDeleteContact extends Controller {
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
		$this->ml->write(sprintf($this->language->get('text_event_delete_contact'), $customer_id));

		$apiUrl = $this->config->get('mautic_base_url') . '/api/';
		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);

		$mautic_contact_id = $this->mi->getMauticContactIdByCustomerId($customer_id);

		if ($mautic_contact_id) {
			$response = $contactApi->delete($mautic_contact_id);
			$this->mi->clearMauticContactIdToCustomerId($customer_id);
		}

		if (isset($response['contact'])) {
			$this->ml->write(sprintf($this->language->get('text_event_deleted_contact'), $mautic_contact_id));
		}

		if (isset($response['errors'])) {
			foreach ($response['errors'] as $key => $error) {
				$this->ml->write($this->language->get('error_common')  . str_replace(["\n","\r\n"], '', $error['message']));
			}
		}
	}
}
