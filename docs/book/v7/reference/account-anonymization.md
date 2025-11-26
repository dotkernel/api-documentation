# Account anonymization

## Premise

According to the GDPR, companies that record personal data from EU citizens must delete said data if its owner requests its deletion.
An alternative is to anonymize the data, according to [this article](https://commission.europa.eu/law/law-topic/data-protection/reform/rules-business-and-organisations/dealing-citizens/do-we-always-have-delete-personal-data-if-person-asks_en).

## Definition

### What is Personally identifiable information?

According to [this article](https://commission.europa.eu/law/law-topic/data-protection/reform/what-personal-data_en), Personally identifiable information (PII) is:

- A name and surname.
- A home address.
- An email address such as name.surname@company.com.
- An identification card number.
- Location data (for example, the location data function on a mobile phone).
- An Internet Protocol (IP) address.
- A cookie ID.
- The advertising identifier of your phone.
- A phone number.
- Data held by a hospital or doctor, which could be a symbol that uniquely identifies a person.

Out of the box, Dotkernel API saves the user's name (firstname and lastname) and email (identity).
This personal data is used for emails related to password reset and account activation.

## Process

### Anonymization

The anonymization process makes these replacements:

- The firstname and lastname are replaced with `anonymous` concatenated with the current UNIX timestamp, e.g. `anonymous1725980747`.
- The email is replaced with `anonymous` concatenated with the current UNIX timestamp and the value in `userAnonymizeAppend`, e.g. `anonymous1725980747@example.com`.
- The avatar image and its database record are deleted.

The `userAnonymizeAppend` key can be set in `config/autoload/local.php` or left empty.

> Using an email domain for `userAnonymizeAppend` would work as a catch-all email, if your email service provider has this option enabled.
