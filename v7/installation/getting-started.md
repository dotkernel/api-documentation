# Clone the project

## Summary

The first installation step: clone the Dotkernel API repository into an empty directory, then grant write permissions on the `data`, `public/uploads` and `log` folders so the application can write caches, uploads and logs.

## Details

In this step you will:

- [Clone the Dotkernel API project](#clone-the-project).
- [Set file permissions](#set-file-and-folder-permissions).

> If you are using the Microsoft Windows Operating System on your machine, you can use WSL2 as a development environment.
> Read more about [installing and using WLS2](https://www.dotkernel.com/how-to/installing-almalinux-10-in-wsl2-php-mariadb-composer-phpmyadmin/).

> Make sure to review the [prerequisites](https://docs.dotkernel.org/api-documentation/v7/introduction/server-requirements/) before proceeding.

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

## FAQ

**Q: Why must the target directory be empty?**

A: `git clone <url> .` clones into the current directory and refuses to run if that directory already contains files.

**Q: Is setting `777` on those folders safe?**

A: Access to application files is controlled by the `.htaccess` rules: only the `public` folder is served directly and everything else is routed through `index.php`.
The three writable folders sit outside the served path.

**Q: Which folders need write access, and why?**

A: `data` for caches and generated files, `public/uploads` for uploaded content, and `log` for the error log.

**Q: I hit permission errors after installing. What now?**

A: Re-run the `chmod` commands, and see the [FAQ](faq.md) page, which lists the specific error messages and their fixes.

**Q: Can I develop on Windows?**

A: Yes, using WSL2 as the development environment.
See the [WSL2 setup guide](https://www.dotkernel.com/how-to/installing-almalinux-10-in-wsl2-php-mariadb-composer-phpmyadmin/).

**Q: What comes after cloning?**

A: Install dependencies with Composer, then configure the local files.
See [Composer](composer.md) and [Configuration files](configuration-files.md).
