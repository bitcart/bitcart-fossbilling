# Bitcart plugin for FOSSBilling

For BoxBilling or FOSSBilling versions less than 0.5.0, you will need to use an [older version](https://github.com/bitcart/bitcart-fossbilling/tree/9aeb99cd3a59545113c2f5416d7ed63f00b149eb) of this payment gateway.
Please keep in mind BoxBilling is unmaintained and both BoxBilling and outdated version FOSSBilling may suffer from security vulnerabilities. Additionally, no support will be provided for either.

## Integration Requirements

This version requires the following:

* A working and up-to-date FOSSBilling instance
* Running Bitcart instance: [deployment guide](https://docs.bitcart.ai/deployment)

## Installing the Plugin

1. From your FOSSBilling panel, go to configuration > payment gateways -> New payment gateway

2. Upload Bitcart directory to the directory suggested by your deployment. [Download it](https://github.com/bitcart/bitcart-fossbilling/releases/latest/download/Bitcart.zip) from this repository

3. Enable it by clicking on the button near Bitcart, fill in all settings and save.

## Plugin Configuration

After you have enabled the Bitcart plugin, the configuration steps are:

1. Enter your admin panel URL (for example, https://admin.bitcart.ai) without slashes. If deployed via configurator, you should use https://bitcart.yourdomain.com/admin
2. Enter your merchants API URL (for example, https://api.bitcart.ai) without slashes. If deployed via configurator, you should use https://bitcart.yourdomain.com/api
3. Enter your store ID (click on id field in Bitcart's admin panel to copy id)

Enjoy!
