<?php
/**
 * @author Shashakhmetov Talgat <talgatks@gmail.com>
 */

// Heading
$_['heading_title']                 = 'Mautic integration';

// Text
$_['text_extension']                = 'Modules';

$_['text_edit']                     = 'Edit settings';

$_['text_select_all']               = 'Select All';
$_['text_unselect_all']             = 'Unselect All';
$_['text_invert_selection']         = 'Invert selection';

$_['text_authorization_settings']   = 'Connection settings';
$_['text_authorization_values']     = 'Session values';

$_['text_synchronization_settings']     = 'Synchronization settings';

$_['entry_fields_mapping']          = 'Fields mapping';
$_['entry_fields_mapping_help']     = 'OpenCart fields marked with * available to sync only in one side OpenCart > Mautic';
$_['text_fields_mautic']            = 'Mautic fields';
$_['text_fields_opencart']          = 'OpenCart fields';
$_['text_api_credentials_help']     = 'Create new API instance in your Mautic dashboard Settings > Integrations > API Credentials. Use Redirect URI in field below for Redirect URI field in Mautic.';

// Tabs
$_['tab_authorization']             = 'Authorization';
$_['tab_general']                   = 'General';
$_['tab_contacts']                  = 'Contacts';
$_['tab_fields']                    = 'Fields mapping';
$_['tab_event']                     = 'OpenCart events';
$_['tab_webhook']                   = 'Mautic webhooks';
$_['tab_log']                       = 'Log';

// Entries
$_['entry_base_url']                = 'Base url';
$_['entry_auth_version']            = 'Auth version';

$_['entry_client_id']               = 'Client ID';
$_['entry_client_id_help']          = 'Client ID or Public key';
$_['entry_client_secret']           = 'Client secret';
$_['entry_client_secret_help']      = 'Client secret or Secret key';
$_['entry_redirect_uri']            = 'Redirect URI';
	
$_['entry_auth']                    = 'Authorization';

$_['entry_sync']                    = 'Synchronization';
$_['entry_sync_help']               = 'If synchronization is interrupted for any reason, restart it and it will continue.';
	
$_['entry_access_token']            = 'Access token';
$_['entry_access_token_expires']    = 'Access token expires';
$_['entry_access_token_type']       = 'Access token type';
$_['entry_access_refresh_token']    = 'Refresh token';

$_['entry_status']                  = 'Enabled';
$_['entry_status_help']             = 'Status of all mautic integration functions';
$_['entry_webhooks']                = 'Enabled webhooks';
$_['entry_webhooks_help']           = 'Webhooks allow Mautic to notify OpenCart about changes in data. Create new webhook in your Mautic dashboard Settings > Integrations > Webhooks.';
$_['entry_webhook_secret']          = 'Webhook secret';

$_['entry_event_triggers']          = 'Event triggers';
$_['entry_event_triggers_help']     = 'Events allow OpenCart to notify Mautic about changes in data. Event triggers integrated in OpenCart via ocmod.';
	
// Button
$_['button_login']                  = 'Login';
$_['button_sync_contacts']          = 'Sync contacts';
$_['button_fetch_mautic_fields']    = 'Fetch Mautic Fields';
$_['button_reset_auth_session']     = 'Reset session';

// Error
$_['error_permission']              = 'You don\'t have permissions to modify this module!';
$_['error_log_not_found']           = 'Log not found!';
$_['error_wrong_request']           = 'Request error!';
$_['error_max_input_vars']          = 'Error: increase your max_input_vars in php.ini';
$_['error_auth_failed']             = 'Cannot authorize.';
$_['error_fields_empty']            = 'Empty fields';

// Success
$_['text_success_saved']            = 'Settings successfully saved!';
$_['text_success_authorized']       = 'Successfully authorized!';
$_['text_success_cleared']          = 'Log successfully cleared!';

$_['text_success_authorized']       = 'Successfully authorized!';
$_['text_cannot_authorize']         = 'Cannot authorize!';
$_['text_already_synchronized']     = 'All data has already been synchronized.';
$_['text_synchronized']             = '%s customers was been exported.';
$_['text_fields_fetched']           = 'Mautic fields successfully fetched';

// Log

$_['text_sync_started']             = '[SYNC] Synchronization started.';
$_['text_sync_ended']               = '[SYNC] Synchronization ended.';
$_['text_customer_exported']        = '[SYNC] Customer with customer_id: %s was been exported mautic_contact_id = %s';
$_['error_invalid_email']           = '[SYNC][ERROR] Customer with customer_id %s have invalid email address %s.';
$_['error_cannot_find_customer_id'] = '[SYNC][ERROR] Cannot find customer_id for email %s';
$_['error_common']                  = '[SYNC][ERROR] ';

?>