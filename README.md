# OpenCart Mautic Integration
[![License: GPLv3](https://img.shields.io/badge/license-GPL%20V3-green?style=plastic)](LICENSE)

Mautic self-hosted is a powerful and free email marketing system. This extension connects Mautic and OpenCart. When used on a dedicated server or VPS, Mautic can handle several thousand emails per day. Unlike some other mailing services, there are no quotas or monthly fees. You are only limited by your hosting configuration.

The module allows you to synchronize OpenCart customer accounts subscribed to newsletters with Mautic contacts in both directions.

## Other languages

* [Russian](README_RU.md)

## Change log

* [CHANGELOG.md](docs/CHANGELOG.md)

## Screenshots

* [SCREENSHOTS.md](docs/SCREENSHOTS.md)

## Features

* Manual secure export of OpenCart customers to Mautic on click*
* Automatically add Mautic contacts when customers subscribing in OpenCart.
* Automatically remove Mautic contacts when customers unsubscribing in OpenCart.
* Automatically update user data in both directions when data changes*
    * From Mautic to OpenCart (When a contact changes)
    * From OpenCart to Mautic (Add/Edit/Delete credentials and addresses by customer and administrator, Change subscription status)
* Custom field mappings for Mautic and OpenCart**
* Synchronized OpenCart users are assigned a Mautic contact_id

* Required on first launch or on request

** Some fields, such as country, geo zones can only be synchronized one way - from OpenCart to Mautic, but not back.

## Compatibility

* OpenCart 3.x

## Dependencies

* PHP >= 8.1

## Demo [Temporarily unavailable]

Admin 

* [https://mautic-integration.shtt.blog/admin/](https://mautic-integration.shtt.blog/admin/) (auto login)

## Installation

* Install the extension via the standard extension installation section.
* Add this code into your system/startup.php after already existed "Autoloader".
```
// Mautic autoload
if (defined('DIR_SYSTEM') && is_file(DIR_SYSTEM . 'library/mautic/vendor/autoload.php')) {
	require_once(DIR_SYSTEM . 'library/mautic/vendor/autoload.php');
}
```
* Go to the modules section and install this module.

## Setting

* To authorize a module in Mautic, you need to fill in 3 fields in the module. "Base url", "Client ID" and "Client secret". Base url is a link to your Mautic panel. The remaining fields can be obtained when creating new API credentials.
* You can create new API instance in your Mautic dashboard Settings > Integrations > API Credentials. 
* In Mautic settings, specify the maximum session lifetime. Because Mautic has a sneaky session reset bug. You can set the session lifetime in Settings > Configuration > API Settings. Recommended values:
    * Access token lifetime (in minutes) - 999999
    * Refresh token lifetime (in days) - 32565
* After filling in these fields, save the settings and log in
* Go to the Fields mapping section to get the currently available fields from Mautic and set up the mapping of the fields that will be synced in both directions.
* Configure and save the settings.
* Sync contacts.
* In the "OpenCart events" tab, you can select OpenCart events that will send changed user data to Mautic.
* In the "Mautic webhooks" tab, you can configure Mautic events that will send data to OpenCart.
    * To create a webhook, go to the Mautic dashboard Settings > Integrations > Webhooks.
    * Copy the Secret from Mautic to OpenCart.
    * Copy the link to the event handler from OpenCart to Mautic, after replacing the webhookCode with the one you selected. For example, onContactUpdated.
    * In the Mautic dashboard, in the Webhook Events field, select "Contact Updated Event"
* That's it, the setup is complete.

Если возникнут любые вопросы, пишите в тему поддержки или личные сообщения.

## License

* [GPL v3.0](LICENSE.MD)

## Thank You for Using My Extensions!

I have decided to make all my OpenCart extensions free and open-source to benefit the community. Developing, maintaining, and updating these extensions takes time and effort.

If my extensions have been helpful for your project and you’d like to support my work, any donation is greatly appreciated.

### 💙 You can support me via:

* [PayPal](https://paypal.me/TalgatShashakhmetov?country.x=US&locale.x=en_US)
* [CashApp](https://cash.app/$TalgatShashakhmetov)

Your support inspires me to keep improving and developing these tools. Thank you!
