Place translation files here, for example:

- builder-vitrine-pt_BR.po / builder-vitrine-pt_BR.mo
- builder-vitrine-en_US.po / builder-vitrine-en_US.mo

Text domain: builder-vitrine

You can also use Vitrines → Translations in wp-admin for per-language overrides,
or Polylang → String translations (group: Builder Vitrine).

Generate a POT template with WP-CLI:

wp i18n make-pot . languages/builder-vitrine.pot --domain=builder-vitrine
