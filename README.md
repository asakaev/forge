## forge

### Overview

`forge` is a [Stripe Checkout](https://stripe.com/payments/checkout) payment plugin for
[Webasyst](https://www.webasyst.com) framework based [Shop-Script](https://www.shop-script.com)
application.

### Why "forge"?

> The next name we came up with was “forge” — big, strong, building something. I ran the name by
> one of my friends from school, whose reply was along the lines of “um... you're kidding right”?
>  He pointed out that forge has an alternate meaning when it comes to currency, documents, and
> the like.
> 
> -- Greg Brockman, Stripe CTO

### Getting Started

1. Copy plugin to `wa-plugins/payment/forge`
2. Enable Stripe payment method in Webasyst settings
3. Use Stripe secret [API Key](https://dashboard.stripe.com/apikeys) in `forge` settings
4. Set up [Webhook](https://dashboard.stripe.com/webhooks) to listen `checkout.session.completed` events using endpoint from `forge` settings

### Tax Rates

[Taxes](https://support.webasyst.com/shop-script/11311/taxes/) and shipping are calculated in Shop-Script and sent to Stripe as line items, ensuring consistent pricing without relying on Stripe's [Tax Rates](https://docs.stripe.com/payments/checkout/use-manual-tax-rates).

### Design

```
Customer -> Wa -> ProcessOrder [Stripe.CreateSession]
Stripe   -> Wa -> ProcessEvent [Wa.Init, Wa.StoreTransaction]
```

Order state between placement and fulfillment is tracked using Stripe [Metadata](https://stripe.com/docs/api/metadata).

### License
MIT
