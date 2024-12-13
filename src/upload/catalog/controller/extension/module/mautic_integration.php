<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;
use \Mautic\MauticOcIntegration;

class ControllerExtensionModuleMauticIntegration extends Controller {
	
	private $_route = 'extension/module/mautic_integration';
	private $_model = 'model_extension_module_mautic_integration';

	private $ml; // Mautic Log instance
	private $mi; // Mautic Integration library instance 
	
	public function __construct($registry) {
		parent::__construct($registry);
		$this->load->config('mautic');

		$this->ml = new Log($this->config->get('mautic_log_filename'));
		$this->mi = new MauticOcIntegration($registry);
	}

	public function webHookHandler() {
		if ($this->config->get('mautic_status')) {
			
			$this->load->model($this->_route);
			$this->load->config('mautic');
			$this->load->language($this->_route);
			
			$webhooks = $this->config->get('mautic_webhooks');
			$webhook_secret = $this->config->get('mautic_webhook_secret');

			$raw_data = file_get_contents('php://input');

			$recieved_signature = isset($this->request->server['HTTP_WEBHOOK_SIGNATURE']) ? $this->request->server['HTTP_WEBHOOK_SIGNATURE'] : '';
			$computed_signature = base64_encode(hash_hmac('sha256', $raw_data, $webhook_secret, true));

			$check_webhook = isset($this->request->get['webhook']) && in_array($this->request->get['webhook'], $webhooks);
			
			$check_secret = $recieved_signature === $computed_signature;

			if ($check_webhook && $check_secret) {	
				$webhook = $this->request->get['webhook'];

				$this->ml->write(sprintf($this->language->get('text_webhook_fired'), $webhook));
				
				switch ($webhook) {
					case 'onContactUpdated':
						$this->load->controller($this->_route . '/opencartUpdateCustomerData' , json_decode($raw_data, true));
						break;
				}
			}
		}
	}
	
	public function eventHandler($args) {
		list($event_trigger, $customer_id) = $args;

		if ($this->config->get('mautic_status')) {

			$this->load->model($this->_route);
			$this->load->language($this->_route);
			
			$avaliableEvents = $this->config->get('mautic_event_triggers');
			
			if (in_array($event_trigger, $avaliableEvents)) {
				$customer_data = $this->mi->getCustomerData($customer_id);
				
				$is_subscribed = $customer_data['customer.newsletter'];

				$auth = $this->apiAuth();
				if ($auth->isAuthorized()) {
					
					$this->ml->write(sprintf($this->language->get('text_event_fired'), $event_trigger));

					switch ($event_trigger) {
						case 'newCustomer':
						case 'adminNewCustomer':
							if ($is_subscribed) {
								$this->load->controller($this->_route . '/mauticAddContact', [$auth, $customer_data]);
							}
							break;
						
						case 'editCustomer':
						case 'addAddress':
						case 'editAddress':
						case 'adminEditCustomer':
						case 'userSubscribe':
						case 'userUnsubscribe':
							if ($is_subscribed) {
								$this->load->controller($this->_route . '/mauticEditContact', [$auth, $customer_data]);
							} else {
								$this->load->controller($this->_route . '/mauticDeleteContact', [$auth, $customer_data]);
							}
							break;

						case 'adminDeleteCustomer':
							$this->load->controller($this->_route . '/mauticDeleteContact', [$auth, $customer_data]);
							break;
					}
				} else {
					$this->ml->write($this->language->get('error_auth_failed'));
				}
			}
		}
	}
	
	private function apiAuth() {
		session_start();

		$this->load->model($this->_route);
		$settings = $this->mi->getApiSettings();

		$initAuth = new ApiAuth();
		$auth     = @$initAuth->newAuth($settings);

		try {
			if (@$auth->validateAccessToken()) {
				return $auth;
			} else {
				$this->ml->write($this->language->get('error_auth_failed'));
				return $auth;
			}
		} catch (Exception $e) {
			// Do Error handling
		}
	}	
}