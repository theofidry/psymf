<?php

namespace
{
    if (false === function_exists('psysh')) {
        function psysh(array $variables = [], $bind = null)
        {
            @trigger_error(
                'This method is deprecated since 3.2.0. Use Fidry\PsyshBundle\psysh instead.',
                E_USER_DEPRECATED
            );
            \Fidry\PsyshBundle\PsyshFacade::debug($variables, $bind);
        }
    }
}

namespace Fidry\PsyshBundle
{
    function psysh(array $variables = [], $bind = null)
    {
        \Fidry\PsyshBundle\PsyshFacade::debug($variables, $bind);
    }
}
