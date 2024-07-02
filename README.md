# Paynocchio Payment Gateway

###### for Moodle 4+
by

[![Paynocchio.com](https://www.paynocchio.com/embleme.svg)](https://paynocchio.com)

### Description
**paygw_paynocchio** opens the opportunity for your student to get rewards for spending money on education!

### Requirements
PHP 8+
Verified profile at [Paynocchio](https://paynocchio.com)


### Installation

Download the latest version of the plugin and process standard plugin installation through the Moodle admin panel.

### Setup

- Go to Paynocchio module settings and enter **Environment ID** and **Secret key** which you can obtain at your Paynocchio Control Panel in the Environments section.
- Fill other fields to brand your Paynocchio card.
- Go to **Site administration** > **Server** > **External services** and create new External Service 
- Add Functions to you new external service: **paygw_paynocchio_create_topup_complete** and **paygw_paynocchio_create_transaction_complete**
- Download [Moodle REST with JSON support](https://github.com/wset/moodle-webservice_restjson) module and install it. (Currently Moodle can't work with raw JSON)
- Go to **Site administration** > **Server** > **Manage tokens** and Create token for the user with Administrator role. Select previously created Webservice and uncheck Valid until box.
- Copy your new token and create links to your site using this scheme:
  - https://%SITE_URL%/webservice/restjson/server.php?wstoken=%TOKEN%&wsfunction=paygw_paynocchio_create_transaction_complete&moodlewsrestformat=json
  - https://%SITE_URL%/webservice/restjson/server.php?wstoken=%TOKEN%&wsfunction=paygw_paynocchio_create_topup_complete&moodlewsrestformat=json
- Go to your Paynocchio Control panel's Webhooks section and create two webhooks (**Replenishment** and **Payment**) using previously created links. 

### Paynocchio Pending Payments

Sometimes especially when you didn't setup webhooks right, payment may hav Pending status, because user has paid the amount and should be enrolled, but the system didn't receive confirmation webhook.
If you **100% sure** the payment has been processed you may manually confirm payment via `/payment/gateway/paynocchio/manage.php` section

Be especially careful with denying pending payment because the user may have paid the amount and money have been transferred from his account. By denying the payment you just delete a   single row in `paynocchio_payments` table in the Database.

### Troubleshooting

If you for some reason will change the 