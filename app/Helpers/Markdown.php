<?php

use League\CommonMark\CommonMarkConverter;

function markdown($md)
{
    return (new CommonMarkConverter())->convertToHtml($md);
}
