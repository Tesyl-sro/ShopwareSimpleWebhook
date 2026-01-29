# Simple Webhooks for Shopware 6
Simple Shopware 6 plugin that adds webhook support for various events in your store. It's an alternative to the built-in [Flow Builder](https://www.shopware.com/en/products/ecommerce-automation/flow-builder/), which requires a commercial license.

### Why use webhooks?
Webhooks can help automate workflows and integrate your Shopware store with other services. For example, you can use webhooks to integrate with N8N, Zapier, or custom applications to perform actions like sending notifications, updating external databases, or triggering other processes whenever something happens in your store.

## Supported Events
- [x] Ping CLI called
- [x] Order Created
- [x] Customer Registered
- [x] Order Status Changed
- [x] Product Updated

## Installation
Download the plugin repository and place it in the `custom/plugins` directory of your Shopware 6 installation. Then, install and activate the plugin via the Shopware Administration panel.

## Configuration
In the configuration panel, just fill out the webhook URLs for the events you want to listen to. You can leave any field empty if you don't want to set up a webhook for that event.

Note that all webhooks use POST requests with a JSON payload containing relevant data about the event. Currently, you cannot customize the HTTP method.

If you need to temporarily disable all webhooks, you can use the "Enable webhooks" toggle in the configuration.

## Webhook call processing
All webhook calls are processed asynchronously using Shopware's built-in message queue system. This ensures that your store's performance is not affected by webhook calls, especially if the target URL is slow to respond.

Note that if you do not have a message consumer configured properly, webhook calls will not be processed. Please refer to the [Shopware documentation](https://developer.shopware.com/docs/guides/hosting/infrastructure/message-queue) for setting up message consumers.