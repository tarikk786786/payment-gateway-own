# Dezopay UPI Gateway - WHMCS Module

This module allows your WHMCS installation to securely accept payments via Dezopay (UPI Gateway).

## Installation

1. Upload the `modules` folder from this directory to the root of your WHMCS installation.
   - It will place `upigateway.php` into `modules/gateways/`
   - It will place `callback/upigateway.php` into `modules/gateways/callback/`
2. Log in to your WHMCS Admin Panel.
3. Navigate to **System Settings > Payment Gateways**.
4. Click on the **All Payment Gateways** tab and find **Dezopay UPI Gateway**. Click on it to activate it.

## Configuration

Once activated, configure the module with your credentials:

- **Display Name:** The name shown to clients (e.g., "Pay via UPI / Card").
- **API Key (User Token):** Enter your API Key/User Token from your Dezopay Developer Settings.
- **API Endpoint URL:** Leave this as the default (`https://chickenpox.in/api/create-order`) unless instructed otherwise by support.
- **Webhook URL:** The module will display your Webhook URL. Copy this and paste it into the "Webhook URL" field in your Dezopay Developer Settings.

## Testing

1. Create a dummy invoice in WHMCS.
2. Select "Dezopay UPI Gateway" as the payment method.
3. Click "Pay Now" and ensure it redirects you to the Dezopay checkout page.
4. Complete the payment to verify that the invoice is automatically marked as Paid in WHMCS.
