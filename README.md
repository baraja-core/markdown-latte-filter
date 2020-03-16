Markdown Latte filter
=====================

Tools for automatic work with markdown.

How to install
--------------

Simple call Composer command:

```shell
composer require baraja-core/markdown-latte-filter
```

In project `common.neon` you must define Latte extension and services.

Fully works example configuration is in `config.neon` file in this package:

```yaml
services:
   markdown.renderer: Baraja\Markdown\CommonMarkRenderer
   nette.latteFactory:
      setup:
         - addFilter(markdown, @Baraja\Markdown\Filter\Markdown)
   - Baraja\Markdown\Filter\Markdown
   - Baraja\Markdown\ConverterAccessor
```

Latte filter
------------

Basic use in Latte template:

```html
{$content|markdown}
```

That will generated `<div>` automatically with the content:

```html
<div class="markdown">
   Final content...
</div>
```

To easily style content within a particular project, the `<div>` is automatically marked as a `markdown` class.

Filter `|noescape` is not required, escaping and security is started automatically by inner logic.

Renderer as a service
---------------------

In case of using Markdown renderer in an inner model or an application logic, you should inject the service by DIC.

Default renderer is `CommonMarkRenderer` (package `league/commonmark`).

To override renderer please change definition of `markdown.renderer` service in project `common.neon`, or use some of these:

- `NoRenderer` (return unchanged input),
- `SimpleRenderer` (escape by `htmlspecialchars` function only).
