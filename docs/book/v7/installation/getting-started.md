# Clone the project

In this step you will:

- [Clone the Dotkernel API project](#clone-the-project).
- [Set file permissions](#set-file-and-folder-permissions).

> If you are using the Microsoft Windows Operating System on your machine, you can use WSL2 as a development environment.
> Read more about [installing and using WLS2](https://www.dotkernel.com/how-to/installing-almalinux-10-in-wsl2-php-mariadb-composer-phpmyadmin//).

> Make sure to review the [prerequisites](prerequisites.md) before proceeding.

## Clone the Project

Using your terminal, navigate inside the directory where you want to download the project files.

> Make sure that the directory is empty before running the command below.

Run this command to clone the project files.

```shell
git clone https://github.com/dotkernel/api.git .
```

## Set File and Folder Permissions

To prevent future permission errors, certain folders must have their permissions set to 777.
This way they assign everyone (owner, group, and other users) permissions to read, write, and execute.

> It is safe to set these permissions as accessing the application files is dictated by the `.htaccess` file.
> The `public` folder is publicly accessible by design, so those files are served directly.
> Everything else is routed to the `index.php` file.

```shell
chmod -R 777 data
chmod -R 777 public/uploads
chmod -R 777 log
```

> The `-R` parameter is used to recursively apply the permissions to all subdirectories and files.
