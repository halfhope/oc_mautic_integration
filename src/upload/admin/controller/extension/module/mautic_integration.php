<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;

class ControllerExtensionModuleMauticIntegration extends Controller {

	public 	$_route 		= 'extension/module/mautic_integration';
	public 	$_model 		= 'model_extension_module_mautic_integration';
	private $_version 		= '1.0';

	private $error = [];

	public function install() {
		$this->load->model($this->_route);
		$this->{$this->_model}->install();
	}

	public function uninstall() {
		$this->load->model($this->_route);
		$this->{$this->_model}->uninstall();
	}

	public function index() {
		$this->load->config('mautic');
		$this->load->model($this->_route);
		$this->load->language($this->_route);

		$this->document->setTitle($this->language->get('heading_title'));
		$data['version'] = $this->_version;
	
		$this->load->model('setting/module');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->load->model('setting/setting');
			
			$this->request->post['mautic_base_url'] = rtrim($this->request->post['mautic_base_url'], '\/');

			$this->request->post['mautic_fields'] = htmlspecialchars_decode($this->request->post['mautic_fields'], ENT_QUOTES);

			unset($this->request->post['mautic_access_token_expires_formatted']);
			unset($this->request->post['mautic_log']);

			$this->model_setting_setting->editSetting('mautic', $this->request->post);
			
			$allowedEvents = !empty($this->request->post['mautic_event_triggers']) ? $this->request->post['mautic_event_triggers'] : [];

			$this->session->data['success'] = $this->language->get('text_success_saved');
			$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}
		
		// AUTH

		if (isset($this->request->post['mautic_base_url'])) {
			$data['mautic_base_url'] = $this->request->post['mautic_base_url'];
		} else {
			$data['mautic_base_url'] =  $this->config->get('mautic_base_url');
		}

		$data['mautic_auth_version'] = 'OAuth2';

		if (isset($this->request->post['mautic_client_id'])) {
			$data['mautic_client_id'] = $this->request->post['mautic_client_id'];
		} else {
			$data['mautic_client_id'] = $this->config->get('mautic_client_id');
		}

		if (isset($this->request->post['mautic_client_secret'])) {
			$data['mautic_client_secret'] = $this->request->post['mautic_client_secret'];
		} else {
			$data['mautic_client_secret'] = $this->config->get('mautic_client_secret');
		}

		$data['mautic_redirect_uri'] = HTTPS_SERVER . 'index.php';
		
		// readonly fields
		
