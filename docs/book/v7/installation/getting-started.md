# Clone the project

## Recommended development environment

> If you are using the Microsoft Windows Operating System on your machine, you can use WSL2 as a development environment.
> Read more about [PHP Mariadb on WLS2](https://www.dotkernel.com/php-development/almalinux-9-in-wsl2-install-php-apache-mariadb-composer-phpmyadmin/).

Using your terminal, navigate inside the directory where you want to download the project files.

> Make sure that the directory is empty before running the command below.

Run this command to clone the project files.

```shell
git clone https://github.com/dotkernel/api.git .
```

To prevent future permission errors, certain folders must have their permissions set to 777.
This way they assign everyone (owner, group, and other users) permissions to read, write, and execute.

```shell
chmod -R 777 data
chmod -R 777 public/uploads
chmod -R 777 log
```

> The `-R` parameter is used to recursively apply the permissions to all subdirectories and files.
