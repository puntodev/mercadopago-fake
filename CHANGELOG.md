# Changelog

All notable changes to `mercadopago-fake` are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

Released entries below are maintained automatically from the GitHub release notes
(see `.github/workflows/update-changelog.yml`); the `Unreleased` section tracks the
range of changes on `main` that have not been released yet.

## [Unreleased](https://github.com/puntodev/mercadopago-fake/compare/0.0.3...HEAD)

- Require Laravel 13+ and bump `puntodev/mercadopago` to `^7.0` (#3).
- Set up Laravel Pint, `AGENTS.md`, `CONTRIBUTING.md` and developer docs (#2).

## [0.0.3](https://github.com/puntodev/mercadopago-fake/compare/0.0.2...0.0.3) - 2026-03-29

### What's Changed

* Refactor FakeCheckoutController to replace Http facade with App request handling and update blade view elements for consistency by @marianogoldman in https://github.com/puntodev/mercadopago-fake/pull/1

### New Contributors

* @marianogoldman made their first contribution in https://github.com/puntodev/mercadopago-fake/pull/1

**Full Changelog**: https://github.com/puntodev/mercadopago-fake/compare/0.0.2...0.0.3

## [0.0.2](https://github.com/puntodev/mercadopago-fake/compare/0.0.1...0.0.2) - 2026-03-29

- Enhance merchant order creation logic and notification handling in `FakeCheckoutController`.

## 0.0.1 - 2026-03-29

- Initial release: fake MercadoPago API client and checkout flow for testing.
