# Creating admin accounts in Dotkernel API

## Summary

The `admin:create` CLI command creates an administrator account from the command line, taking an identity, a password and a first and last name.
Accounts created this way always receive the `admin` role.

## Usage

Run the following command in your application’s root directory:

```shell
php ./bin/cli.php admin:create -i {IDENTITY} -p {PASSWORD} -f {FIRST_NAME} -l {LAST_NAME}
```

OR

```shell
php ./bin/cli.php admin:create --identity {IDENTITY} --password {PASSWORD} --firstName {FIRST_NAME} --lastName {LAST_NAME}
```

OR

```shell
php ./bin/cli.php admin:create --identity={IDENTITY} --password={PASSWORD} --firstName={FIRST_NAME} --lastName={LAST_NAME}
```

after replacing:

* {IDENTITY} with a valid username OR email address
* {PASSWORD} with a valid password
* {FIRST_NAME} and {LAST_NAME} with valid names

> If the specified fields contain special characters, make sure you surround them with double quote signs this method does not allow specifying an admin role – newly created accounts will have a role of admin.

If the submitted data is valid, the outputted response is:

```text

 [INFO] Admin created successfully.

```

All four options are required.
If any is missing or invalid, the command fails with the input filter's validation messages instead,
one per line.

The new admin account is ready to use.

You can get more help with this command by running:

```shell
php ./bin/cli.php help admin:create
```

## FAQ

**Q: Can I choose the role of the created account?**

A: No. The command always assigns the `admin` role; other roles must be set afterwards.
See [Authorization](../core-features/authorization.md).

**Q: What can I use as the identity?**

A: Either a username or an email address, as long as it is not already taken.

**Q: My name or password contains special characters and the command fails. What do I do?**

A: Surround the value in double quotes so the shell passes it through unchanged.

**Q: Are the short and long option forms equivalent?**

A: Yes.
`-i`, `-p`, `-f` and `-l` are shorthand for `--identity`, `--password`, `--firstName` and `--lastName`.

**Q: How do I know the account was created?**

A: The command prints `[INFO] Admin created successfully.` and the account is immediately usable.
Anything else — a list of field messages — means validation failed and no account was created.

**Q: Where do I see the full command help?**

A: Run `php ./bin/cli.php help admin:create`.
