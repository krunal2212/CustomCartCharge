# Custom Cart Charge

This module is prepared to custom shipping rates,  configure the Percentage amount of shipping charge

### Features

1) It will create a custom shipping method.
2) You can set a condition where the shipping method will be shown on the  basis of the customer group
3) It can be managed from Store Configuration.
4) The Shipping method can be calculated on the basis of 5% of the subtotal amount.
5) In Product Boolean Attribute is there named "Ignore From Shipping" , If the value of "Ignore from shipping" is Yes then it will ignore it from the Shipping calculation
7) Another attribute called "Add Shipping Price". If any product has value in "Add Shipping Price" then at the time
of calculation you need to calculate added shipping price value and for the remaining
product, 5% of the remaining product subtotal should be calculated.
9) The percentage should I give 5% or any other % it is dynamically set from store configuration.

### Steps to Install Module

1. Create Directory under app/code > "Krunal/PercentShipping"
2. Copy whole code under PercentShipping Directory
3. Then Run Below commands

```
php bin/magento module:enable Krunal_PercentShipping
php bin/magento setup:di:compile
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy -f
php bin/magento cache:flush
```


