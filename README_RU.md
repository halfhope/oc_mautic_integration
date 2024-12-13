# OpenCart Mautic integration
[![License: GPLv3](https://img.shields.io/badge/license-GPL%20V3-green?style=plastic)](LICENSE)

Mautic self-hosted - это мощная и бесплатная система email-маркетинга. Это расширение связывает Mautic и Opencart. При использовании на выделенном сервере или VPS Mautic может обрабатывать несколько тысяч писем в день. В отличие от некоторых других почтовых служб у вас не будет квот и ежемесячной платы. Вы ограничены только настройками хостинга.

Модуль позволяет вам синхронизировать учетные записи клиентов Opencart, подписанных на рассылку, и контакты Mautic в обоих направлениях.

## Другие языки

* [English](README.md)

## Лог изменений

* [CHANGELOG_RU.md](docs/CHANGELOG_RU.md)

## Скриншоты

* [SCREENSHOTS.md](docs/SCREENSHOTS.md)

## Возможности

* Ручной безопасный экспорт клиентов OpenCart в Mautic по клику*
* Автоматическое добавление контактов Mautic при подписке в OpenCart.
* Автоматическое удаление контактов Mautic при отписке в OpenCart.
* Автоматическое обновление данных пользователя в обе стороны при изменении данных*
    * Из Mautic в OpenCart (хуки):
        * При изменении контакта
    * Из OpenCart в Mautic:
        * Создание/Изменение/Удаление учетных данных и адресов пользователем
        * Добавление/Изменение/Удаление учетных данных пользователем и админитратором
        * Изменение статуса подписки.
* Настраиваемые соответствия для полей Mautic и OpenCart**
* Синхронизированный пользователям OpenCart назначается contact_id

* Необходим при первом запуске 

** Некоторые поля, такие как страна, гео зоны могут быть синхронизированы только в одну сторону - из OpenCart в Mautic, но не обратно..

## Совместимость

* OpenCart 3.x
* PHP >= 8.1

## Демо [Временно недоступно]

Админка 

* [https://mautic-integration.shtt.blog/admin/](https://mautic-integration.shtt.blog/admin/) (авто вход)

Каталог 

* [https://mautic-integration.shtt.blog/](https://mautic-intrgration.shtt.blog/)

На демо сайте есть верхнее меню для быстрой навигации.

## Установка

* Установите расширение через стандартный раздел установки дополнений.
* Добавьте этот код в ваш system/startup.php после уже имеющегося Autoloader:
```
// Mautic autoload
if (defined('DIR_SYSTEM') && is_file(DIR_SYSTEM . 'library/mautic/vendor/autoload.php')) {
	require_once(DIR_SYSTEM . 'library/mautic/vendor/autoload.php');
}
```
* Перейдите в раздел модулей и установите нужный модуль.

## Настройка

* Для авторизации модуля в Mautic нужно заполнить в модуле 3 поля. "Base url", "Client ID" and "Client secret". Base url это ссылка на вашу панель Mautic. Остальные поля можно получить при создании нового API credentials.
* Create new API instance in your Mautic dashboard Settings > Integrations > API Credentials. 
* В настройках Mautic укажите максимальный срок жизни сессии. Потому что в Mautic есть коварный [баг](https://github.com/PipedreamHQ/pipedream/issues/6176) со сбросом сессии. Установить срок жизни сессии можно в разделе Settings > Configuration > API Settings. Рекомендуемые значения:
    * Access token lifetime (in minutes) - 999999
    * Refresh token lifetime (in days) - 32565
* После заполнения и этих полей сохраните настройки и вторизуйтесь
* Перейдите в раздел Fields mapping, чтобы получить текущие доступные поля из Mautic и настроить соответствие полей, которые будут синхронизированы в обе стороны.
* Настройте и сохраните настройки.
* Синхронизируйте контакты.
* На вкладке "OpenCart events" можно выбрать события OpenCart, которые будут отправлять измененные данные пользователя в Mautic. 
* На вкладке "Mautic webhooks" вы можете настроить события Mautic, которые будут отправлять данные в OpenCart
    * Для того чтобы создать вебхук, прейдите в Mautic dashboard Settings > Integrations > Webhooks.
    * Скопируйте Secret из Mautic в OpenCart.
    * Скопируйте из OpenCart в Mautic ссылку на обработчик события, предварительно, заменив webhookCode на тот, что был выбран. Например, onContactUpdated.
    * В панели управления Mautic в поле Webhook Events выберите "Contact Updated Event"
* Все, на этом настройка закончена.

Если возникнут любые вопросы, пишите в тему поддержки или личные сообщения.

## Лицензия

* [GPL v3.0](LICENSE.MD)

## Спасибо за использование моих дополнений!

Я решил сделать все свои OpenCart-дополнения бесплатными и с открытым исходным кодом, чтобы они могли приносить пользу сообществу. Разработка, поддержка и обновление этих дополнений требуют времени и усилий.

Если мои дополнения помогли вам в вашем проекте, и вы хотите поддержать мою работу, я буду благодарен за любую сумму пожертвований.

### 💙 Поддержать меня можно через:

* [PayPal](https://paypal.me/TalgatShashakhmetov?country.x=US&locale.x=en_US)
* [CashApp](https://cash.app/$TalgatShashakhmetov)

Ваше участие мотивирует меня продолжать развивать и улучшать эти инструменты. Спасибо за вашу поддержку!
