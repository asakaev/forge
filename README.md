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

[Dynamic tax rates](https://stripe.com/docs/payments/checkout/taxes?tax-calculation=tax-rates#dynamic-tax-rates)
using information from your customer (for example, their billing or shipping address).
Stripe attempts to match your customer’s location to one of those predefined [tax rates](https://dashboard.stripe.com/tax-rates).
`forge` using an [inclusive](https://stripe.com/docs/billing/taxes/tax-rates#inclusive-vs-exclusive-tax) tax rate.

### Design

```
Customer -> Wa -> ProcessOrder [Stripe.ListTaxRates, Stripe.CreateSession]
Stripe   -> Wa -> ProcessEvent [Wa.Init, Wa.StoreTransaction]
```

Tax Rates are synced before each payment.
State between order placement and fulfilment implemented using [Metadata](https://stripe.com/docs/api/metadata).

### License
MIT
