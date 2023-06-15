# Bitcart plugin for FOSSBilling

For BoxBilling or FOSSBilling versions less than 0.5.0, you will need to use an [older version](https://github.com/bitcart/bitcart-fossbilling/tree/9aeb99cd3a59545113c2f5416d7ed63f00b149eb) of this payment gateway.
Please keep in mind BoxBilling is unmaintained and both BoxBilling and outdated version FOSSBilling may suffer from security vulnerabilities. Additionally, no support will be provided for either.

## Integration Requirements

This version requires the following:

* A working and up-to-date FOSSBilling instance
* Running Bitcart instance: [deployment guide](https://docs.bitcart.ai/deployment)

## Installing the Plugin

### Extension directory

The easiest way to install this extension is by using the [FOSSBilling extension directory](https://extensions.fossbilling.org/extension/Bitcart).

### Manual installation

1. Download the latest release from the [extension directory](https://extensions.fossbilling.org/extension/Bitcart)
2. Create a new folder named `Bitcart` in the `/library/Payment/Adapter` directory of your FOSSBilling installation
3. Extract the archive you've downloaded in the first step into the new directory
4. Go to the "Payment gateways" page in your admin panel (under the "System" menu in the navigation bar) and find Bitcart in the "New payment gateway" tab
5. Click the cog icon next to Bitcart to install and configure Bitcart

## Plugin Configuration

After you have enabled the Bitcart plugin, the configuration steps are:

1. Enter your admin panel URL (for example, https://admin.bitcart.ai) without slashes. If deployed via configurator, you should use https://bitcart.yourdomain.com/admin
2. Enter your merchants API URL (for example, https://api.bitcart.ai) without slashes. If deployed via configurator, you should use https://bitcart.yourdomain.com/api
3. Enter your store ID (click on id field in Bitcart's admin panel to copy id)

Enjoy!
