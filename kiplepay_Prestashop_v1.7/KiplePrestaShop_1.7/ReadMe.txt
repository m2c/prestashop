#Installing the Kiple Plugin

1. Extract the KiplePrestaShop_1.7.zip file
2. Inside this zip file you can see the webcash.zip.
3. open Module Manager, and clickon the Upload a Module
4. Select the webcash.zip file, once finished you can configure the settings.


When you moving to Production environment, please do the following.

1. Please change the Kiple staging URL to Kiple production URL (https://kiplepay.com/wcgatewayinit.php) in /modules/webcash/webcash.php (line No:41).
2. Please change the Kiple staging URL to Kiple production URL (https://kiplepay.com/enquiry.php) in /modules/webcash/controllers/front/paymentreturn.php (line No:86).
3. Please change the Production Merchant ID and Merchant Key in the Settings page.

Configure at admin !