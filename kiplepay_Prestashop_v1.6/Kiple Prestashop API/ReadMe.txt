Copy:

1) order-failed.tpl  -> to all themes directory
2) order-failed.php  -> to shop home directory

Copy Webcash folder to /modules

When you moving to Production environment, please do the following.
1. Please change the Kiple staging URL to Kiple production URL (https://kiplepay.com/wcgatewayinit.php) in /modules/webcash/webcash.php (line No:34).
2. Please change the Kiple staging URL to Kiple production URL (https://kiplepay.com/enquiry.php) in /modules/webcash/validation.php (line No:36).
3. Please change the Production Merchant ID and Merchant Key in the Settings page.

Configure at admin !