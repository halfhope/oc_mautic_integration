<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;

class ControllerExtensionModuleMauticIntegration extends Controller {
	
	private $_route = 'extension/module/mautic_integration';
	private $_model = 'model_extension_module_mautic_integration';
	private $mautic_log;
	
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->config('mautic');

		$this->mautic_log = new Log($this->config->get('mautic_log_filename'));
	}

	public function index($setting) {
		return true;
	}
	
	public function webHookHandler() {
		$this->load->model($this->_route);
		$this->load->config('mautic');

		$webhooks = $this->config->get('mautic_webhooks');
		$webhook_secret = $this->config->get('mautic_webhook_secret');

		$raw_data = file_get_contents('php://input');

		$recieved_signature = isset($this->request->server['HTTP_WEBHOOK_SIGNATURE']) ? $this->request->server['HTTP_WEBHOOK_SIGNATURE'] : '';
		$computed_signature = base64_encode(hash_hmac('sha256', $raw_data, $webhook_secret, true));

		$check_webhook = isset($this->request->get['webhook']) && in_array($this->request->get['webhook'], $webhooks);
		
		if ($recieved_signature === $computed_signature) {
			$check_secret = true;
			$this->mautic_log->write("[WEBHOOK] Auth successful");
		} else {
			$check_secret = false;
			$this->mautic_log->write("[WEBHOOK] Cannot auth!");
		}

		if ($check_webhook && $check_secret) {	
			$this->mautic_log->write("[WEBHOOK] {$this->request->get['webhook']} fired");

			switch ($this->request->get['webhook']) {
				case 'OnContactUpdated':
					$this->OnContactUpdatedHandler($raw_data);
					break;
			}
			
		}
	}
	
	public function OnContactUpdatedHandler($raw_data) {

		$response = json_decode($raw_data, true);

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
			$customer_id = $this->{$this->_model}->getCustomerIdByEmail($email);

			$this->mautic_log->write("[WEBHOOK] Update customer with customer_id: {$customer_id}");

			$mautic_fields_map = $this->config->get('mautic_fields_map');
			$customer = $this->mergeFieldDataMapping($mautic_fields_map, $contact_data, true);
	
			$field_data = [];
			foreach ($customer as $key => $value) {
				$exp = explode('.', $key);
				$field_data[$exp[0]][$exp[1]] = $value;
			}
			
			if (isset($field_data['customer'])) {
				$this->{$this->_model}->updateCustomerData($customer_id, $field_data['customer']);
			}

			if (isset($field_data['address'])) {
				$this->{$this->_model}->updateCustomerAddressData($customer_id, $field_data['address']);
			}
			
		}
	}

	public function eventHandler($args) {
		$event_trigger = $args[0];
		$customer_id = $args[1];

		if ($this->config->get('mautic_status')) {
			$this->load->model($this->_route);
			
			$avaliableEvents = $this->config->get('mautic_event_triggers');
			
			if (in_array($event_trigger, $avaliableEvents)) {
				$customer_data = $this->{$this->_model}->getCustomerData($customer_id);
				
				$is_subscribed = $customer_data['customer.newsletter'];

				$this->load->language($this->_route);
				
				$auth = $this->apiAuth();
				if ($auth->isAuthorized()) {
					$this->mautic_log->write("[EVENT] {$event_trigger} fired");
					switch ($event_trigger) {
						case 'newCustomer':
						case 'adminNewCustomer':
							if ($is_subscribed) {
								$this->mauticApiAddContact($auth, $customer_data);
							}
							break;
						
						case 'editCustomer':
						case 'addAddress':
						case 'editAddress':
						case 'adminEditCustomer':
						case 'userSubscribe':
						case 'userUnsubscribe':
							if ($is_subscribed) {
								$this->mauticApiEditContact($auth, $customer_data);
							} else {
								$this->mauticApiDeleteContact($auth, $customer_data);
							}
							break;

						case 'adminDeleteCustomer':
							$this->mauticApiDeleteContact($auth, $customer_data);
							break;
					}
				} else {
					$this->mautic_log->write("[EVENT] Not authorized");
				}
			}
		}
	}
	
	private function mergeFieldDataMapping($map, $data, $invert = false) {
		$result = [];
		foreach ($map as $field_data) {
			$m_field = $field_data['m'];
			$o_field = $field_data['o'];

			if ($invert) {
				$result[$o_field] = $data[$m_field];
			} else {
				$result[$m_field] = $data[$o_field];
			}
		}
		return $result;
	}

	private function logErrors($errors) {
		foreach ($errors as $key => $error) {
			$this->mautic_log->write("[ERROR] Code " . $error['code'] . " " . str_replace(["\n","\r\n"], '', $error['message']));
		}
	}
	
	private function mauticApiAddContact($auth, $data) {

		$apiUrl = $this->config->get('mautic_base_url') . '/api/';

		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);

		$mautic_fields_map = $this->config->get('mautic_fields_map');		
		$contact_data = $this->mergeFieldDataMapping($mautic_fields_map, $data);

		$customer_id = $data['customer.customer_id'];
		$this->mautic_log->write("ADD CONTACT FOR customer_id: {$customer_id}");

		$response = $contactApi->create($contact_data);
		
		if (isset($response['contact'])) {
			$contact = $response['contact'];
			$mautic_contact_id = $contact['id'];
			$this->{$this->_model}->setMauticContactIdToCustomerId($customer_id, $mautic_contact_id);

			$this->mautic_log->write("ADDED contact_id: {$mautic_contact_id}");
		}

		if (isset($response['errors'])) {
			$this->logErrors($response['errors']);
		}
	}

	private function mauticApiEditContact($auth, $data) {

		$apiUrl = $this->config->get('mautic_base_url') . '/api/';

		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);

		$mautic_fields_map = $this->config->get('mautic_fields_map');
		$contact_data = $this->mergeFieldDataMapping($mautic_fields_map, $data);

		$createIfNotFound = true;
		$mautic_contact_id = $data['customer.mautic_contact_id'];
		$response = $contactApi->edit($mautic_contact_id, $contact_data, $createIfNotFound);
		
		$customer_id = $data['customer.customer_id'];
		$this->mautic_log->write("EDIT CONTACT FOR customer_id: {$customer_id}");

		if (isset($response['contact'])) {
			$mautic_contact_id = $response['contact']['id'];
			$this->{$this->_model}->setMauticContactIdToCustomerId($customer_id, $mautic_contact_id);

			$this->mautic_log->write("EDITED contact_id: {$mautic_contact_id}");
		}

		if (isset($response['errors'])) {
			$this->logErrors($response['errors']);
		}
	}

	private function mauticApiDeleteContact($auth, $data) {
		
		$apiUrl = $this->config->get('mautic_base_url') . '/api/';

		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);

		$customer_id = $data['customer.customer_id'];
		$mautic_contact_id = $this->{$this->_model}->getMauticContactIdByCustomerId($customer_id);

		if ($mautic_contact_id) {
			$response = $contactApi->delete($mautic_contact_id);
			$this->{$this->_model}->clearMauticContactIdToCustomerId($customer_id);

			$this->mautic_log->write("DELETE CONTACT FOR customer_id: {$customer_id}");
		}

		if (isset($response['contact'])) {
			$this->mautic_log->write("DELETED contact_id: {$mautic_contact_id}");
		}

		if (isset($response['errors'])) {
			$this->logErrors($response['errors']);
		}
	}
	private function apiAuth() {
		session_start();

		$this->load->model($this->_route);
		$settings = $this->{$this->_model}->getApiSettings();

		$initAuth = new ApiAuth();
		$auth     = @$initAuth->newAuth($settings);

		try {
			if (@$auth->validateAccessToken()) {
				return $auth;
			} else {
				$this->mautic_log->write("INVALID ACCESS_TOKEN");
				return $auth;
			}
		} catch (Exception $e) {
			// Do Error handling
		}
	}	
}