		$data['mautic_auth_url'] = html_entity_decode($this->url->link($this->_route . '/authMautic', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
		$data['mautic_sync_contacts'] = html_entity_decode($this->url->link($this->_route . '/syncContacts', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
		$data['mautic_fetch_mautic_fields'] = html_entity_decode($this->url->link($this->_route . '/fetchMauticFields', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
		$data['mautic_reset_auth_session'] = html_entity_decode($this->url->link($this->_route . '/resetAuthSession', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
		$data['mautic_reset_connection_settings'] = html_entity_decode($this->url->link($this->_route . '/resetConectionSettings', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
			
		$data['mautic_access_token'] = $this->config->get('mautic_access_token');
		
		if (isset($this->request->post['mautic_access_token_expires'])) {
			$data['mautic_access_token_expires'] = $this->request->post['mautic_access_token_expires'];
		} else {
			$data['mautic_access_token_expires'] = $this->config->get('mautic_access_token_expires');
		}

		$data['mautic_access_token_expires_formatted'] = !empty($data['mautic_access_token_expires']) ? date('Y-m-d H:i:s', $data['mautic_access_token_expires']) : '';

		$data['mautic_access_token_type'] = $this->config->get('mautic_access_token_type');

		$data['mautic_access_refresh_token'] = $this->config->get('mautic_access_refresh_token');

		// GENERAL

		if (isset($this->request->post['mautic_status'])) {
			$data['mautic_status'] = $this->request->post['mautic_status'];
		} else {
			$data['mautic_status'] = $this->config->get('mautic_status');
		}

		if (isset($this->request->post['mautic_webhook_secret'])) {
			$data['mautic_webhook_secret'] = $this->request->post['mautic_webhook_secret'];
		} else {
			$data['mautic_webhook_secret'] = $this->config->get('mautic_webhook_secret');
		}

		$data['webhooks'] = $this->config->get('static_webhooks');

		if (isset($this->request->post['mautic_webhooks'])) {
			$data['mautic_webhooks'] = $this->request->post['mautic_webhooks'];
		} else {
			$data['mautic_webhooks'] = $this->config->get('mautic_webhooks');
		}

		$data['webhook_link_example'] = str_replace(
			'admin/', 
			'',
			$this->url->link('extension/module/mautic_integration/webHookHandler', 'webhook=<font color="green">[webhookCode]</font>&secret=<font color="blue">[yourSecretHash]</font>', true)
		);

		$data['event_triggers'] = $this->config->get('static_events');

		if (isset($this->request->post['mautic_event_triggers'])) {
			$data['mautic_event_triggers'] = $this->request->post['mautic_event_triggers'];
		} else {
			$data['mautic_event_triggers'] = $this->config->get('mautic_event_triggers');
		}

		$data['get_mautic_fields'] = html_entity_decode($this->url->link($this->_route . '/getMauticFields', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');

		$data['opencart_fields'] = $this->config->get('static_opencart_fields');
		
		$data['mautic_fields'] = $this->config->get('mautic_fields');
		$data['mautic_fields_decoded'] = empty($data['mautic_fields']) ? [] : json_decode($data['mautic_fields'], true);
		$data['mautic_fields'] = !empty($data['mautic_fields']) ? htmlspecialchars($data['mautic_fields'], ENT_QUOTES, 'UTF-8') : '';

		if (isset($this->request->post['mautic_fields_map'])) {
			$data['mautic_fields_map'] = $this->request->post['mautic_fields_map'];
		} else {
			$data['mautic_fields_map'] = $this->config->get('mautic_fields_map');
		}

		$data['fetch_log'] = html_entity_decode($this->url->link($this->_route . '/fetchLog', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');
		$data['clear_log'] = html_entity_decode($this->url->link($this->_route . '/clearLog', 'user_token=' . $this->session->data['user_token'], true), ENT_QUOTES, 'UTF-8');

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['action'] = $this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view($this->_route, $data));
	}
	
	public function resetAuthSession() {
		$this->load->language($this->_route);
		
		$settings = [
			'mautic_access_token',
			'mautic_access_token_expires',
			'mautic_access_token_type',
			'mautic_access_refresh_token',
		];
		
		$this->load->model('setting/setting');
	
		foreach ($settings as $value) {
			$this->model_setting_setting->editSettingValue('mautic', $value,  '', $store_id);
		}
	
		$this->session->data['success'] = $this->language->get('text_success_reseted');

		$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
	}
	
	public function resetConectionSettings() {
		$this->load->language($this->_route);
		
		$settings = [
			'mautic_base_url',
			'mautic_auth_version',
			'mautic_client_id',
			'mautic_client_secret',
		];
		
		$this->load->model('setting/setting');
	
		foreach ($settings as $value) {
			$this->model_setting_setting->editSettingValue('mautic', $value,  '', $store_id);
		}

		$this->session->data['success'] = $this->language->get('text_success_reseted');

		$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
	}
	
	public function clearLog() {
		$this->load->config('mautic');
		$this->load->language($this->_route);
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

			if (file_exists(DIR_LOGS . $this->config->get('mautic_log_filename'))) {
				
				file_put_contents(DIR_LOGS . $this->config->get('mautic_log_filename'), '');

				$response = ['success' => $this->language->get('text_success_cleared')];
			} else {
				$response = ['error' => $this->language->get('error_log_not_found')];
			}
		} else {
			$response = ['error' => $this->language->get('error_wrong_request')];
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($response));
	}

	public function fetchLog() {
		$this->load->config('mautic');
		$this->load->language($this->_route);
		
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {

			if (file_exists(DIR_LOGS . $this->config->get('mautic_log_filename'))) {

				$result = [];
				$fp = fopen(DIR_LOGS . $this->config->get('mautic_log_filename'), 'r');
				
				while(!feof($fp)) {
					$line = fgets($fp, 4096);
					array_push($result, trim($line, PHP_EOL));
					if (count($result) > 200) {
						array_shift($result);
					}
				}
				
				fclose($fp);

				$result = implode(PHP_EOL, $result);

				if (empty($result)) {
					$response = ['success' => 'Empty'];
				} else {
					$response = ['success' => $result];
				}
			} else {
				$response = ['error' => 'Mautic log not found!'];
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($response));
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', $this->_route)) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (count($this->request->post, COUNT_RECURSIVE) >= ini_get('max_input_vars')) {
			$this->error['warning'] = $this->language->get('error_max_input_vars');
		}

		return !$this->error;
	}

	public function authMautic() {
		$this->load->language($this->_route);
		
		$auth = $this->apiAuth();

		if ($auth->isAuthorized()) {
			$this->session->data['success'] = $this->language->get('text_success_authorized');
		} else {
			$this->session->data['error'] = $this->language->get('text_cannot_authorize');
		}

		$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
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

	public function syncContacts() {
		$this->load->language($this->_route);
		
		$auth = $this->apiAuth();

		if ($auth->isAuthorized()) {
			$this->session->data['success'] = $this->language->get('text_success_authorized');

			$apiUrl = $this->config->get('mautic_base_url') . '/api/';

			$api        = new MauticApi();
			$contactApi = $api->newApi('contacts', $auth, $apiUrl);
			
			$subscribed_only = true;
			$this->load->model($this->_route);
			$customers = $this->{$this->_model}->getAllCustomersData($subscribed_only);
			
			$mautic_fields_map = $this->config->get('mautic_fields_map');

			$already_synchronized = 0;

			$contacts = [];
			foreach ($customers as $customer) {
				$contact = $this->mergeFieldDataMapping($mautic_fields_map, $customer, false);
				
				if ((int) $customer['customer.mautic_contact_id'] === 0) {
					$contacts[] = $contact;
				} else {
					$already_synchronized++;
				}
			}
			
			$response = $contactApi->createBatch($contacts);

			if (isset($response['contacts'])) {
				foreach ($response['contacts'] as $mautic_contact) {
					$mautic_contact_id = $mautic_contact['id'];
					$email = $mautic_contact['fields']['core']['email']['value'];
					$customer_id = $this->{$this->_model}->getCustomerIdByEmail($email);

					$this->{$this->_model}->setMauticContactIdToCustomerId($customer_id, $mautic_contact_id);
				}
				$synchronized = count($response['contacts']);
			} else {
				$synchronized = 0;
			}

			$success_sync = ($synchronized) ? "Successfully exported {$synchronized} contacts." : "";
			$success_already = ($already_synchronized) ? "{$already_synchronized} contacts was been already synchronized" : "";

			$this->session->data['success'] =  $success_sync . " " . $success_already;
		} else {
			$this->session->data['error'] = $this->language->get('text_cannot_authorize');
		}

		$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
	}
	
	public function fetchMauticFields() {
		$this->load->language($this->_route);

		$auth = $this->apiAuth();
		
		if ($auth->isAuthorized()) {
			$apiUrl = $this->config->get('mautic_base_url') . '/api/';

			$api        = new MauticApi();
			$contactApi = $api->newApi('contacts', $auth, $apiUrl);
				
			$response = $contactApi->getFieldList();

			$mautic_fields = [];
			foreach ($response as $field) {
				$mautic_fields[$field['object']][$field['id']] = [
					'label' => $field['label'],
					'alias' => $field['alias'],
					'group' => $field['group']
				];
			}
			
			if (!empty($mautic_fields)) {
				$this->load->model('setting/setting');

				$store_id = 0;

				$this->model_setting_setting->editSettingValue('mautic', 'mautic_fields', json_encode($mautic_fields), $store_id);

				$this->session->data['success'] = $this->language->get('text_success_fetched');
			} else {
				$this->session->data['error'] = $this->language->get('text_fields_empty');
			}
		} else {
			$this->session->data['error'] = $this->language->get('text_cannot_authorize');
		}
		
		$this->response->redirect($this->url->link($this->_route, 'user_token=' . $this->session->data['user_token'], true));
	}
	
	private function apiAuth() {
		session_start();

		$this->load->model($this->_route);
		$settings = $this->{$this->_model}->getApiSettings();

		$initAuth = new ApiAuth();
		$auth     = @$initAuth->newAuth($settings);

		try {
			if (@$auth->validateAccessToken()) {
				
				if (@$auth->accessTokenUpdated()) {
					$accessTokenData = @$auth->getAccessTokenData();

					$this->load->model('setting/setting');

					$this->log->write(var_export($accessTokenData, true));

					$store_id = 0;

					$this->model_setting_setting->editSettingValue('mautic', 'mautic_access_token', 		$accessTokenData['access_token'], 	$store_id);
					$this->model_setting_setting->editSettingValue('mautic', 'mautic_access_token_expires', $accessTokenData['expires'], 		$store_id);
					$this->model_setting_setting->editSettingValue('mautic', 'mautic_access_token_type', 	$accessTokenData['token_type'], 	$store_id);
					$this->model_setting_setting->editSettingValue('mautic', 'mautic_access_refresh_token', $accessTokenData['refresh_token'], 	$store_id);					
				}
				return $auth;
			} else {
				return $auth;
			}
		} catch (Exception $e) {
			// Do Error handling
		}
	}
}