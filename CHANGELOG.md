# Changelog

All Notable changes to `laravel-invoice` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## 2.0.2 - 2020-04-21
### Added
- Illuminate Support ^7.0

## 2.0.2 - 2020-04-14
### Added
- IsInvoiceable trait for "product" like models
- `findByInvoiceable()` method for collecting invoices by invoiced models
- `findByRelated()` method for collecting invoices by invoice receiver models
- BaseModel created

## 2.0.1 - 2020-04-08
### Changed
- Version fixed in changelog
- Test coverage

## 2.0.0 - 2020-04-08

### Added
- Bills for receiving invoices
- Free or complimentary sale for invoice lines
- Polymorphic customer and polymorphic product relationships
- Service layer
- Multiple tax for each invoice line
- Supporting fixed or percentage tax
- Configurable table names

### Changed
- Package name
- File structure
- Readme for new distribution

---

#### ATTENTION
To follow original repository, please see [sandervanhooft/laravel-invoicable](https://github.com/sandervanhooft/laravel-invoicable).

Former changes (changelog of original): [CHANGELOG.md](https://github.com/sandervanhooft/laravel-invoicable/blob/master/CHANGELOG.md)

### TEMPLATE

```
## NEXT - YYYY-MM-DD

### Added
- for new features

### Changed
- for changes in existing functionality

### Deprecated
- for soon-to-be removed features

### Fixed
- for any bug fixes

### Removed
- for now removed features

### Security
- in case of vulnerabilities

```
