<?php

$_['mautic_log_filename'] = 'mautic.log';

$_['static_webhooks'] = [
	// ['name' => 'Company Create/Update Event', 'code' => 'onCompanyUpdate'],
	// ['name' => 'Company Deleted Event', 'code' => 'onCompanyDeleted'],
	// ['name' => 'Contact Channel Subscription Change Event', 'code' => 'onContactChannelSubscriptionChange'],
	// ['name' => 'Contact Company Subscription Change Event', 'code' => 'onContactCompanySubscriptionChange'],
	// ['name' => 'Contact Deleted Event', 'code' => 'onContactDeleted'],
	// ['name' => 'Contact Identified Event', 'code' => 'onContactIdentified'],
	// ['name' => 'Contact Points Changed Event', 'code' => 'onContactPointsChanged'],
	// ['name' => 'Contact Segment Membership Change Event', 'code' => 'onContactSegmentMembershipChange'],
	['name' => 'Contact Updated Event', 'code' => 'onContactUpdated'],
	// ['name' => 'Email Open Event', 'code' => 'onEmailOpen'],
	// ['name' => 'Email Send Event', 'code' => 'onEmailSend'],
	// ['name' => 'Form Submit Event', 'code' => 'onFormSubmit'],
	// ['name' => 'Page Hit Event', 'code' => 'onPageHit'],
	// ['name' => 'Text Send Event', 'code' => 'onTextSend'],
];

$_['static_events'] = [
	'user' => [
		['name' => 'User subscribe', 'code' => 'userSubscribe'],
		['name' => 'User Unsubscribe', 'code' => 'userUnsubscribe'],
		
		['name' => 'New Customer', 'code' => 'newCustomer'],
		['name' => 'Edit Customer', 'code' => 'editCustomer'],
		['name' => 'Add Address', 'code' => 'addAddress'],
		['name' => 'Edit Address', 'code' => 'editAddress'],
	],
	'admin' => [
		['name' => 'New Customer', 'code' => 'adminNewCustomer'],
		['name' => 'Edit Customer', 'code' => 'adminEditCustomer'],
		['name' => 'Delete Customer', 'code' => 'adminDeleteCustomer'],
	]
];

$_['static_opencart_fields'] = [
	'customer' => [
		['name' => 'customer_id', 'code' => 'customer_id'],
		['name' => 'customer_group_id', 'code' => 'customer_group_id'],
		['name' => 'store_id', 'code' => 'store_id'],
		['name' => 'language_id', 'code' => 'language_id'],
		['name' => 'firstname', 'code' => 'firstname'],
		['name' => 'lastname', 'code' => 'lastname'],
		['name' => 'email', 'code' => 'email'],
		['name' => 'telephone', 'code' => 'telephone'],
		['name' => 'fax', 'code' => 'fax'],
		['name' => 'password', 'code' => 'password'],
		['name' => 'salt', 'code' => 'salt'],
		['name' => 'cart', 'code' => 'cart'],
		['name' => 'wishlist', 'code' => 'wishlist'],
		['name' => 'newsletter', 'code' => 'newsletter'],
		['name' => 'address_id', 'code' => 'address_id'],
		['name' => 'custom_field', 'code' => 'custom_field'],
		['name' => 'ip', 'code' => 'ip'],
		['name' => 'status', 'code' => 'status'],
		['name' => 'safe', 'code' => 'safe'],
		['name' => 'token', 'code' => 'token'],
		['name' => 'code', 'code' => 'code'],
		['name' => 'date_added', 'code' => 'date_added'],
	],
	'customer_group' => [
		['name' => 'customer_group_name', 'code' => 'customer_group_name', 'rc' => false],
	],
	'store' => [
		['name' => 'store_name', 'code' => 'store_name', 'rc' => false],
	],
	'language' => [
		['name' => 'language_name', 'code' => 'language_name', 'rc' => false],
		['name' => 'language_code', 'code' => 'language_code', 'rc' => false],
	],
	'address' => [
		['name' => 'firstname', 'code' => 'firstname'],
		['name' => 'lastname', 'code' => 'lastname'],
		['name' => 'company', 'code' => 'company'],
		['name' => 'address_1', 'code' => 'address_1'],
		['name' => 'address_2', 'code' => 'address_2'],
		['name' => 'city', 'code' => 'city'],
		['name' => 'postcode', 'code' => 'postcode'],
		['name' => 'country_id', 'code' => 'country_id'],
		['name' => 'zone_id', 'code' => 'zone_id'],
		['name' => 'custom_field', 'code' => 'custom_field'],
	],
	'country' => [
		['name' => 'name', 'code' => 'name', 'rc' => false],
	],
	'zone' => [
		['name' => 'name', 'code' => 'name', 'rc' => false],
		['name' => 'code', 'code' => 'code', 'rc' => false],
	]
];