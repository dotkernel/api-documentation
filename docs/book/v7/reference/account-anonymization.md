# Account anonymization

## Summary

Anonymization is the GDPR-compliant alternative to deleting a user's personal data.
Dotkernel API replaces the stored first name, last name and email with timestamp-based placeholders and deletes the avatar, optionally appending a domain configured through `userAnonymizeAppend`.

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

## FAQ

**Q: Why anonymize instead of delete?**

A: The GDPR requires that personal data stop identifying the individual; anonymizing satisfies that while leaving the surrounding records intact, so related data does not have to be removed as well.

**Q: Which personal data does Dotkernel API store by default?**

A: The user's first name, last name and email (used as the identity).
These are needed for password reset and account activation emails.

**Q: What do the anonymized values look like?**

A: The name fields become `anonymous` plus the current UNIX timestamp, for example `anonymous1725980747`, and the email becomes that value plus the configured append value, for example `anonymous1725980747@example.com`.

**Q: Where do I configure `userAnonymizeAppend`?**

A: In `config/autoload/local.php`.
It may also be left empty.
See [Configuration files](../installation/configuration-files.md).

**Q: What should I set `userAnonymizeAppend` to?**

A: A domain you control works well, because anonymized addresses then land in a catch-all mailbox if your provider supports it.

**Q: What happens to the user's avatar?**

A: Both the image file and its database record are deleted.

**Q: Is anonymization reversible?**

A: No.
The original values are overwritten, so keep your own backup policy in mind before running it.
