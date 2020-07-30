# Example

## Prerequisites

- PHP 7
- composer
- [Stripe cli](https://stripe.com/docs/stripe-cli)

## Setup

`make install`

`mv example.env .env` and fill in `.env` with the values for your Stripe account.

## Running

### No 3DS

- `make no3DS`
- `make refund`
- `make no3DSBut3DSTriggers`

### With 3DS

- `make forwardWebhooks`
- `make 3DSWebhooks`
- `make trigger3DS`
