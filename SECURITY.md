# Security policy

## Reporting a vulnerability

Please use [GitHub private vulnerability reporting](https://github.com/defstudio/whatsapper/security/advisories/new)
to report a suspected vulnerability privately. Do not open a public issue with
exploit details or include real access tokens, App Secrets, or personal data.

Include the affected package and dependency versions, relevant configuration,
reproduction steps using dummy credentials, and the observed impact. Distinguish
verified behavior from assumptions, including whether you exercised the HTTP
webhook route or invoked package classes directly.

## Security updates

Security fixes target the latest release. Consult [the changelog](CHANGELOG.md)
for fixes and required configuration changes. Older releases may remain affected;
upgrade to a release containing the relevant fix.

Webhook authentication requires the Meta App Secret, separate from the GET
verification token. Keep access tokens and App Secrets private and store received
media outside publicly served or executable directories. See [the README](README.md#webhook-security-and-upgrading)
for configuration and upgrade instructions.
