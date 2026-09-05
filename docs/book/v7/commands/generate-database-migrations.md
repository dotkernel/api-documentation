# Generate a database migration without dropping custom tables

## Summary

`doctrine-migrations diff` generates migrations from your entity mappings, but it also emits `DROP TABLE` statements for unmapped tables such as `oauth_*`.
Passing a `filter-expression` excludes those prefixes so the generated migration leaves them alone.

## Usage

Run the following command in your application’s root directory:

```shell
vendor/bin/doctrine-migrations diff
```

If you have mapping modifications, this will create a new migration file under `src/Core/src/App/src/Migration/`, in the `Core\App\Migration` namespace.
The location comes from the `doctrine.migrations.migrations_paths` key in `Core\App\ConfigProvider`.
Opening the migration file, you will notice that it contains some queries that will drop your `oauth_*` tables because they are unmapped (there is no doctrine entity describing them).
You should delete your latest migration with the DROP queries in it as we will create another one, without the DROP queries in it.
To avoid dropping these tables, you need to add a parameter called `filter-expression`.

The command to be executed without dropping these tables looks like this:

On Windows (use double quotes):

```shell
vendor/bin/doctrine-migrations diff --filter-expression="/^(?!oauth_)/"
```

On Linux/macOS (use single quotes):

```shell
vendor/bin/doctrine-migrations diff --filter-expression='/^(?!oauth_)/'
```

## Filtering multiple unmapped table patterns

If your database contains multiple unmapped table groups, then the pattern in `filter-expression` should hold all table prefixes concatenated by pipe character (`|`).
For example, if you need to filter tables prefixed with `foo_` and `bar_`, then the command should look like this:

On Windows:

```shell
vendor/bin/doctrine-migrations diff --filter-expression="/^(?!foo_|bar_)/"
```

On Linux/macOS:

```shell
vendor/bin/doctrine-migrations diff --filter-expression='/^(?!foo_|bar_)/'
```

## Troubleshooting

On Windows, running the command in PowerShell might still add the `DROP TABLE oauth_*` queries to the migration file.
This happens because for PowerShell the caret (`^`) is a special character, so it gets dropped (`"/^(?!oauth_)/"` becomes `"/(?!oauth_)/"` when it reaches your command).
Escaping it will not help either.
In this case, we recommend running the command:

* directly from your IDE
* using `Linux shell`
* from the `Command Prompt`

## Help

You can get more help with this command by running:

```shell
vendor/bin/doctrine-migrations help diff
```

## FAQ

**Q: Why does the generated migration try to drop my `oauth_*` tables?**

A: Because no Doctrine entity describes them.
From the ORM's point of view they are not part of the schema, so `diff` proposes removing them.

**Q: What should I do with a migration that already contains those DROP queries?**

A: Delete that migration file and regenerate it with a `filter-expression`, rather than editing the queries out by hand.

**Q: Why do the quotes differ between platforms?**

A: Windows shells require double quotes around the expression, while Linux and macOS shells require single quotes to prevent the pattern from being interpreted.

**Q: How do I exclude more than one prefix?**

A: Concatenate the prefixes with a pipe inside the negative lookahead, for example `/^(?!foo_|bar_)/`.

**Q: The filter is ignored in PowerShell. What is happening?**

A: PowerShell treats `^` as a special character and strips it, so the expression arrives without the anchor.
Escaping does not help — run the command from your IDE, a Linux shell, or the Command Prompt instead.

**Q: Where do generated migrations end up?**

A: In `src/Core/src/App/src/Migration/`, as classes in the `Core\App\Migration` namespace.
The path is configured under `doctrine.migrations.migrations_paths` in `Core\App\ConfigProvider`, so change it there if you want migrations elsewhere.
See [Doctrine ORM](../installation/doctrine-orm.md).

**Q: How do I see all options for the command?**

A: Run `vendor/bin/doctrine-migrations help diff`.
