# Customize PsySH

You may also want to add a custom command or change the parameters. To achieve that, simply override the
`psysh.shell` service declaration:

```yaml
# app/config/config_dev.yml

services:
    psysh.shell:
        class: Psy\Shell
        calls:
            - method: setScopeVariables
              arguments:
                -
                    container: @service_container
                    session: @session
```

Now if you run `php app/console psysh` and then `ls`, you will see the variables `$container` and `$session`:

```
>>> ls
Variables: $container, $session
```

The default configuration is the following:

```yaml
# app/config/config_dev.yml

services:
    psysh.shell:
        class: Psy\Shell
        calls:
            - method: setScopeVariables
              arguments:
                -
                    kernel: @kernel
                    container: @service_container
                    parameters: @=service('service_container').getParameterBag().all()
```

**Note: PsyshBundle is by default registered to the Kernel only in dev/test environment and so are the bundle package
. If you override the service declaration, ensure that it will not occur in production. You can declare your service
in `app/config/config_dev.yml` for example or create a new `app/config/services_dev.yml` that will be imported only
in dev.**

Previous chapter: [PsySH for breakpoints](breakpoint.md)<br />
[Back to Table of Contents](./../../../README.md#documentation)
