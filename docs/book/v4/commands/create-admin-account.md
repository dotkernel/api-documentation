# Creating admin accounts in DotKernel API

## Usage

Run the following command in your application’s root directory:
```shell
php ./bin/cli.php admin:create -i {IDENTITY} -p {PASSWORD} -f {FIRST_NAME} -l {LAST_NAME}
```

OR

```shell
php ./bin/cli.php admin:create --identity {IDENTITY} --password {PASSWORD} --firstName {FIRST_NAME} --lastName {LAST_NAME}
```

after replacing:

* {IDENTITY} with a valid username OR email address
* {PASSWORD} with a valid password
* {FIRST_NAME} and {LAST_NAME} with valid names

**NOTE:**

* if the specified fields contain special characters, make sure you surround them with double quote signs
* this method does not allow specifying an admin role – newly created accounts will have role of admin

If the submitted data is valid, the outputted response is:

```text
Admin account has been created.
```
The new admin account is ready to use.

You can get more help with this command by running:

```shell
php ./bin/cli.php help admin:create
```
