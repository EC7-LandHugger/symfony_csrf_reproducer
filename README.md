# Symfony Security Bundle CSRF Bug Reproducer

This repository demonstrates a potential bug in the Symfony Security Bundle's CSRF token validation.

## Setup

```sh
git clone https://github.com/EaswaranChinraj7/symfony_csrf_reproducer.git
cd symfony_csrf_reproducer
composer install
symfony serve -d
symfony open:local
```

## Key Detail

The key detail in reproducing the bug is the `$this->isCsrfTokenValid()` method call in the `HomeController::home()` method.

## Steps to Reproduce

1. Select `Left` in the `Pollute the Token At` field.
2. Submit the form and observe the bug details displayed below the form.

> Note: In the controller, the CSRF token is retrieved from the `Request`, altered on its left end using the custom service `CsrfPolluter`, and passed into the `$this->isCsrfTokenValid()` method along with the ID.

## Actual Behavior

The `$this->isCsrfTokenValid()` method returns `true` for polluted tokens.

## Expected Behavior

The `$this->isCsrfTokenValid()` method should return `false` for polluted tokens.
