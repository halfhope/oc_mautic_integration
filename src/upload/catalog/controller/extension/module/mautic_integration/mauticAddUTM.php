<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

use \Mautic\Auth\ApiAuth;
use \Mautic\MauticApi;
use \Mautic\MauticOcIntegration;

class ControllerExtensionModuleMauticIntegrationMauticAddUTM extends Controller {
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
		$this->ml->write(sprintf($this->language->get('text_event_add_utm'), $customer_id));

		$apiUrl = $this->config->get('mautic_base_url') . '/api/';
		$api        = new MauticApi();
		$contactApi = $api->newApi('contacts', $auth, $apiUrl);
		
		$mautic_contact_id = $this->mi->getMauticContactIdByCustomerId($customer_id);
		if ($mautic_contact_id !== false) {
			// fill with data
			$utm_data = [
				'utm_campaign' => 'apicampaign',
				'utm_source'   => 'fb',
				'utm_medium'   => 'social',
				'utm_content'  => 'fbad',
				'utm_term'     => 'mautic api',
				'Useragent'    => 'Mozilla/5.0 (Windows NT 10.0; WOW64; rv:50.0) Gecko/20100101 Firefox/50.0',
				'url'          => '/product/fbad01/',
				'referer'      => 'https://google.com/q=mautic+api',
				'query'        => ['cid'=>'abc','cond'=>'new'], // or as string with "cid=abc&cond=new"
				'remotehost'   => 'example.com',
				'lastActive'   => '2017-01-17T00:30:08+00:00'
			];
				
			$response = $contactApi->addUtm($contact_data);
		
			if (isset($response['contact'])) {
				$contact = $response['contact'];
				$mautic_contact_id = $contact['id'];
				$this->mi->setMauticContactIdToCustomerId($customer_id, $mautic_contact_id);
	
				$this->ml->write(sprintf($this->language->get('text_event_added_contact'), $mautic_contact_id));
			}
	
			if (isset($response['errors'])) {
				foreach ($response['errors'] as $key => $error) {
					$this->ml->write($this->language->get('error_common')  . str_replace(["\n","\r\n"], '', $error['message']));
				}
			}
		}
	}
}
